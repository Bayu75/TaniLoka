<?php

namespace App\Http\Controllers\Pengusaha;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PesananController extends Controller
{
    /**
     * Daftar pesanan milik pembeli yang sedang login.
     */
    public function index()
    {
        $pesanan = Pesanan::with([
            'petani.profilPetani',
            'item.produk.fotoUtama',
            'pembayaran',
            'riwayat',
        ])
            ->where('pengusaha_id', auth()->id())
            ->latest()
            ->get();

        $jumlahPesanan = [
            'semua' => $pesanan->count(),
            'menunggu' => $pesanan->whereIn('status', [
                'menunggu_konfirmasi',
                'menunggu_pembayaran',
            ])->count(),
            'diproses' => $pesanan->where('status', 'diproses')->count(),
            'dikirim' => $pesanan->whereIn('status', [
                'dikirim',
                'siap_diambil',
                'terkirim',
            ])->count(),
            'selesai' => $pesanan->where('status', 'selesai')->count(),
            'dibatalkan' => $pesanan->whereIn('status', [
                'dibatalkan',
                'ditolak',
            ])->count(),
        ];

        return view('pengusaha.pesanan', compact(
            'pesanan',
            'jumlahPesanan'
        ));
    }

    /**
     * Detail pesanan.
     */
    public function show($id)
    {
        $pesanan = Pesanan::with([
            'petani.profilPetani',
            'item.produk.fotoUtama',
            'riwayat',
            'pembayaran',
            'ulasan',
        ])
            ->where('pengusaha_id', auth()->id())
            ->findOrFail($id);

        $riwayatStatus = $pesanan->riwayat->sortBy('created_at');

        $dataPembatalan = $riwayatStatus
            ->where('status', 'dibatalkan')
            ->last();

        $tanggalDibatalkan = $dataPembatalan
            ? $dataPembatalan->created_at?->format('d M Y, H.i')
            : null;

        $alasanDibatalkan = $dataPembatalan?->keterangan;

        return view('pengusaha.detail-pesanan', compact(
            'pesanan',
            'tanggalDibatalkan',
            'alasanDibatalkan',
            'riwayatStatus'
        ));
    }

