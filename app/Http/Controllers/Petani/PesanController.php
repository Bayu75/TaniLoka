<?php

namespace App\Http\Controllers\Petani;

use App\Models\Percakapan;
use App\Models\Pesan;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Percakapan petani dengan pembeli (pengusaha).
 * Hanya menampilkan dan membalas percakapan milik petani yang login.
 * Membuat percakapan baru tidak dilakukan di sini (biasanya dimulai dari sisi pembeli).
 */
class PesanController extends PetaniController
{
    public function index(Request $request): View
    {
        $user = $this->petani($request);

        return view('petani.pesan', [
            'percakapan' => $this->daftarPercakapan($user),
            'percakapanAktif' => null,
            'pesan' => [],
        ]);
    }

    public function show(Request $request, int $percakapan): View
    {
        $user = $this->petani($request);
        $model = $this->percakapanMilik($percakapan, $user);

        $this->tandaiDibaca($model, $user);

        $model->load([
            'pengusaha.profilPengusaha',
            'pesanan.item',
            'pesan.pengirim',
            'pesan.produk.fotoUtama',
        ]);

        // Ambil seluruh pesan penawaran dalam percakapan
        $daftarPenawaran = $model->pesan
            ->where('tipe', 'penawaran')
            ->sortBy('id')
            ->values();

        // Penawaran pertama harus berasal dari pengusaha
        $penawaranAwal = $daftarPenawaran->first(
            fn (Pesan $m) =>
                (int) $m->pengirim_id === (int) $model->pengusaha_id
        );

        // Produk mengikuti penawaran awal dari pengusaha
        $produk = $penawaranAwal?->produk;

        // Penawaran terakhir dalam negosiasi
        $penawaranTerakhir = $daftarPenawaran->last();

        // Penawaran yang masih menunggu jawaban
        $penawaranAktif = $penawaranTerakhir?->status_penawaran === 'menunggu'
            ? $penawaranTerakhir
            : null;

        // Panel negosiasi hanya aktif jika pengusaha sudah memulai
        // negosiasi dan percakapan belum ditutup
        $negosiasiAktif = $penawaranAwal !== null
            && $model->status !== 'tertutup';

        $daftarPesan = $model->pesan->map(
            fn (Pesan $m) => $this->ringkasPesan($m, $user, $model)
        )->values();

        return view('petani.pesan', [
            'percakapan' => $this->daftarPercakapan($user),
            'percakapanAktif' => $this->ringkasPercakapan($model, $user),
            'pesan' => $daftarPesan,
            'daftarPesan' => $daftarPesan,
            'namaPengusaha' => $this->namaPengusaha($model->pengusaha),
            'produk' => $produk,
            'penawaranAwal' => $penawaranAwal,
            'penawaranAktif' => $penawaranAktif,
            'penawaranTerakhir' => $penawaranTerakhir,
            'negosiasiAktif' => $negosiasiAktif,
        ]);
    }

    public function kirim(Request $request, int $percakapan): RedirectResponse|JsonResponse
    {
        $user = $this->petani($request);
        $model = $this->percakapanMilik($percakapan, $user);

        $data = $request->validate([
            'isi' => ['required', 'string', 'max:2000'],
        ], $this->pesanValidasi(), [
            'isi' => 'Pesan',
        ]);

        $isi = trim($data['isi']);

        if ($isi === '') {
            return $request->expectsJson()
                ? response()->json([
                    'success' => false,
                    'message' => 'Pesan wajib diisi.',
                ], 422)
                : back()->withErrors(['isi' => 'Pesan wajib diisi.'])->withInput();
        }

        $pesan = Pesan::query()->create([
            'percakapan_id' => $model->id,
            'pengirim_id' => $user->id,
            'isi' => $isi,
        ]);

        $model->update([
            'pesan_terakhir_pada' => now(),
        ]);

        if ($request->expectsJson()) {
            $pesan->load('pengirim');

            return response()->json([
                'success' => true,
                'pesan' => [
                    'id' => $pesan->id,
                    'tipe' => $pesan->tipe ?? 'teks',
                    'arah' => 'keluar',
                    'pengirim_id' => $pesan->pengirim_id,
                    'pengirim' => 'Anda',
                    'isi' => $pesan->isi,
                    'harga' => $pesan->harga_penawaran,
                    'jumlah' => $pesan->jumlah_penawaran,
                    'total' => $pesan->total_penawaran,
                    'harga_normal' => $pesan->harga_normal,
                    'status' => $pesan->status_penawaran,
                    'waktu' => $this->waktuWita($pesan->created_at, 'H:i'),
                ],
            ]);
        }

        return redirect()
            ->route('petani.pesan.show', $model->id)
            ->with('sukses', 'Pesan terkirim.');
    }
    
