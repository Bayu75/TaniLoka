<?php

namespace Database\Seeders;

use App\Models\Alamat;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use App\Services\Petani\AlurPesanan;
use Illuminate\Database\Seeder;

class PesananSeeder extends Seeder
{
    public function run(): void
    {
        $made = User::where('email', 'made@taniloka.test')->firstOrFail();
        $ketut = User::where('email', 'ketut@taniloka.test')->firstOrFail();
        $resto = User::where('email', 'resto@taniloka.test')->firstOrFail();
        $alamat = Alamat::where('user_id', $resto->id)->where('utama', true)->firstOrFail();

        $dasar = ['pengusaha' => $resto, 'alamat' => $alamat, 'metode_pengiriman' => 'kendaraan'];

        $daftar = [
            ['kode' => 'TL-SEED-01', 'petani' => $made, 'items' => [['Wortel Bedugul', 10]],
             'status' => 'menunggu_konfirmasi', 'metode_pembayaran' => 'transfer_bank', 'status_pembayaran' => 'belum_bayar'],
            ['kode' => 'TL-SEED-02', 'petani' => $made, 'items' => [['Kol Bedugul', 20]],
             'status' => 'diproses', 'metode_pembayaran' => 'gopay', 'status_pembayaran' => 'lunas'],
            ['kode' => 'TL-SEED-03', 'petani' => $made, 'items' => [['Kentang Granola', 15]],
             'status' => 'terkirim', 'metode_pembayaran' => 'cod', 'status_pembayaran' => 'lunas'],
            ['kode' => 'TL-SEED-04', 'petani' => $ketut, 'items' => [['Bawang Merah', 5], ['Tomat', 5]],
             'status' => 'selesai', 'metode_pembayaran' => 'transfer_bank', 'status_pembayaran' => 'lunas',
             'ulasan' => ['rating' => 5, 'komentar' => 'Bawang segar, tomat sedikit lewat masak tapi masih layak. Pengiriman tepat waktu.']],
            ['kode' => 'TL-SEED-05', 'petani' => $ketut, 'items' => [['Cabai Rawit Merah', 3]],
             'status' => 'siap_diambil', 'metode_pengiriman' => 'Kendaraan Sendiri',
             'metode_pembayaran' => 'ovo', 'status_pembayaran' => 'lunas'],
            ['kode' => 'TL-SEED-06', 'petani' => $ketut, 'items' => [['Bawang Putih', 5]],
             'status' => 'ditolak', 'metode_pembayaran' => 'transfer_bank', 'status_pembayaran' => 'belum_bayar',
             'alasan' => 'Stok bawang putih sedang dipakai untuk pesanan sebelumnya.'],
            ['kode' => 'TL-SEED-07', 'petani' => $made, 'items' => [['Selada Keriting', 2]],
             'status' => 'menunggu_pembayaran', 'metode_pembayaran' => 'transfer_bank', 'status_pembayaran' => 'belum_bayar'],
        ];

        foreach ($daftar as $data) {
            $this->buatPesanan(array_merge($dasar, $data));
        }
    }

