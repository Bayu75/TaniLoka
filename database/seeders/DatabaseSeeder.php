<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Data awal untuk pengembangan dan demo.
 *
 * Akun masuk (kata sandi semua: "password"):
 *   admin@taniloka.test   admin
 *   made@taniloka.test    petani terverifikasi   (tombol Demo Petani)
 *   ketut@taniloka.test   petani terverifikasi
 *   wayan@taniloka.test   petani, menunggu verifikasi
 *   resto@taniloka.test   pengusaha terverifikasi (tombol Demo Pengusaha)
 *   kafe@taniloka.test    pengusaha, menunggu verifikasi
 *
 * Aman dijalankan berulang (php artisan db:seed) tanpa error unique.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AkunSeeder::class,
            KategoriSeeder::class,
            ProdukSeeder::class,
            KeranjangSeeder::class,
            PesananSeeder::class,
            PercakapanSeeder::class,
        ]);
    }
}