    public function kirimPenawaran(Request $request, int $percakapan): RedirectResponse
    {
        $user = $this->petani($request);
        $model = $this->percakapanMilik($percakapan, $user);

        if ($model->status === 'tertutup') {
            return back()->with('gagal', 'Negosiasi sudah ditutup.');
        }

        // Cari penawaran pertama yang dikirim oleh pengusaha.
        // Penawaran ini menjadi acuan produk dalam negosiasi.
        $penawaranAwal = $model->pesan()
            ->where('tipe', 'penawaran')
            ->where('pengirim_id', $model->pengusaha_id)
            ->orderBy('id')
            ->first();

        if (! $penawaranAwal || ! $penawaranAwal->produk_id) {
            return back()->with('gagal', 'Pengusaha belum memulai negosiasi.');
        }

        // Produk tidak diambil dari input form.
        // Produk harus mengikuti penawaran awal pengusaha.
        $produk = Produk::query()
            ->where('id', $penawaranAwal->produk_id)
            ->where('petani_id', $user->id)
            ->first();

        if (! $produk) {
            return back()->with('gagal', 'Produk dalam negosiasi tidak ditemukan.');
        }

        $penawaranTerakhir = $model->pesan()
            ->where('tipe', 'penawaran')
            ->latest('id')
            ->first();

        if (
            $penawaranTerakhir &&
            $penawaranTerakhir->status_penawaran === 'menunggu' &&
            (int) $penawaranTerakhir->pengirim_id === (int) $user->id
        ) {
            return back()->with('gagal', 'Tunggu jawaban pengusaha atas penawaran sebelumnya.');
        }

        $data = $request->validate([
            'harga_penawaran' => ['required', 'numeric', 'min:1'],
            'jumlah_penawaran' => ['required', 'numeric', 'gt:0'],
            'isi' => ['nullable', 'string', 'max:5000'],
        ], $this->pesanValidasi(), [
            'harga_penawaran' => 'Harga penawaran',
            'jumlah_penawaran' => 'Jumlah penawaran',
            'isi' => 'Catatan',
        ]);

        $jumlah = (float) $data['jumlah_penawaran'];

        if ($jumlah < (float) $produk->min_order_kg) {
            return back()->withErrors([
                'jumlah_penawaran' => 'Jumlah minimal pembelian adalah '.$produk->min_order_kg.' kg.',
            ])->withInput();
        }

        if ($jumlah > (float) $produk->stok_kg) {
            return back()->withErrors([
                'jumlah_penawaran' => 'Jumlah penawaran melebihi stok yang tersedia.',
            ])->withInput();
        }

        \Illuminate\Support\Facades\DB::transaction(function () use (
            $model,
            $penawaranTerakhir,
            $produk,
            $data,
            $jumlah,
            $user
        ) {
            if (
                $penawaranTerakhir &&
                $penawaranTerakhir->status_penawaran === 'menunggu'
            ) {
                $penawaranTerakhir->update([
                    'status_penawaran' => 'ditawar',
                ]);
            }

            $model->pesan()->create([
                'pengirim_id' => $user->id,
                'produk_id' => $produk->id,
                'tipe' => 'penawaran',
                'harga_normal' => $produk->harga_per_kg,
                'harga_penawaran' => $data['harga_penawaran'],
                'jumlah_penawaran' => $jumlah,
                'total_penawaran' => $data['harga_penawaran'] * $jumlah,
                'status_penawaran' => 'menunggu',
                'isi' => $data['isi'] ?? '',
            ]);

            $model->update([
                'status' => 'terbuka',
                'pesan_terakhir_pada' => now(),
            ]);
        });

        return redirect()
            ->route('petani.pesan.show', $model->id)
            ->with('sukses', 'Penawaran berhasil dikirim.');
    }