    /**
     * Membuat pesanan dari checkout.
     * Satu petani mendapatkan satu pesanan.
     */
    public function store(Request $request)
    {
        $request->validate([
            'alamat_id' => ['required', 'integer'],
            'pengiriman' => ['required'],
            'pembayaran' => ['required', 'in:transfer,gopay,ovo,cod'],
        ]);

        $user = $request->user();

        $alamat = $user->alamat()
            ->whereKey($request->alamat_id)
            ->first();

        if (!$alamat) {
            return redirect()
                ->route('pengusaha.checkout')
                ->with('error', 'Alamat pengiriman tidak ditemukan.');
        }

        $pengiriman = $request->input('pengiriman');

        if (is_string($pengiriman)) {
            $pengiriman = json_decode($pengiriman, true);
        }

        if (!is_array($pengiriman)) {
            return back()->with('error', 'Pilihan pengiriman tidak valid.');
        }

        $metodePembayaran = [
            'transfer' => 'transfer_bank',
            'gopay' => 'gopay',
            'ovo' => 'ovo',
            'cod' => 'cod',
        ][$request->pembayaran];

        try {
            $daftarPesanan = DB::transaction(function () use (
                $user,
                $alamat,
                $pengiriman,
                $metodePembayaran
            ) {
                $keranjang = $user->keranjang()
                    ->with('produk.petani')
                    ->lockForUpdate()
                    ->get();

                if ($keranjang->isEmpty()) {
                    throw ValidationException::withMessages([
                        'keranjang' => 'Keranjang masih kosong.',
                    ]);
                }

                foreach ($keranjang as $item) {
                    $produk = $item->produk;

                    if (
                        !$produk ||
                        !$produk->aktif ||
                        $item->jumlah_kg <= 0 ||
                        $item->jumlah_kg > $produk->stok_kg
                    ) {
                        throw ValidationException::withMessages([
                            'keranjang' => 'Ada produk yang tidak tersedia atau stoknya berubah. Periksa keranjang.',
                        ]);
                    }
                }

                $perPetani = $keranjang->groupBy(
                    fn ($item) => $item->produk->petani_id
                );

                $hasil = [];

                foreach ($perPetani as $petaniId => $items) {
                    $pilihan = $pengiriman[$petaniId] ?? [
                        'metode' => 'diantar',
                        'kurir' => 'kendaraan',
                    ];

                    $ambilSendiri = ($pilihan['metode'] ?? 'diantar') === 'ambil';
                    $kurir = $pilihan['kurir'] ?? 'kendaraan';

                    if ($ambilSendiri) {
                        $labelPengiriman = 'ambil';
                        $ongkir = 0;
                    } elseif ($kurir === 'jnt') {
                        $labelPengiriman = 'jnt';
                        $ongkir = 12000;
                    } else {
                        $labelPengiriman = 'kendaraan';
                        $ongkir = 15000;
                    }

                    if (
                        $metodePembayaran === 'cod' &&
                        $ambilSendiri
                    ) {
                        throw ValidationException::withMessages([
                            'pembayaran' => 'COD hanya tersedia untuk pengiriman yang diantarkan petani.',
                        ]);
                    }

                    $subtotal = (int) $items->sum(
                        fn ($item) => $item->subtotal
                    );

                    $total = $subtotal + $ongkir;

                    $alamatLengkap = implode(', ', array_filter([
                        $alamat->alamat_lengkap,
                        $alamat->kecamatan,
                        $alamat->kabupaten,
                    ]));

                    $pesanan = Pesanan::create([
                        'kode' => Pesanan::buatKode(),
                        'pengusaha_id' => $user->id,
                        'petani_id' => $petaniId,
                        'nama_penerima' => $alamat->nama_penerima,
                        'no_hp_penerima' => $alamat->no_hp,
                        'alamat_pengiriman' => $alamatLengkap,
                        'catatan' => null,
                        'subtotal' => $subtotal,
                        'ongkir' => $ongkir,
                        'biaya_layanan' => 0,
                        'total' => $total,
                        'metode_pengiriman' => $labelPengiriman,
                        'status' => 'menunggu_konfirmasi',
                    ]);

                    foreach ($items as $item) {
                        $harga = (int) (
                            $item->harga_per_kg
                            ?? $item->produk->harga_per_kg
                        );

                        $pesanan->item()->create([
                            'produk_id' => $item->produk_id,
                            'nama_produk' => $item->produk->nama,
                            'harga_per_kg' => $harga,
                            'jumlah_kg' => $item->jumlah_kg,
                            'subtotal' => (int) round(
                                $harga * $item->jumlah_kg
                            ),
                        ]);
                    }

                    $pesanan->pembayaran()->create([
                        'metode' => $metodePembayaran,
                        'jumlah' => $total,
                        'status' => 'belum_bayar',
                    ]);

                    $pesanan->riwayat()->create([
                        'status' => 'menunggu_konfirmasi',
                        'keterangan' => 'Pesanan dibuat oleh pembeli.',
                        'diubah_oleh' => $user->id,
                        'created_at' => now(),
                    ]);

                    $hasil[] = $pesanan;
                }

                // Hapus keranjang hanya setelah seluruh pesanan berhasil dibuat.
                $user->keranjang()->delete();

                return $hasil;
            });
        } catch (ValidationException $e) {
            throw $e;
        }

        return redirect()
            ->route('pengusaha.pesanan')
            ->with(
                'success',
                count($daftarPesanan) . ' pesanan berhasil dibuat dan dikirim ke petani.'
            );
    }

    
    public function uploadBukti(Request $request, int $id)
    {
        $request->validate([
            'bukti_pembayaran' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
        ]);

        $userId = $request->user()->id;

        DB::transaction(function () use ($request, $id, $userId) {
            $pesanan = Pesanan::where('id', $id)
                ->where('pengusaha_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($pesanan->status !== 'menunggu_pembayaran') {
                throw ValidationException::withMessages([
                    'bukti_pembayaran' => 'Pesanan tidak sedang menunggu pembayaran.',
                ]);
            }

            $pembayaran = $pesanan->pembayaran;

            if (!$pembayaran) {
                throw ValidationException::withMessages([
                    'bukti_pembayaran' => 'Data pembayaran tidak ditemukan.',
                ]);
            }

            $fileLama = $pembayaran->bukti_path;

            $path = $request->file('bukti_pembayaran')
                ->store('bukti-pembayaran', 'public');

            $pembayaran->update([
                'bukti_path' => $path,
                'status' => 'lunas',
                'dibayar_pada' => now(),
            ]);

            $pesanan->update([
                'status' => 'diproses',
            ]);

            $pesanan->riwayat()->create([
                'status' => 'diproses',
                'keterangan' => 'Bukti pembayaran berhasil diunggah pembeli.',
                'diubah_oleh' => $userId,
            ]);

            if ($fileLama) {
                \Illuminate\Support\Facades\Storage::disk('public')
                    ->delete($fileLama);
            }
        });

        return back()->with(
            'success',
            'Bukti pembayaran berhasil diunggah. Pesanan sedang diproses.'
        );
    }


