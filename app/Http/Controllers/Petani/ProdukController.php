<?php

namespace App\Http\Controllers\Petani;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

/**
 * Kelola Produk petani: daftar, tambah (dengan foto), ubah, tersedia/tidak, hapus (soft delete).
 */
class ProdukController extends PetaniController
{
    private const FOLDER_FOTO = 'produk';

    public function index(Request $request): View
    {
        $user = $this->petani($request);
        $kabupaten = $user->profilPetani?->kabupaten ?? '';

        $produk = $user->produk()
            ->with(['kategori', 'fotoUtama'])
            ->latest()
            ->latest('id')
            ->get()
            ->map(fn (Produk $p) => [
                'id' => $p->id,
                'nama' => $p->nama,
                'slug' => $p->slug,
                'kategori_id' => $p->kategori_id,
                'kategori' => $p->kategori?->nama ?? '-',
                'harga' => (int) $p->harga_per_kg,
                'stok' => (float) $p->stok_kg,
                'min' => (float) $p->min_order_kg,
                'grade' => $p->grade ?? '',
                'tanggalPanen' => $p->tanggal_panen?->format('Y-m-d') ?? '',
                // "Periode Panen" di tampilan disimpan di kolom deskripsi (tabel produk tidak punya kolom periode).
                'periode' => $p->deskripsi ?? '',
                // Lokasi produk mengikuti lokasi kebun di profil_petani.
                'kabupaten' => $kabupaten,
                'tersedia' => $p->aktif,
                'gambar' => $p->fotoUtama?->url,
                // TODO(tersedia_sampai): tabel produk belum punya kolom tersedia_sampai. Tunggu keputusan Bayu.
                'tersediaSampai' => '',
            ])
            ->values();

        $kategori = Kategori::orderBy('nama')->get(['id', 'nama'])
            ->map(fn (Kategori $k) => ['id' => $k->id, 'nama' => $k->nama])
            ->values();

        return view('petani.produk', [
            'produk' => $produk,
            'kategori' => $kategori,
            'kabupatenPetani' => $kabupaten,
            'terverifikasi' => $user->terverifikasi(),
            'statusVerifikasi' => $user->status_verifikasi ?? 'belum_diajukan',
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $user = $this->petani($request);
        if (! $user->terverifikasi()) {
            return $this->tolakBelumTerverifikasi($request);
        }

        $data = $this->validasi($request, baru: true);
        $pathFoto = $request->hasFile('foto') ? $request->file('foto')->store(self::FOLDER_FOTO, 'public') : null;

        try {
            $produk = DB::transaction(function () use ($user, $data, $pathFoto) {
                $produk = $user->produk()->create([
                    'kategori_id' => $data['kategori_id'],
                    'nama' => $data['nama'],
                    'slug' => $this->slugUnik($data['nama']),
                    'deskripsi' => $data['periode'] ?? null,
                    'harga_per_kg' => $data['harga'],
                    'stok_kg' => $data['stok'],
                    'min_order_kg' => $data['min'],
                    'grade' => $data['grade'] ?? null,
                    'tanggal_panen' => $data['tanggal_panen'] ?? null,
                    'aktif' => true,
                ]);

                if ($pathFoto) {
                    $produk->foto()->create(['path' => $pathFoto, 'urutan' => 0]);
                }

                return $produk;
            });
        } catch (Throwable $e) {
            if ($pathFoto) {
                Storage::disk('public')->delete($pathFoto);
            }
            throw $e;
        }

        return $this->berhasil($request, 'Produk berhasil ditambahkan.', ['id' => $produk->id], 201);
    }

    public function update(Request $request, int $produk): JsonResponse|RedirectResponse
    {
        $user = $this->petani($request);
        $produk = $this->produkMilik($produk, $request);
        if (! $user->terverifikasi()) {
            return $this->tolakBelumTerverifikasi($request);
        }

        $data = $this->validasi($request, baru: false);
        $pathBaru = $request->hasFile('foto') ? $request->file('foto')->store(self::FOLDER_FOTO, 'public') : null;
        $pathLama = [];

        try {
            DB::transaction(function () use ($produk, $data, $pathBaru, &$pathLama) {
                $produk->update([
                    'kategori_id' => $data['kategori_id'],
                    'nama' => $data['nama'],
                    // Slug sengaja tidak diubah supaya link produk yang sudah dibagikan tetap jalan.
                    'deskripsi' => $data['periode'] ?? null,
                    'harga_per_kg' => $data['harga'],
                    'stok_kg' => $data['stok'],
                    'min_order_kg' => $data['min'],
                    'grade' => $data['grade'] ?? null,
                    'tanggal_panen' => $data['tanggal_panen'] ?? null,
                    'aktif' => array_key_exists('tersedia', $data) ? (bool) $data['tersedia'] : $produk->aktif,
                ]);

                if ($pathBaru) {
                    // Satu foto utama per produk: foto lama diganti.
                    $pathLama = $produk->foto()->pluck('path')->all();
                    $produk->foto()->delete();
                    $produk->foto()->create(['path' => $pathBaru, 'urutan' => 0]);
                }
            });
        } catch (Throwable $e) {
            if ($pathBaru) {
                Storage::disk('public')->delete($pathBaru);
            }
            throw $e;
        }

        // File lama baru dihapus setelah database berhasil diperbarui.
        $lokal = array_values(array_filter($pathLama, fn ($p) => $this->pathLokal($p)));
        if ($lokal) {
            Storage::disk('public')->delete($lokal);
        }

        return $this->berhasil($request, 'Produk berhasil diperbarui.', ['id' => $produk->id]);
    }

    /** Tombol Tersedia / Tidak tersedia di kartu produk (kolom aktif). */
    public function tersedia(Request $request, int $produk): JsonResponse|RedirectResponse
    {
        $user = $this->petani($request);
        $produk = $this->produkMilik($produk, $request);
        if (! $user->terverifikasi()) {
            return $this->tolakBelumTerverifikasi($request);
        }

        $data = $request->validate(
            ['aktif' => ['required', 'boolean']],
            $this->pesanValidasi(),
            ['aktif' => 'status ketersediaan'],
        );

        $produk->update(['aktif' => (bool) $data['aktif']]);

        $pesan = $produk->aktif ? 'Produk ditandai tersedia.' : 'Produk ditandai tidak tersedia.';

        // Tidak di-flash: halaman tidak dimuat ulang setelah toggle.
        return $this->berhasil($request, $pesan, ['aktif' => $produk->aktif], 200, flash: false);
    }

    /** Hapus produk (soft delete). Foto tetap disimpan karena riwayat pesanan masih bisa merujuk produk ini. */
    public function destroy(Request $request, int $produk): JsonResponse|RedirectResponse
    {
        $this->petani($request);
        $produk = $this->produkMilik($produk, $request);

        DB::transaction(function () use ($produk) {
            $produk->update(['aktif' => false]);
            $produk->delete();
        });

        return $this->berhasil($request, 'Produk dihapus.', ['id' => $produk->id]);
    }

    /** Produk yang belum dihapus: 404 kalau tidak ada, 403 kalau milik petani lain. */
    private function produkMilik(int $id, Request $request): Produk
    {
        return $this->pastikanMilik(Produk::find($id), $request->user());
    }

    private function validasi(Request $request, bool $baru): array
    {
        $stokPositif = is_numeric($request->input('stok')) && (float) $request->input('stok') > 0;

        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kategori_id' => ['required', 'integer', 'exists:kategori,id'],
            'harga' => ['required', 'integer', 'min:1', 'max:100000000'],
            'stok' => $baru
                ? ['required', 'numeric', 'gt:0', 'max:99999999']
                : ['required', 'numeric', 'min:0', 'max:99999999'],
            // Minimal pesan tidak boleh melebihi stok, kecuali stok sedang 0 (produk habis tetap bisa diubah).
            'min' => array_merge(['required', 'numeric', 'gt:0', 'max:999999'], $stokPositif ? ['lte:stok'] : []),
            'periode' => [$baru ? 'required' : 'nullable', 'string', 'max:1000'],
            'grade' => ['nullable', 'in:A,B,C'],
            'tanggal_panen' => ['nullable', 'date'],
            'tersedia' => ['sometimes', 'boolean'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            // TODO(tersedia_sampai): input "tersedia_sampai" dari form diabaikan sampai kolomnya ada.
        ], $this->pesanValidasi(), [
            'nama' => 'nama produk',
            'kategori_id' => 'kategori',
            'harga' => 'harga per kg',
            'stok' => 'stok (kg)',
            'min' => 'minimal pesan (kg)',
            'periode' => 'periode panen',
            'grade' => 'grade',
            'tanggal_panen' => 'tanggal panen',
            'tersedia' => 'status ketersediaan',
            'foto' => 'foto produk',
        ]);
    }

    /** Slug unik, termasuk terhadap produk yang sudah di-soft delete (index unique berlaku untuk semua baris). */
    private function slugUnik(string $nama): string
    {
        $dasar = Str::limit(Str::slug($nama) ?: 'produk', 200, '');
        $slug = $dasar;
        $i = 2;
        while (Produk::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $dasar.'-'.$i++;
        }

        return $slug;
    }

    private function berhasil(Request $request, string $pesan, array $data = [], int $status = 200, bool $flash = true): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            if ($flash) {
                session()->flash('sukses', $pesan); // tampil setelah halaman dimuat ulang
            }

            return response()->json(['message' => $pesan] + $data, $status);
        }

        return redirect()->route('petani.produk')->with('sukses', $pesan);
    }
}
