<?php

namespace App\Http\Controllers\Pengusaha;

use App\Http\Controllers\Controller;

class PesananController extends Controller
{
    // Menampilkan daftar pesanan
    public function index()
    {
        $pesanan = array_merge(
            session()->get('pesanan_baru', []),
            $this->dataPesanan()
        );
        foreach ($pesanan as &$item) {
            if (session()->get("pesanan_dibatalkan.{$item['id']}")) {
                $item['status'] = 'dibatalkan';
            }
        }

        unset($item);

        $jumlahPesanan = [
            'semua' => count($pesanan),
            'menunggu' => collect($pesanan)->whereIn('status', [
                'menunggu_konfirmasi',
                'menunggu_pembayaran',
            ])->count(),
            'diproses' => collect($pesanan)->where('status', 'diproses')->count(),
            'dikirim' => collect($pesanan)->where('status', 'dikirim')->count(),
            'selesai' => collect($pesanan)->where('status', 'selesai')->count(),
            'dibatalkan' => collect($pesanan)->where('status', 'dibatalkan')->count(),
        ];

        return view('pengusaha.pesanan', compact('pesanan', 'jumlahPesanan'));
    }

    // Menampilkan detail pesanan berdasarkan ID
    public function show($id)
    {
        $daftarPesanan = array_merge(
            session()->get('pesanan_baru', []),
            $this->dataPesanan()
        );
        $pesanan = collect($daftarPesanan)->firstWhere('id', $id);

        abort_if(!$pesanan, 404);

        $dataPembatalan = session()->get("pesanan_dibatalkan.$id", []);

        // Memperbaiki data pembatalan lama yang masih bernilai true
        if ($dataPembatalan === true) {
            $dataPembatalan = [
                'tanggal' => now()->format('d M, H.i'),
                'alasan' => 'Dibatalkan pembeli',
            ];

            session()->put("pesanan_dibatalkan.$id", $dataPembatalan);
        }

        if (!empty($dataPembatalan)) {
            $pesanan['status'] = 'dibatalkan';
        }

        $tanggalDibatalkan = $dataPembatalan['tanggal'] ?? null;
        $alasanDibatalkan = $dataPembatalan['alasan'] ?? null;

        return view('pengusaha.detail-pesanan', compact(
            'pesanan',
            'tanggalDibatalkan',
            'alasanDibatalkan'
        ));
    }


    // Membatalkan pesanan
    public function cancel($id)
    {
        $daftarPesanan = array_merge(
            session()->get('pesanan_baru', []),
            $this->dataPesanan()
        );

        $pesanan = collect($daftarPesanan)->firstWhere('id', $id);

        abort_if(!$pesanan, 404);

        if (!in_array($pesanan['status'], ['menunggu_konfirmasi', 'menunggu_pembayaran'])) {
            return redirect()
                ->route('pengusaha.pesanan.detail', $id);
        }

        session()->put("pesanan_dibatalkan.$id", [
            'tanggal' => now()->format('d M, H.i'),
            'alasan' => 'Dibatalkan pembeli',
        ]);

        return redirect()
            ->route('pengusaha.pesanan.detail', $id)
            ->with('success', 'Pesanan berhasil dibatalkan.');
    }


        // Menyimpan pesanan baru dari halaman konfirmasi
        public function store()
        {
            $metodePengiriman = request('metode', 'diantar');
            $kurir = request('kurir', 'kendaraan');
            $metodeBayar = request('pembayaran', 'transfer');

            $ongkir = $metodePengiriman === 'ambil'
                ? 0
                : ($kurir === 'jnt' ? 12000 : 0);

            $pesananBaru = [
                'id' => 'TL-' . now()->format('ymdHis'),
                'tanggal' => now()->format('d M Y'),
                'status' => 'menunggu_konfirmasi',
                'nama_petani' => 'Ibu Wayan Sari',
                'produk' => 'Cabai Rawit Merah',
                'gambar' => 'images/products/cabai rawit.jpg',
                'jumlah' => '2 kg',
                'total' => 90000 + $ongkir,
                'notifikasi' => 'Menunggu konfirmasi petani',
                'jenis_notifikasi' => 'waktu',
                'metode_pengiriman' => $metodePengiriman === 'ambil'
                    ? 'Ambil Sendiri'
                    : ($kurir === 'jnt' ? 'J&T Express' : 'Kendaraan Sendiri'),
                'metode_pembayaran' => $metodeBayar,
            ];

            $daftarPesanan = session()->get('pesanan_baru', []);

            array_unshift($daftarPesanan, $pesananBaru);

            session()->put('pesanan_baru', $daftarPesanan);

            return redirect()
                ->route('pengusaha.pesanan')
                ->with('success', 'Pesanan berhasil dikirim!');
        }

    // Sumber dummy data pesanan sementara
    private function dataPesanan()
    {
        return [
            [
                'id' => 'TL-0008',
                'tanggal' => '26 Okt 2026',
                'status' => 'menunggu_konfirmasi',
                'nama_petani' => 'Ibu Wayan Sari',
                'produk' => 'Cabai Rawit Merah',
                'gambar' => 'images/products/cabai rawit.jpg',
                'jumlah' => '5 kg',
                'total' => 237000,
                'notifikasi' => 'Batas konfirmasi: 21 jam 58 menit',
                'jenis_notifikasi' => 'waktu',
            ],
            [
                'id' => 'TL-0006',
                'tanggal' => '26 Okt 2026',
                'status' => 'menunggu_pembayaran',
                'nama_petani' => 'Ibu Nyoman Dewi',
                'produk' => 'Kangkung Segar',
                'gambar' => 'images/products/kangkung.png',
                'jumlah' => '20 kg',
                'total' => 110000,
                'notifikasi' => 'Batas pembayaran: 1 menit 44 detik',
                'jenis_notifikasi' => 'waktu',
                'tindakan' => 'Perlu tindakan: Unggah bukti pembayaran',
            ],
            [
                'id' => 'TL-0004',
                'tanggal' => '23 Okt 2026',
                'status' => 'dikirim',
                'nama_petani' => 'Pak Ketut Wirasa',
                'produk' => 'Tomat Segar Grade A',
                'gambar' => 'images/products/tomat.jpg',
                'jumlah' => '20 kg',
                'total' => 260000,
            ],
            [
                'id' => 'TL-0001',
                'tanggal' => '14 Okt 2026',
                'status' => 'selesai',
                'nama_petani' => 'Pak Made Sukartha',
                'produk' => 'Bayam Hijau Organik',
                'gambar' => 'images/products/bayam.png',
                'jumlah' => '10 kg',
                'total' => 100000,
                'tindakan' => 'Perlu tindakan: Beri penilaian',
            ],
        ];
    }
}
