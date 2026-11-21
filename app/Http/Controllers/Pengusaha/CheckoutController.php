<?php

namespace App\Http\Controllers\Pengusaha;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /**
     * Menampilkan halaman checkout.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Pastikan akun pengusaha sudah terverifikasi
        if (!$user->terverifikasi()) {
            return redirect()
                ->route('pengusaha.keranjang')
                ->with('error', 'Akun harus terverifikasi sebelum checkout.');
        }

        // Ambil keranjang dari database
        $items = $user->keranjang()
            ->with([
                'produk.fotoUtama',
                'produk.petani.profilPetani',
            ])
            ->get();

        // Pastikan keranjang tidak kosong
        if ($items->isEmpty()) {
            return redirect()
                ->route('pengusaha.keranjang')
                ->with('error', 'Keranjang masih kosong.');
        }

        // Periksa ketersediaan produk dan stok
        foreach ($items as $item) {
            $produk = $item->produk;

            if (
                !$produk ||
                !$produk->aktif ||
                $item->jumlah_kg <= 0 ||
                $item->jumlah_kg > $produk->stok_kg
            ) {
                return redirect()
                    ->route('pengusaha.keranjang')
                    ->with(
                        'error',
                        'Ada produk yang sudah tidak tersedia atau stoknya berubah. Periksa kembali keranjang.'
                    );
            }
        }

        // Kelompokkan produk berdasarkan petani
        $perPetani = $items->groupBy(
            fn ($item) => $item->produk->petani_id
        );

        // Hitung subtotal berdasarkan harga keranjang
        // Harga negosiasi tetap diprioritaskan
        $subtotal = $items->sum(
            fn ($item) => $item->subtotal
        );

        // Ambil alamat milik pengguna
        $daftarAlamat = $user->alamat()
            ->orderByDesc('utama')
            ->orderByDesc('updated_at')
            ->get();

        // Tentukan alamat utama
        $alamatUtama = $daftarAlamat->firstWhere('utama', true)
            ?? $daftarAlamat->first();

        return view('pengusaha.checkout', compact(
            'items',
            'perPetani',
            'subtotal',
            'daftarAlamat',
            'alamatUtama'
        ));
    }


    /**
     * Menampilkan halaman pembayaran.
     */
    public function pembayaran(Request $request)
    {
        $data = $this->siapkanDataCheckout($request);

        if ($data instanceof \Illuminate\Http\RedirectResponse) {
            return $data;
        }

        return view('pengusaha.pembayaran', $data);
    }

    /**
     * Menampilkan halaman konfirmasi pesanan.
     */
    public function konfirmasi(Request $request)
    {
        $data = $this->siapkanDataCheckout($request);

        if ($data instanceof \Illuminate\Http\RedirectResponse) {
            return $data;
        }

        $data['metodeBayar'] = $request->input('pembayaran', 'transfer');

        return view('pengusaha.konfirmasi', $data);
    }

    /**
     * Menyiapkan data keranjang dan alamat untuk pembayaran
     * serta konfirmasi.
     */
    private function siapkanDataCheckout(Request $request)
    {
        $user = $request->user();

        $items = $user->keranjang()
            ->with([
                'produk.fotoUtama',
                'produk.petani.profilPetani',
            ])
            ->get();

        if ($items->isEmpty()) {
            return redirect()
                ->route('pengusaha.keranjang')
                ->with('error', 'Keranjang masih kosong.');
        }

        foreach ($items as $item) {
            $produk = $item->produk;

            if (
                !$produk ||
                !$produk->aktif ||
                $item->jumlah_kg <= 0 ||
                $item->jumlah_kg > $produk->stok_kg
            ) {
                return redirect()
                    ->route('pengusaha.keranjang')
                    ->with('error', 'Ada produk yang sudah tidak tersedia atau stoknya berubah.');
            }
        }

        $perPetani = $items->groupBy(
            fn ($item) => $item->produk->petani_id
        );

        $subtotal = $items->sum(
            fn ($item) => $item->subtotal
        );

        $daftarAlamat = $user->alamat()
            ->orderByDesc('utama')
            ->orderByDesc('updated_at')
            ->get();

        $alamatId = $request->input('alamat_id');

        $alamatAktif = $alamatId
            ? $daftarAlamat->firstWhere('id', (int) $alamatId)
            : ($daftarAlamat->firstWhere('utama', true) ?? $daftarAlamat->first());

        if (!$alamatAktif) {
            return redirect()
                ->route('pengusaha.alamat')
                ->with('error', 'Silakan tambahkan alamat pengiriman terlebih dahulu.');
        }

        $pengiriman = $request->input('pengiriman', []);

        if (is_string($pengiriman)) {
            $pengiriman = json_decode($pengiriman, true) ?? [];
        }

        $ongkosKirim = [
            'kendaraan' => 15000,
            'ambil' => 0,
        ];

        $totalOngkir = 0;

        foreach ($perPetani->keys() as $petaniId) {
            $pilihan = $pengiriman[$petaniId] ?? [
                'metode' => 'diantar',
                'kurir' => 'kendaraan',
            ];

            if (($pilihan['metode'] ?? 'diantar') === 'ambil') {
                continue;
            }

            $kurir = $pilihan['kurir'] ?? 'kendaraan';

            $totalOngkir += $ongkosKirim[$kurir] ?? 0;
        }

        // Hitung PPN 10% dari subtotal produk
        $totalPPN = $subtotal * 0.10;

        // Total pembayaran (subtotal + ongkir + PPN)
        $totalPembayaran = $subtotal + $totalOngkir + $totalPPN;

        return compact(
            'items',
            'perPetani',
            'subtotal',
            'daftarAlamat',
            'alamatAktif',
            'pengiriman',
            'totalOngkir',
            'totalPPN',
            'totalPembayaran'
        );
    }

}
