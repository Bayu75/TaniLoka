<?php

namespace Database\Seeders;

use App\Models\FotoProduk;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        $made = User::where('email', 'made@taniloka.test')->firstOrFail();
        $ketut = User::where('email', 'ketut@taniloka.test')->firstOrFail();
        $kategori = Kategori::all()->keyBy('nama');

        // petani, kategori, nama, harga, stok, min order, grade,
        // tersedia sampai (hari dari hari ini), aktif, harga flash sale (atau null)
        $daftar = [
            [$made,  'Sayuran',      'Kol Bedugul',            8000, 200, 5,  'A', 7,  true,  null],
            [$made,  'Sayuran',      'Wortel Bedugul',        12000, 150, 5,  'A', 14, true,  null],
            [$made,  'Sayuran',      'Selada Keriting',       18000,  60, 2,  'A', 2,  true,  14000], // flash sale aktif
            [$made,  'Buah',         'Stroberi Bedugul',      45000,  40, 1,  'A', 2,  true,  null],  // calon flash sale
            [$made,  'Umbi',         'Kentang Granola',       14000, 300, 10, 'B', 30, true,  null],
            [$made,  'Beras & Biji', 'Beras Merah Jatiluwih', 22000, 400, 10, 'A', 60, true,  null],
            [$made,  'Sayuran',      'Sawi Putih',             9000,  80, 5,  'B', 10, false, null],  // nonaktif
            [$ketut, 'Bumbu',        'Cabai Rawit Merah',     55000,  80, 2,  'A', 5,  true,  null],
            [$ketut, 'Bumbu',        'Bawang Merah',          38000, 120, 5,  'A', 21, true,  null],
            [$ketut, 'Bumbu',        'Bawang Putih',          32000, 100, 5,  'B', 30, true,  null],
            [$ketut, 'Buah',         'Jeruk Kintamani',       15000, 250, 10, 'A', 10, true,  null],
            [$ketut, 'Sayuran',      'Tomat',                 10000, 180, 5,  'B', -1, true,  null],  // lewat tanggal
            [$ketut, 'Sayuran',      'Timun Tulis',            7000,   0, 5,  'C', 8,  true,  null],  // stok habis
        ];

        foreach ($daftar as [$petani, $namaKategori, $nama, $harga, $stok, $min, $grade, $hari, $aktif, $hargaFlash]) {
            $tersediaSampai = now()->addDays($hari)->toDateString();

            $produk = Produk::updateOrCreate(
                ['slug' => Str::slug($nama)],
                [
                    'petani_id' => $petani->id,
                    'kategori_id' => $kategori[$namaKategori]->id,
                    'nama' => $nama,
                    'deskripsi' => $nama.' dipanen dari kebun petani mitra TaniLoka di Bali. Segar, siap kirim dalam jumlah besar.',
                    'harga_per_kg' => $harga,
                    'stok_kg' => $stok,
                    'min_order_kg' => $min,
                    'grade' => $grade,
                    'tanggal_panen' => now()->subDays(3)->toDateString(),
                    'tersedia_sampai' => $tersediaSampai,
                    'aktif' => $aktif,
                    'harga_flash_sale' => $hargaFlash,
                    'flash_sale_sampai' => $hargaFlash ? $tersediaSampai : null,
                    'deleted_at' => null,
                ],
            );

            FotoProduk::updateOrCreate(
                ['produk_id' => $produk->id, 'urutan' => 0],
                ['path' => 'https://placehold.co/600x400?text='.urlencode($nama)],
            );
        }
    }
}