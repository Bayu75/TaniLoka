<?php

namespace Database\Seeders;

use App\Models\Percakapan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PercakapanSeeder extends Seeder
{
    public function run(): void
    {
        $made = User::where('email', 'made@taniloka.test')->firstOrFail();
        $ketut = User::where('email', 'ketut@taniloka.test')->firstOrFail();
        $resto = User::where('email', 'resto@taniloka.test')->firstOrFail();

        $selada = Produk::where('nama', 'Selada Keriting')->firstOrFail();
        $cabai = Produk::where('nama', 'Cabai Rawit Merah')->firstOrFail();
        $pesananSelesai = Pesanan::where('kode', 'TL-SEED-04')->first();

        // Nego selada dengan Made: masih menunggu jawaban.
        $this->isiPercakapan(
            Percakapan::updateOrCreate(
                ['pengusaha_id' => $resto->id, 'petani_id' => $made->id],
                ['status' => 'terbuka', 'pesanan_id' => null],
            ),
            [
                ['pengirim' => $resto, 'tipe' => 'teks', 'jam' => 5,
                 'isi' => 'Selamat pagi Pak Made, warung saya butuh selada untuk tiga hari ke depan.'],
                ['pengirim' => $resto, 'tipe' => 'penawaran', 'jam' => 4, 'isi' => 'Penawaran selada keriting',
                 'produk' => $selada, 'harga_penawaran' => 15000, 'jumlah_penawaran' => 10, 'status_penawaran' => 'menunggu'],
                ['pengirim' => $made, 'tipe' => 'teks', 'jam' => 3,
                 'isi' => 'Pagi Pak Putu. Stok masih ada, tapi di bawah Rp16.000 sulit. Saya cek dulu ya.'],
            ],
        );

        // Nego cabai dengan Ketut: sudah disepakati.
        $this->isiPercakapan(
            Percakapan::updateOrCreate(
                ['pengusaha_id' => $resto->id, 'petani_id' => $ketut->id],
                ['status' => 'terbuka', 'pesanan_id' => $pesananSelesai?->id],
            ),
            [
                ['pengirim' => $resto, 'tipe' => 'teks', 'jam' => 30,
                 'isi' => 'Pak Ketut, boleh tawar cabai rawit untuk stok dapur?'],
                ['pengirim' => $resto, 'tipe' => 'penawaran', 'jam' => 28, 'isi' => 'Penawaran cabai rawit merah',
                 'produk' => $cabai, 'harga_penawaran' => 50000, 'jumlah_penawaran' => 4, 'status_penawaran' => 'disepakati'],
                ['pengirim' => $ketut, 'tipe' => 'teks', 'jam' => 26,
                 'isi' => 'Bisa, Pak. Harga itu saya setujui. Silakan lanjut pesan lewat keranjang.'],
            ],
        );
    }

    private function isiPercakapan(Percakapan $percakapan, array $daftar): void
    {
        $percakapan->pesan()->delete();
        $terakhir = null;

        foreach ($daftar as $data) {
            $waktu = Carbon::now()->subHours($data['jam']);
            $terakhir = $waktu;

            $harga = $data['harga_penawaran'] ?? null;
            $jumlah = $data['jumlah_penawaran'] ?? null;
            $produk = $data['produk'] ?? null;

            $percakapan->pesan()->create([
                'pengirim_id' => $data['pengirim']->id,
                'tipe' => $data['tipe'],
                'isi' => $data['isi'],
                'produk_id' => $produk?->id,
                'harga_normal' => $produk?->harga_per_kg,
                'harga_penawaran' => $harga,
                'jumlah_penawaran' => $jumlah,
                'total_penawaran' => ($harga && $jumlah) ? $harga * $jumlah : null,
                'status_penawaran' => $data['status_penawaran'] ?? null,
                'dibaca_pada' => $waktu->copy()->addMinutes(20),
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ]);
        }

        $percakapan->update(['pesan_terakhir_pada' => $terakhir]);
    }
}