<?php

namespace Database\Seeders;

use App\Models\KeranjangItem;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Database\Seeder;

class KeranjangSeeder extends Seeder
{
    public function run(): void
    {
        $resto = User::where('email', 'resto@taniloka.test')->firstOrFail();

        foreach (['Kol Bedugul' => 10, 'Cabai Rawit Merah' => 3] as $nama => $jumlah) {
            $produk = Produk::where('nama', $nama)->firstOrFail();

            KeranjangItem::updateOrCreate(
                ['user_id' => $resto->id, 'produk_id' => $produk->id],
                ['jumlah_kg' => $jumlah, 'harga_per_kg' => $produk->hargaAktif()],
            );
        }
    }
}