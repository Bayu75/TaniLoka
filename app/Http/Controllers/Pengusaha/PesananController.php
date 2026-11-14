<?php

namespace App\Http\Controllers\Pengusaha;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Support\Facades\Auth;

class PesananController extends Controller
{
    // Menampilkan daftar pesanan
    public function index()
    {
        $dataPesanan = Pesanan::with([
            'petani.profilPetani',
            'item.produk.fotoUtama',
        ])
        ->where('pengusaha_id', Auth::id())
        ->latest()
        ->get();

        $pesanan = $dataPesanan->map(function ($data) {
            return $this->formatPesanan($data);
        })->all();

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
        $data = Pesanan::with([
            'petani.profilPetani',
            'item.produk.fotoUtama',
            'riwayat',
            'pembayaran',
        ])
        ->where('pengusaha_id', Auth::id())
        ->where('kode', $id)
        ->firstOrFail();

        $pesanan = $this->formatPesanan($data);

        $dataPembatalan = $data->riwayat->firstWhere('status', 'dibatalkan');

        $tanggalDibatalkan = $dataPembatalan
            ? $dataPembatalan->created_at?->format('d M Y, H.i')
            : null;

        $alasanDibatalkan = $dataPembatalan?->keterangan;

        $riwayatStatus = $data->riwayat;

        return view('pengusaha.detail-pesanan', compact(
            'pesanan',
            'tanggalDibatalkan',
            'alasanDibatalkan',
            'riwayatStatus'
        ));
    }


    // Membatalkan pesanan
    public function cancel($id)
    {
        $pesanan = Pesanan::where('kode', $id)
            ->where('pengusaha_id', Auth::id())
            ->firstOrFail();

        if (!in_array($pesanan->status, [
            'menunggu_konfirmasi',
            'menunggu_pembayaran',
        ])) {
            return redirect()
                ->route('pengusaha.pesanan.detail', $id);
        }

        $pesanan->ubahStatus(
            'dibatalkan',
            'Pesanan dibatalkan oleh pembeli'
        );

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

        private function formatPesanan(Pesanan $data): array
        {
            $itemPertama = $data->item->first();
            $produk = $itemPertama?->produk;

            $jumlahKg = (float) $data->item->sum('jumlah_kg');

            $jumlahLabel = rtrim(
                rtrim(number_format($jumlahKg, 2, ',', '.'), '0'),
                ','
            );

            $namaPetani = $data->petani?->profilPetani?->nama_usaha_tani
                ?: ($data->petani?->name ?? '-');

            $notifikasi = match ($data->status) {
                'menunggu_konfirmasi' => 'Menunggu konfirmasi petani',
                'menunggu_pembayaran' => 'Menunggu pembayaran',
                'diproses' => 'Pesanan sedang diproses',
                'dikirim' => 'Pesanan sedang dikirim',
                'selesai' => 'Pesanan selesai',
                'dibatalkan' => 'Pesanan dibatalkan',
                'ditolak' => 'Pesanan ditolak',
                default => '-',
            };

            $gambar = $produk?->fotoUtama?->path;

            return [
                'id' => $data->kode,
                'petani_id' => $data->petani_id,
                'tanggal' => $data->created_at?->format('d M Y'),
                'status' => $data->status,
                'nama_petani' => $namaPetani,
                'produk' => $itemPertama?->nama_produk ?? '-',
                'gambar' => $gambar,
                'jumlah' => $jumlahLabel . ' kg',
                'total' => $data->total,
                'subtotal' => $data->subtotal,
                'ongkir' => $data->ongkir,
                'biaya_layanan' => $data->biaya_layanan,
                'notifikasi' => $notifikasi,
                'jenis_notifikasi' => in_array($data->status, [
                    'menunggu_konfirmasi',
                    'menunggu_pembayaran',
                ]) ? 'waktu' : null,
                'tindakan' => $data->status === 'menunggu_pembayaran'
                    ? 'Perlu tindakan: Unggah bukti pembayaran'
                    : null,
                'metode_pembayaran' => $data->pembayaran?->metode,
                'items' => $data->item,
            ];
        }

}
