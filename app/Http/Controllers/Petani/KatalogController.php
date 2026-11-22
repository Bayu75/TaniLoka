<?php

namespace App\Http\Controllers\Petani;

use App\Models\Kategori;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Ulasan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * Katalog produk petani: menampilkan produk aktif dari semua petani.
 * Pencarian, kategori, kabupaten, harga, dan urutan dijalankan di server (GET).
 */
class KatalogController extends PetaniController
{
    public function index(Request $request): View
    {
        $user = $this->petani($request);

        $q = trim((string) $request->query('q', ''));
        $cat = trim((string) $request->query('cat', ''));
        $district = trim((string) $request->query('district', ''));
        $minPrice = $this->queryTeks($request, 'min_price');
        $maxPrice = $this->queryTeks($request, 'max_price');
        $sort = (string) $request->query('sort', 'newest');
        if (! in_array($sort, ['newest', 'price-asc', 'price-desc', 'rating'], true)) {
            $sort = 'newest';
        }

        $kategoriKey = Schema::hasColumn('kategori', 'slug') ? 'slug' : 'id';
        $kolomKategori = ['id', 'nama', 'ikon'];
        if ($kategoriKey === 'slug') {
            $kolomKategori[] = 'slug';
        }
        $kategoriList = Kategori::query()
            ->orderBy('id')
            ->get($kolomKategori);

        $koleksi = Produk::query()
            ->with(['kategori', 'foto', 'petani.profilPetani', 'petani.alamatUtama'])
            ->where('aktif', true)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $petaniIds = $koleksi->pluck('petani_id')->unique()->filter()->values();
        $statistik = $this->statistikPetani($petaniIds);

        $produk = $koleksi->map(fn (Produk $p) => $this->petaProduk($p, $statistik, $kategoriKey))->values();

        $produkList = $produk->filter(function (array $p) use ($q, $cat, $district, $minPrice, $maxPrice) {
            if ($q !== '') {
                $haystack = mb_strtolower($p['nama'].' '.$p['petani'].' '.$p['lokasi']);
                if (! str_contains($haystack, mb_strtolower($q))) {
                    return false;
                }
            }
            if ($cat !== '' && (string) ($p['kategoriKunci'] ?? '') !== $cat) {
                return false;
            }
            if ($district !== '' && (string) $p['lokasi'] !== $district) {
                return false;
            }
            if ($minPrice !== '' && (int) $p['harga'] < (int) $minPrice) {
                return false;
            }
            if ($maxPrice !== '' && (int) $p['harga'] > (int) $maxPrice) {
                return false;
            }

            return true;
        })->values();

        $produkList = $this->urutkanProduk($produkList, $sort);

        $kabupatenList = $produk
            ->pluck('lokasi')
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $activeCount = collect([$cat, $district, $minPrice, $maxPrice])
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->count();

        $daftarKategori = collect([['nama' => 'Semua', 'ikon' => '']])
            ->concat($kategoriList->map(fn (Kategori $k) => [
                'nama' => $k->nama,
                'ikon' => $k->ikon ?: '',
            ]))
            ->values()
            ->all();

        $pilihId = (int) $request->query('produk', 0);
        $pilihProduk = $pilihId > 0
            ? $produk->first(fn (array $row) => (int) $row['id'] === $pilihId)
            : null;

        return view('petani.katalog', [

            'produk' => $produk,
            'produkList' => $produkList,
            'daftarKategori' => $daftarKategori,
            'kategoriList' => $kategoriList,
            'kategoriKey' => $kategoriKey,
            'kabupatenList' => $kabupatenList,
            'namaSaya' => $this->namaTokoPetani($user),
            'idSaya' => (int) $user->id,
            'banner' => asset('images/banner-toko.jpg'),
            'q' => $q,
            'cat' => $cat,
            'district' => $district,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'sort' => $sort,
            'activeCount' => $activeCount,
            'pilihProduk' => $pilihProduk,
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
     * @param  array<int, array{rating: float, transaksi: int}>  $statistik
     * @return array<string, mixed>
     */
    private function petaProduk(Produk $p, array $statistik, string $kategoriKey): array
    {
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

        $kunci = $kategoriKey === 'slug'
            ? (string) ($p->kategori?->slug ?? '')
            : (string) ($p->kategori_id ?? '');

        return array_merge([
            'id' => $p->id,
            'nama' => $p->nama,
            'kategori' => $p->kategori?->nama ?? '-',
            'kategoriKunci' => $kunci,
            'harga' => (int) $p->harga_per_kg,
            'minimal' => (float) $p->min_order_kg,
            'stokKg' => (float) $p->stok_kg,
            'petani' => $this->namaTokoPetani($pemilik),
            'rating' => $stat['rating'],
            'transaksi' => $stat['transaksi'],
            'lokasi' => trim((string) (
                ((Schema::hasColumn('profil_petani', 'kabupaten') ? ($profil?->kabupaten ?? '') : '')
                    ?: ($pemilik?->alamatUtama?->kabupaten ?? ''))
            )),
            'label' => $label,
            'grade' => $p->grade ?: null,
            'petani_id' => (int) $p->petani_id,
            'tanggalPanen' => $p->tanggal_panen ? $this->waktuWita($p->tanggal_panen, 'd F Y') : null,
            // Tabel produk belum punya kolom nego. Badge "Bisa Nego" disembunyikan sampai ada kolomnya.
            'nego' => false,
            'stok' => (float) $p->stok_kg > 0,
            'gambar' => $foto[0] ?? null,
            'foto' => $foto,
            'ikon' => $p->kategori?->ikon ?: '',
            'panen' => $panen,
            'deskripsi' => $p->deskripsi ?: '',
            'terverifikasi' => $pemilik?->terverifikasi() ?? false,
        ], $this->infoTersediaSampai($p->tersedia_sampai));
    }

    private function queryTeks(Request $request, string $nama): string
    {
        $nilai = $request->query($nama);
        if ($nilai === null || $nilai === '') {
            return '';
        }

        return trim((string) $nilai);
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

    /**
     * Tanggal tersedia_sampai dari database, dihitung terhadap hari ini (WITA).
     * Model Produk tidak diubah; tanggal dibaca aman sebagai string Y-m-d.
     *
     * @return array{
     *     tersediaSampai: ?string,
     *     sisaHari: ?int,
     *     labelKartuTersedia: ?string,
     *     labelDetailTanggal: ?string,
     *     labelDetailSisa: ?string,
     *     segeraHabis: bool
     * }
     */
    private function infoTersediaSampai(mixed $nilai): array
    {
        $kosong = [
            'tersediaSampai' => null,
            'sisaHari' => null,
            'labelKartuTersedia' => null,
            'labelDetailTanggal' => null,
            'labelDetailSisa' => null,
            'segeraHabis' => false,
            'kedaluwarsa' => false,
        ];

        $tanggal = $this->tanggalYmdAtauNull($nilai);
        if ($tanggal === null) {
            return $kosong;
        }

        try {
            $batas = Carbon::createFromFormat('Y-m-d', $tanggal, self::ZONA_WAKTU);
        } catch (\Throwable) {
            return $kosong;
        }

        if (! $batas) {
            return $kosong;
        }

        $batas = $batas->startOfDay();
        $hariIni = now(self::ZONA_WAKTU)->startOfDay();
        $sisaHari = (int) round(($batas->getTimestamp() - $hariIni->getTimestamp()) / 86400);

        if ($sisaHari < 0) {
            $labelKartu = 'Sudah kedaluwarsa';
            $labelSisa = 'Sudah kedaluwarsa';
        } elseif ($sisaHari === 0) {
            $labelKartu = 'Berakhir hari ini';
            $labelSisa = 'Berakhir hari ini';
        } elseif ($sisaHari <= 3) {
            $labelKartu = 'Tinggal '.$sisaHari.' hari lagi';
            $labelSisa = $sisaHari.' hari lagi';
        } else {
            $labelKartu = 'Tersedia s/d '.$batas->copy()->timezone(self::ZONA_WAKTU)->locale('id')->translatedFormat('d M');
            $labelSisa = $sisaHari.' hari lagi';
        }

        return [
            'tersediaSampai' => $tanggal,
            'sisaHari' => $sisaHari,
            'labelKartuTersedia' => $labelKartu,
            'labelDetailTanggal' => $this->waktuWita($batas, 'j F Y'),
            'labelDetailSisa' => $labelSisa,
            'segeraHabis' => $sisaHari >= 0 && $sisaHari <= 3,
            'kedaluwarsa' => $sisaHari < 0,
        ];
    }

    /**
     * Urutkan sesuai pilihan (terbaru / harga), lalu taruh produk kedaluwarsa di akhir.
     *
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $produkList
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function urutkanProduk($produkList, string $sort)
    {
        return $produkList->values()->sort(function (array $a, array $b) use ($sort) {
            $ka = ! empty($a['kedaluwarsa']) ? 1 : 0;
            $kb = ! empty($b['kedaluwarsa']) ? 1 : 0;
            if ($ka !== $kb) {
                return $ka <=> $kb;
            }

            if ($sort === 'price-asc') {
                return ((int) $a['harga'] <=> (int) $b['harga']) ?: ((int) $b['id'] <=> (int) $a['id']);
            }

            if ($sort === 'price-desc') {
                return ((int) $b['harga'] <=> (int) $a['harga']) ?: ((int) $b['id'] <=> (int) $a['id']);
            }

            if ($sort === 'rating') {
                return (((float) $b['rating']) <=> ((float) $a['rating'])) ?: ((int) $b['id'] <=> (int) $a['id']);
            }

            // Terbaru: tetap pakai urutan koleksi (created_at desc, id desc).
            return 0;
        })->values();
    }

    /** Baca kolom date tanpa mengandalkan cast di model Produk. */
    private function tanggalYmdAtauNull(mixed $nilai): ?string
    {
        if ($nilai === null || $nilai === '') {
            return null;
        }

        try {
            return Carbon::parse($nilai)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