    private function buatPesanan(array $data): Pesanan
    {
        $items = collect($data['items'])->map(function ($item) {
            [$nama, $kg] = $item;
            $produk = Produk::where('nama', $nama)->firstOrFail();

            return [
                'produk' => $produk,
                'kg' => $kg,
                'harga' => (int) $produk->harga_per_kg,
                'subtotal' => (int) $produk->harga_per_kg * $kg,
            ];
        });

        // Rumus sama dengan PesananController: subtotal + ongkir + PPN 10%, biaya layanan 0.
        $subtotal = (int) $items->sum('subtotal');
        $ambilSendiri = in_array(
            mb_strtolower($data['metode_pengiriman']),
            array_map('mb_strtolower', AlurPesanan::METODE_AMBIL_SENDIRI),
            true,
        );
        $ongkir = $ambilSendiri ? 0 : 15000;
        $ppn = (int) round($subtotal * 0.10);
        $total = $subtotal + $ongkir + $ppn;
        $alamat = $data['alamat'];

        $pesanan = Pesanan::updateOrCreate(
            ['kode' => $data['kode']],
            [
                'pengusaha_id' => $data['pengusaha']->id,
                'petani_id' => $data['petani']->id,
                'nama_penerima' => $alamat->nama_penerima,
                'no_hp_penerima' => $alamat->no_hp,
                'alamat_pengiriman' => implode(', ', array_filter([
                    $alamat->alamat_lengkap, $alamat->kecamatan, $alamat->kabupaten,
                ])),
                'metode_pengiriman' => $data['metode_pengiriman'],
                'catatan' => $data['catatan'] ?? null,
                'subtotal' => $subtotal,
                'ongkir' => $ongkir,
                'ppn' => $ppn,
                'biaya_layanan' => 0,
                'total' => $total,
                'status' => $data['status'],
            ],
        );

        $pesanan->item()->delete();
        foreach ($items as $item) {
            $pesanan->item()->create([
                'produk_id' => $item['produk']->id,
                'nama_produk' => $item['produk']->nama,
                'harga_per_kg' => $item['harga'],
                'jumlah_kg' => $item['kg'],
                'subtotal' => $item['subtotal'],
            ]);
        }

        $pesanan->riwayat()->delete();
        foreach ($this->alurSampai($data['status'], $ambilSendiri) as $i => $status) {
            $pesanan->riwayat()->create([
                'status' => $status,
                'keterangan' => $this->keteranganRiwayat($status, $data['alasan'] ?? null),
                'diubah_oleh' => $i === 0 ? $data['pengusaha']->id : $data['petani']->id,
                'created_at' => now()->subHours(max(0, 12 - $i)),
            ]);
        }

        $lunas = $data['status_pembayaran'] === 'lunas';
        $pesanan->pembayaran()->updateOrCreate(
            ['pesanan_id' => $pesanan->id],
            [
                'metode' => $data['metode_pembayaran'],
                'jumlah' => $total,
                'status' => $data['status_pembayaran'],
                'bukti_path' => $lunas && $data['metode_pembayaran'] === 'transfer_bank' ? 'pembayaran/contoh.jpg' : null,
                'dibayar_pada' => $lunas ? now()->subHours(8) : null,
            ],
        );

        if (isset($data['ulasan'])) {
            $pesanan->ulasan()->updateOrCreate(
                ['pesanan_id' => $pesanan->id],
                [
                    'pengusaha_id' => $data['pengusaha']->id,
                    'petani_id' => $data['petani']->id,
                    'rating' => $data['ulasan']['rating'],
                    'komentar' => $data['ulasan']['komentar'],
                ],
            );
        }

        return $pesanan;
    }

    // Urutan status mengikuti AlurPesanan: konfirmasi petani dulu, baru pembayaran.
    private function alurSampai(string $status, bool $ambilSendiri): array
    {
        $siap = $ambilSendiri ? 'siap_diambil' : 'dikirim';
        $awal = ['menunggu_konfirmasi', 'menunggu_pembayaran', 'diproses'];

        return match ($status) {
            'menunggu_konfirmasi' => ['menunggu_konfirmasi'],
            'ditolak', 'dibatalkan' => ['menunggu_konfirmasi', $status],
            'menunggu_pembayaran' => ['menunggu_konfirmasi', 'menunggu_pembayaran'],
            'diproses' => $awal,
            'dikirim', 'siap_diambil' => [...$awal, $status],
            'terkirim' => [...$awal, $siap, 'terkirim'],
            'selesai' => [...$awal, $siap, 'terkirim', 'selesai'],
            default => [$status],
        };
    }

    private function keteranganRiwayat(string $status, ?string $alasan): ?string
    {
        return match ($status) {
            'menunggu_konfirmasi' => 'Pesanan dibuat oleh pembeli.',
            'menunggu_pembayaran' => 'Pesanan diterima petani, menunggu pembayaran.',
            'diproses' => 'Pembayaran diterima, pesanan sedang disiapkan.',
            'dikirim' => 'Pesanan dikirim penjual.',
            'siap_diambil' => 'Pesanan siap diambil pembeli.',
            'terkirim' => 'Pesanan sampai di pembeli.',
            'selesai' => 'Pesanan diselesaikan pembeli.',
            'ditolak' => $alasan ?? 'Pesanan ditolak petani.',
            'dibatalkan' => $alasan ?? 'Pesanan dibatalkan.',
            default => null,
        };
    }
}