    public function terimaPenawaran(Request $request, int $percakapan, int $pesan): RedirectResponse
    {
        $user = $this->petani($request);
        $model = $this->percakapanMilik($percakapan, $user);

        $penawaran = $model->pesan()    
            ->where('id', $pesan)
            ->where('tipe', 'penawaran')
            ->where('pengirim_id', '!=', $user->id)
            ->where('status_penawaran', 'menunggu')
            ->firstOrFail();

        \Illuminate\Support\Facades\DB::transaction(function () use ($model, $penawaran) {
            $penawaran->update([
                'status_penawaran' => 'disepakati',
            ]);

            $model->update([
                'status' => 'tertutup',
                'pesan_terakhir_pada' => now(),
            ]);
        });

        return redirect()
            ->route('petani.pesan.show', $model->id)
            ->with('sukses', 'Penawaran berhasil disepakati.');
    }

    public function tolakPenawaran(Request $request, int $percakapan, int $pesan): RedirectResponse
    {
        $user = $this->petani($request);
        $model = $this->percakapanMilik($percakapan, $user);

        $penawaran = $model->pesan()
            ->where('id', $pesan)
            ->where('tipe', 'penawaran')
            ->where('pengirim_id', '!=', $user->id)
            ->where('status_penawaran', 'menunggu')
            ->firstOrFail();

        \Illuminate\Support\Facades\DB::transaction(function () use ($model, $penawaran) {
            $penawaran->update([
                'status_penawaran' => 'ditolak',
            ]);

            $model->update([
                'status' => 'tertutup',
                'pesan_terakhir_pada' => now(),
            ]);
        });

        return redirect()
            ->route('petani.pesan.show', $model->id)
            ->with('sukses', 'Penawaran berhasil ditolak.');
    }

    /** 404 kalau tidak ada, 403 kalau percakapan milik petani lain. */
    private function percakapanMilik(int $id, User $user): Percakapan
    {
        $percakapan = Percakapan::query()->find($id);

        return $this->pastikanMilik($percakapan, $user, 'petani_id');
    }

    /** @return \Illuminate\Support\Collection<int, array<string, mixed>> */
    private function daftarPercakapan(User $user)
    {
        $daftar = Percakapan::query()
            ->with(['pengusaha.profilPengusaha', 'pesanan.item'])
            ->withCount([
                'pesan as belum_dibaca' => fn ($q) => $q->whereNull('dibaca_pada')->where('pengirim_id', '!=', $user->id),
            ])
            ->where('petani_id', $user->id)
            ->orderByDesc('pesan_terakhir_pada')
            ->orderByDesc('id')
            ->get();

        $terakhir = $this->pesanTerakhir($daftar->pluck('id'));
        $daftar->each(function (Percakapan $p) use ($terakhir) {
            $p->setRelation('pesan', collect($terakhir->get($p->id) ? [$terakhir->get($p->id)] : []));
        });

        return $daftar->map(fn (Percakapan $p) => $this->ringkasPercakapan($p, $user))->values();
    }

    /** @param  \Illuminate\Support\Collection<int, mixed>  $ids */
    private function pesanTerakhir($ids)
    {
        if ($ids->isEmpty()) {
            return collect();
        }

        $idTerakhir = Pesan::query()
            ->selectRaw('MAX(id) as id')
            ->whereIn('percakapan_id', $ids)
            ->groupBy('percakapan_id')
            ->pluck('id');

        return Pesan::query()
            ->whereIn('id', $idTerakhir)
            ->get()
            ->keyBy('percakapan_id');
    }

