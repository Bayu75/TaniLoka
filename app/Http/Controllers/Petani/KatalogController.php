<?php

namespace App\Http\Controllers\Petani;

use App\Models\Kategori;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Ulasan;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Katalog produk petani: menampilkan produk aktif dari semua petani (bukan hanya milik sendiri).
 * Pencarian dan saring tetap dijalankan di Alpine.js, sama seperti sebelumnya (hanya setelah Cari/Enter).
 */
class KatalogController extends PetaniController
{
    public function index(Request $request): View
    {
        $user = $this->petani($request);

        $daftarKategori = Kategori::query()
            ->orderBy('id')
            ->get(['nama', 'ikon'])
            ->map(fn (Kategori $k) => [
                'nama' => $k->nama,
                'ikon' => $k->ikon ?: '',
            ])
            ->values()
            ->all();

        array_unshift($daftarKategori, ['nama' => 'Semua', 'ikon' => '']);

        $koleksi = Produk::query()
            ->with(['kategori', 'foto', 'petani.profilPetani'])
            ->where('aktif', true)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $petaniIds = $koleksi->pluck('petani_id')->unique()->filter()->values();
        $statistik = $this->statistikPetani($petaniIds);

        $produk = $koleksi->map(function (Produk $p) use ($statistik) {
            $pemilik = $p->petani;
            $profil = $pemilik?->profilPetani;
            $stat = $statistik[(int) $p->petani_id] ?? ['rating' => 0, 'transaksi' => 0];
            $foto = $p->foto
                ->sortBy('urutan')
                ->map(fn ($f) => $f->url)
                ->filter()
                ->values()
                ->all();

            $label = [];
            if ($p->grade) {
                $label[] = 'Grade '.$p->grade;
            }

            $panen = $p->tanggal_panen
                ? 'Dipanen '.$this->waktuWita($p->tanggal_panen, 'j M Y')
                : (string) ($p->deskripsi ?? '');

            return [
                'id' => $p->id,
                'nama' => $p->nama,
                'kategori' => $p->kategori?->nama ?? '-',
                'harga' => (int) $p->harga_per_kg,
                'minimal' => (float) $p->min_order_kg,
                'stokKg' => (float) $p->stok_kg,
                'petani' => $this->namaTokoPetani($pemilik),
                'rating' => $stat['rating'],
                'transaksi' => $stat['transaksi'],
                'lokasi' => $profil?->kabupaten ?: '',
                'label' => $label,
                // Tabel produk belum punya kolom nego. Badge "Bisa Nego" disembunyikan sampai ada kolomnya.
                'nego' => false,
                'stok' => (float) $p->stok_kg > 0,
                'gambar' => $foto[0] ?? null,
                'foto' => $foto,
                'ikon' => $p->kategori?->ikon ?: '',
                'panen' => $panen,
                'deskripsi' => $p->deskripsi ?: '',
                'terverifikasi' => $pemilik?->terverifikasi() ?? false,
            ];
        })->values();

        return view('petani.katalog', [
            'produk' => $produk,
            'daftarKategori' => $daftarKategori,
            'namaSaya' => $this->namaTokoPetani($user),
            'banner' => asset('images/banner-toko.jpg'),
        ]);
    }

    /** Nama yang dipakai di kartu, detail, dan perbandingan "toko saya". */
    private function namaTokoPetani($user): string
    {
        if (! $user) {
            return 'Petani';
        }

        $namaUsaha = trim((string) ($user->profilPetani?->nama_usaha_tani ?? ''));

        return $namaUsaha !== '' ? $namaUsaha : (trim((string) $user->name) ?: 'Petani');
    }

    /**
     * Rating rata-rata dari tabel ulasan (per petani, bukan per produk)
     * dan jumlah pesanan selesai sebagai angka transaksi.
     *
     * @param  \Illuminate\Support\Collection<int, mixed>  $petaniIds
     * @return array<int, array{rating: float, transaksi: int}>
     */
    private function statistikPetani($petaniIds): array
    {
        if ($petaniIds->isEmpty()) {
            return [];
        }

        $rating = Ulasan::query()
            ->selectRaw('petani_id, AVG(rating) as rata, COUNT(*) as jumlah')
            ->whereIn('petani_id', $petaniIds)
            ->groupBy('petani_id')
            ->get()
            ->keyBy('petani_id');

        $transaksi = Pesanan::query()
            ->selectRaw('petani_id, COUNT(*) as jumlah')
            ->whereIn('petani_id', $petaniIds)
            ->where('status', 'selesai')
            ->groupBy('petani_id')
            ->pluck('jumlah', 'petani_id');

        $hasil = [];
        foreach ($petaniIds as $id) {
            $id = (int) $id;
            $rata = $rating->get($id);
            $hasil[$id] = [
                'rating' => $rata ? round((float) $rata->rata, 1) : 0,
                'transaksi' => (int) ($transaksi[$id] ?? 0),
            ];
        }

        return $hasil;
    }
}
