<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Controller;
use App\Http\Requests\Petani\StoreProdukRequest;
use App\Http\Requests\Petani\UpdateProdukRequest;
use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProdukController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $produk = Produk::with(['kategori', 'fotoUtama'])
            ->where('petani_id', $user->id)
            ->latest()
            ->get();

        return view('petani.produk.index', [
            'produk' => $produk,
            'stat' => [
                'jumlah' => $produk->count(),
                'aktif'  => $produk->filter(fn ($p) => $p->aktif && $p->stok_kg > 0)->count(),
                'habis'  => $produk->filter(fn ($p) => ! $p->aktif || $p->stok_kg <= 0)->count(),
            ],
            'kabupatenPetani'  => $this->kabupaten($request),
            'terverifikasi'    => $this->terverifikasi($request),
            'statusVerifikasi' => $user->status_verifikasi,
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (! $this->terverifikasi($request)) {
            return $this->tolakBelumVerifikasi();
        }

        return view('petani.produk.create', $this->dataForm($request, new Produk(['aktif' => true])));
    }

    public function store(StoreProdukRequest $request): RedirectResponse
    {
        if (! $this->terverifikasi($request)) {
            return $this->tolakBelumVerifikasi();
        }

        DB::transaction(function () use ($request) {
            $produk = Produk::create($this->kolom($request->validated()) + [
                'petani_id' => $request->user()->id,
                'slug'      => $this->buatSlug($request->input('nama')),
                'aktif'     => true,
            ]);

            if ($request->hasFile('foto')) {
                $this->simpanFoto($produk, $request->file('foto'));
            }
        });

        return redirect()->route('petani.produk.index')
            ->with('sukses', 'Produk berhasil ditambahkan.');
    }

    public function edit(Request $request, Produk $produk): View|RedirectResponse
    {
        $this->pastikanMilik($request, $produk);

        if (! $this->terverifikasi($request)) {
            return $this->tolakBelumVerifikasi();
        }

        $produk->load('fotoUtama');

        return view('petani.produk.edit', $this->dataForm($request, $produk));
    }

    public function update(UpdateProdukRequest $request, Produk $produk): RedirectResponse
    {
        $this->pastikanMilik($request, $produk);

        if (! $this->terverifikasi($request)) {
            return $this->tolakBelumVerifikasi();
        }

        DB::transaction(function () use ($request, $produk) {
            $data = $this->kolom($request->validated());
            $data['aktif'] = $request->boolean('aktif');

            if ($produk->nama !== $data['nama']) {
                $data['slug'] = $this->buatSlug($data['nama']);
            }

            $produk->update($data);

            if ($request->hasFile('foto')) {
                $this->hapusFoto($produk);
                $this->simpanFoto($produk, $request->file('foto'));
            }
        });

        return redirect()->route('petani.produk.index')
            ->with('sukses', 'Produk berhasil diperbarui.');
    }

    public function toggleTersedia(Request $request, Produk $produk): RedirectResponse
    {
        $this->pastikanMilik($request, $produk);

        if (! $this->terverifikasi($request)) {
            return $this->tolakBelumVerifikasi();
        }

        $request->validate(['aktif' => ['required', 'boolean']]);
        $produk->update(['aktif' => $request->boolean('aktif')]);

        return back()->with('sukses', $produk->aktif
            ? 'Produk ditampilkan di katalog.'
            : 'Produk disembunyikan dari katalog.');
    }

    public function destroy(Request $request, Produk $produk): RedirectResponse
    {
        $this->pastikanMilik($request, $produk);

        // Pakai soft delete: foto tidak dihapus, supaya riwayat pesanan tetap aman.
        $produk->delete();

        return redirect()->route('petani.produk.index')
            ->with('sukses', 'Produk dihapus.');
    }

    /* ---------- fungsi bantu ---------- */

    // Data dari formulir yang disimpan ke tabel produk.
    private function kolom(array $v): array
    {
        return [
            'nama'            => trim($v['nama']),
            'kategori_id'     => $v['kategori_id'],
            'grade'           => $v['grade'] ?? null,
            'harga_per_kg'    => $v['harga_per_kg'],
            'stok_kg'         => $v['stok_kg'],
            'min_order_kg'    => $v['min_order_kg'],
            'deskripsi'       => isset($v['deskripsi']) ? trim($v['deskripsi']) : null,
            'tanggal_panen'   => $v['tanggal_panen'] ?? null,
            'tersedia_sampai' => $v['tersedia_sampai'] ?? null,
        ];
    }

    // Contoh hasil: "tomat-cherry-segar-x7k2qa"
    private function buatSlug(string $nama): string
    {
        do {
            $slug = Str::slug($nama) . '-' . Str::lower(Str::random(6));
        } while (Produk::withTrashed()->where('slug', $slug)->exists());

        return $slug;
    }

    private function simpanFoto(Produk $produk, UploadedFile $file): void
    {
        $produk->foto()->create([
            'path'   => $file->store('produk', 'public'),
            'urutan' => 0,
        ]);
    }

    private function hapusFoto(Produk $produk): void
    {
        foreach ($produk->foto as $foto) {
            Storage::disk('public')->delete($foto->path);
            $foto->delete();
        }
    }

    private function dataForm(Request $request, Produk $produk): array
    {
        return [
            'produk'          => $produk,
            'kategori'        => Kategori::orderBy('nama')->get(),
            'kabupatenPetani' => $this->kabupaten($request),
        ];
    }

    private function kabupaten(Request $request): string
    {
        return $request->user()->alamatUtama?->kabupaten ?? '';
    }

    // Samakan dengan kondisi verifikasi di pengendali lama kalau nilainya beda.
    private function terverifikasi(Request $request): bool
    {
        return $request->user()->status_verifikasi === 'terverifikasi';
    }

    private function tolakBelumVerifikasi(): RedirectResponse
    {
        return redirect()->route('petani.produk.index')
            ->with('gagal', 'Akun belum terverifikasi. Produk belum bisa ditambah atau diubah.');
    }

    private function pastikanMilik(Request $request, Produk $produk): void
    {
        abort_unless($produk->petani_id === $request->user()->id, 403);
    }
}