    /** @return array<string, mixed> */
    private function ringkasPercakapan(Percakapan $p, User $user): array
    {
        $terakhir = $p->relationLoaded('pesan')
            ? $p->pesan->sortByDesc('id')->first()
            : $p->pesan()->orderByDesc('id')->first();

        $waktuSumber = $p->pesan_terakhir_pada ?: $terakhir?->created_at;
        $baru = (int) ($p->belum_dibaca ?? 0) > 0;

        return [
            'id' => $p->id,
            'nama' => $this->namaPengusaha($p->pengusaha),
            'foto' => $this->urlFile($p->pengusaha?->foto),
            'produk' => $p->pesanan?->item?->first()?->nama_produk ?: '',
            'cuplikan' => $terakhir?->isi ? $this->potong($terakhir->isi, 80) : 'Belum ada pesan.',
            'waktu' => $this->waktuDaftar($waktuSumber),
            'baru' => $baru,
            'status' => $this->labelStatus($p->pesanan?->status),
        ];
    }

    /** @return array<string, mixed> */
    private function ringkasPesan(Pesan $m, User $user, Percakapan $percakapan): array
    {
        $milikSaya = (int) $m->pengirim_id === (int) $user->id;

        $namaPengirim = $milikSaya
            ? 'Anda'
            : $this->namaPengusaha($m->pengirim ?: $percakapan->pengusaha);

        return [
            'id' => $m->id,
            'tipe' => $m->tipe ?? 'teks',
            'arah' => $milikSaya ? 'keluar' : 'masuk',
            'pengirim_id' => $m->pengirim_id,
            'pengirim' => $namaPengirim,
            'isi' => $m->isi,
            'harga' => $m->harga_penawaran,
            'jumlah' => $m->jumlah_penawaran,
            'total' => $m->total_penawaran,
            'harga_normal' => $m->harga_normal ?? $m->produk?->harga_per_kg ?? 0,
            'status' => $m->status_penawaran,
            'waktu' => $this->waktuWita($m->created_at, 'H:i'),
        ];
    }

    private function tandaiDibaca(Percakapan $percakapan, User $user): void
    {
        Pesan::query()
            ->where('percakapan_id', $percakapan->id)
            ->whereNull('dibaca_pada')
            ->where('pengirim_id', '!=', $user->id)
            ->update(['dibaca_pada' => now()]);
    }

    private function namaPengusaha($user): string
    {
        if (! $user) {
            return 'Pembeli';
        }

        $namaUsaha = trim((string) ($user->profilPengusaha?->nama_usaha ?? ''));

        return $namaUsaha !== '' ? $namaUsaha : (trim((string) $user->name) ?: 'Pembeli');
    }

    private function labelStatus(?string $status): string
    {
        return match ($status) {
            'menunggu_pembayaran' => 'Belum dibayar',
            'menunggu_konfirmasi' => 'Menunggu konfirmasi',
            'diproses' => 'Diproses',
            'dikirim' => 'Dikirim',
            'siap_diambil' => 'Siap diambil',
            'terkirim' => 'Terkirim',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
            'ditolak' => 'Ditolak',
            default => 'Terbuka',
        };
    }

    private function waktuDaftar($waktu): string
    {
        if (! $waktu) {
            return '';
        }

        $wita = \Illuminate\Support\Carbon::parse($waktu)->timezone(self::ZONA_WAKTU);
        $hariIni = now()->timezone(self::ZONA_WAKTU)->isSameDay($wita);

        return $this->waktuWita($waktu, $hariIni ? 'H:i' : 'j M');
    }

    private function potong(string $teks, int $batas): string
    {
        $teks = trim(preg_replace('/\s+/u', ' ', $teks) ?? $teks);

        return mb_strlen($teks) > $batas ? mb_substr($teks, 0, $batas - 1).'…' : $teks;
    }
}