    public function konfirmasiSelesai($id)
    {
        $userId = auth()->id();

        $berhasil = DB::transaction(function () use ($id, $userId) {
            $pesanan = Pesanan::where('pengusaha_id', $userId)
                ->whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($pesanan->status, ['dikirim', 'siap_diambil', 'terkirim'])) {
                return false;
            }

            $pesanan->update([
                'status' => 'selesai',
            ]);

            $pesanan->riwayat()->create([
                'status' => 'selesai',
                'keterangan' => 'Pembeli mengonfirmasi pesanan telah diterima.',
                'diubah_oleh' => $userId,
                'created_at' => now(),
            ]);

            return true;
        });

        if (!$berhasil) {
            return redirect()
                ->route('pengusaha.pesanan.detail', $id)
                ->with('error', 'Pesanan belum dapat diselesaikan atau statusnya sudah berubah.');
        }

        return redirect()
            ->route('pengusaha.pesanan.detail', $id)
            ->with('success', 'Pesanan berhasil dikonfirmasi selesai. Silakan berikan ulasan.');
    }

    public function simpanUlasan(Request $request, int $id)
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'komentar' => ['nullable', 'string', 'max:1000'],
        ]);

        $userId = auth()->id();

        $berhasil = DB::transaction(function () use ($id, $userId, $data) {
            $pesanan = Pesanan::where('pengusaha_id', $userId)
                ->whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($pesanan->status !== 'selesai') {
                return false;
            }

            if ($pesanan->ulasan()->exists()) {
                return false;
            }

            $pesanan->ulasan()->create([
                'pengusaha_id' => $userId,
                'petani_id' => $pesanan->petani_id,
                'rating' => $data['rating'],
                'komentar' => $data['komentar'] ?? null,
            ]);

            return true;
        });

        if (!$berhasil) {
            return redirect()
                ->route('pengusaha.pesanan.detail', $id)
                ->with('error', 'Ulasan tidak dapat dikirim atau pesanan sudah memiliki ulasan.');
        }

        return redirect()
            ->route('pengusaha.pesanan.detail', $id)
            ->with('success', 'Terima kasih! Ulasan berhasil dikirim.');
    }

    /**
     * Pembeli membatalkan pesanan.
     */
    public function cancel($id)
    {
        $pesanan = Pesanan::where('pengusaha_id', auth()->id())
            ->findOrFail($id);

        if (!in_array($pesanan->status, [
            'menunggu_konfirmasi',
            'menunggu_pembayaran',
        ])) {
            return redirect()
                ->route('pengusaha.pesanan.detail', $id)
                ->with('error', 'Pesanan tidak dapat dibatalkan pada status ini.');
        }

        DB::transaction(function () use ($pesanan) {
            $pesanan = Pesanan::whereKey($pesanan->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($pesanan->status, [
                'menunggu_konfirmasi',
                'menunggu_pembayaran',
            ])) {
                throw ValidationException::withMessages([
                    'pesanan' => 'Status pesanan sudah berubah dan tidak dapat dibatalkan.',
                ]);
            }

            $pesanan->update([
                'status' => 'dibatalkan',
            ]);

            $pesanan->riwayat()->create([
                'status' => 'dibatalkan',
                'keterangan' => 'Pesanan dibatalkan oleh pembeli.',
                'diubah_oleh' => auth()->id(),
                'created_at' => now(),
            ]);
        });

        return redirect()
            ->route('pengusaha.pesanan.detail', $id)
            ->with('success', 'Pesanan berhasil dibatalkan.');
    }
}
