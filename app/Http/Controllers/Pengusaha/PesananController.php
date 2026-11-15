<?php

namespace App\Http\Controllers\Pengusaha;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Alamat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
    public function show(string $id)
    {
        $data = Pesanan::with([
            'petani.profilPetani',
            'item.produk.fotoUtama',
            'riwayat',
            'pembayaran',
            'ulasan',
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
    public function cancel(string $id)
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

    public function prosesCheckoutKeranjang(Request $request)
    {
        $request->validate([
            'items' => 'required|string',
        ]);

        $items = json_decode($request->items, true);

        if (!is_array($items) || empty($items)) {
            return back()->with('error', 'Pilih minimal satu produk.');
        }

        $checkoutItems = [];

        foreach ($items as $item) {
            if (
                !isset($item['slug'], $item['jumlah']) ||
                !is_numeric($item['jumlah']) ||
                $item['jumlah'] <= 0
            ) {
                return back()->with('error', 'Data produk tidak valid.');
            }

            $produk = Produk::aktif()
                ->where('slug', $item['slug'])
                ->first();

            if (!$produk) {
                return back()->with('error', 'Produk tidak ditemukan atau tidak tersedia.');
            }

            $jumlah = (float) $item['jumlah'];

            if ($jumlah < $produk->min_order_kg) {
                return back()->with('error', 'Jumlah pesanan tidak memenuhi minimum pembelian.');
            }

            if ($jumlah > $produk->stok_kg) {
                return back()->with('error', 'Jumlah pesanan melebihi stok produk.');
            }

            $checkoutItems[$produk->slug] = $jumlah;
        }

        session()->put('checkout_keranjang', $checkoutItems);

        return redirect()->route('pengusaha.checkout');
    }

        // Menyimpan pesanan baru dari halaman konfirmasi
        public function store(Request $request)
        {
            $request->validate([
                'alamat_id' => 'required|integer',
                'pengiriman' => 'required|string',
                'pembayaran' => 'required|in:transfer,gopay,ovo,cod',
            ]);

            // Ambil data checkout dari session
            $checkoutKeranjang = session()->get('checkout_keranjang', []);
            $checkoutLangsung = session()->get('checkout_langsung');

            if (!empty($checkoutKeranjang)) {
                $daftarCheckout = $checkoutKeranjang;
            } elseif ($checkoutLangsung) {
                $daftarCheckout = [
                    $checkoutLangsung['slug'] => $checkoutLangsung['jumlah'],
                ];
            } else {
                return redirect()
                    ->route('pengusaha.keranjang')
                    ->with('error', 'Data checkout tidak ditemukan.');
            }

            // Periksa alamat penerima
            $alamat = Alamat::where('user_id', Auth::id())
                ->where('id', $request->alamat_id)
                ->first();

            if (!$alamat) {
                return back()->with('error', 'Alamat pengiriman tidak ditemukan.');
            }

            // Ambil pilihan pengiriman setiap petani
            $pilihanPengiriman = json_decode($request->pengiriman, true);

            if (!is_array($pilihanPengiriman)) {
                return back()->with('error', 'Data pengiriman tidak valid.');
            }

            // Mapping metode pembayaran
            $metodePembayaran = [
                'transfer' => 'transfer_bank',
                'gopay' => 'gopay',
                'ovo' => 'ovo',
                'cod' => 'cod',
            ];

             try {
                DB::transaction(function () use (
                    $daftarCheckout,
                    $pilihanPengiriman,
                    $metodePembayaran,
                    $request,
                    $alamat
                ) {

                    // Ambil produk dan kunci stok selama transaksi
                    $produkDatabase = Produk::aktif()
                        ->whereIn('slug', array_keys($daftarCheckout))
                        ->lockForUpdate()
                        ->get()
                        ->keyBy('slug');

                    // Validasi dan siapkan produk checkout
                    $itemsCheckout = collect($daftarCheckout)->map(
                        function ($jumlah, $slug) use ($produkDatabase) {

                            $produk = $produkDatabase->get($slug);
                            $jumlah = (float) $jumlah;

                            if (!$produk) {
                                throw \Illuminate\Validation\ValidationException::withMessages([
                                    'produk' => 'Produk tidak ditemukan atau tidak tersedia.',
                                ]);
                            }

                            if (
                                $jumlah <= 0 ||
                                $jumlah < $produk->min_order_kg ||
                                $jumlah > $produk->stok_kg
                            ) {
                                throw \Illuminate\Validation\ValidationException::withMessages([
                                    'produk' => 'Jumlah pembelian tidak sesuai dengan stok atau minimum pembelian.',
                                ]);
                            }

                            return [
                                'produk' => $produk,
                                'jumlah' => $jumlah,
                                'subtotal' => (int) round(
                                    $produk->harga_per_kg * $jumlah
                                ),
                            ];
                        }
                    )->values();;

                    // Kelompokkan produk berdasarkan petani
                    $kelompokPetani = $itemsCheckout->groupBy(
                        fn($item) => $item['produk']->petani_id
                    );

                    // Validasi seluruh pilihan pengiriman
                    $pengirimanPerPetani = [];

                    foreach ($kelompokPetani as $petaniId => $itemsPetani) {

                        $pilihan = $pilihanPengiriman[$petaniId] ?? null;

                        if (
                            !is_array($pilihan) ||
                            !in_array($pilihan['metode'] ?? '', ['diantar', 'ambil'])
                        ) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'pengiriman' => 'Pilihan pengiriman petani belum lengkap.',
                            ]);
                        }

                        $metode = $pilihan['metode'];
                        $kurir = $pilihan['kurir'] ?? null;

                        if (
                            $metode === 'diantar' &&
                            !in_array($kurir, ['kendaraan', 'jnt'])
                        ) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'pengiriman' => 'Pilihan kurir tidak valid.',
                            ]);
                        }

                        $biayaOngkir = $metode === 'ambil'
                            ? 0
                            : ($kurir === 'jnt' ? 12000 : 15000);

                        $pengirimanPerPetani[$petaniId] = [
                            'metode' => $metode === 'ambil'
                                ? 'ambil'
                                : $kurir,
                            'ongkir' => $biayaOngkir,
                        ];
                    }

                    // Validasi COD
                    $metodeBayar = $request->pembayaran;

                    $codTersedia = collect($pengirimanPerPetani)->isNotEmpty()
                        && collect($pengirimanPerPetani)->every(
                            fn($pilihan) => $pilihan['metode'] === 'kendaraan'
                        );

                    if ($metodeBayar === 'cod' && !$codTersedia) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'pembayaran' => 'COD hanya tersedia jika semua petani menggunakan Kendaraan Petani.',
                        ]);
                    }

                    // Buat satu pesanan untuk setiap petani
                    foreach ($kelompokPetani as $petaniId => $itemsPetani) {

                        $pengiriman = $pengirimanPerPetani[$petaniId];

                        $subtotal = (int) $itemsPetani->sum('subtotal');
                        $ongkir = $pengiriman['ongkir'];
                        $biayaLayanan = 0;
                        $total = $subtotal + $ongkir + $biayaLayanan;

                        // Simpan pesanan utama
                        $pesanan = Pesanan::create([
                            'kode' => Pesanan::buatKode(),
                            'pengusaha_id' => Auth::id(),
                            'petani_id' => $petaniId,

                            'nama_penerima' => $alamat->nama_penerima,
                            'no_hp_penerima' => $alamat->no_hp,

                            'alamat_pengiriman' => implode(', ', array_filter([
                                $alamat->alamat_lengkap,
                                $alamat->kecamatan,
                                $alamat->kabupaten,
                                $alamat->kode_pos,
                            ])),

                            'metode_pengiriman' => $pengiriman['metode'],

                            'subtotal' => $subtotal,
                            'ongkir' => $ongkir,
                            'biaya_layanan' => $biayaLayanan,
                            'total' => $total,

                            'status' => 'menunggu_konfirmasi',
                        ]);

                        // Simpan setiap produk dalam pesanan
                        foreach ($itemsPetani as $item) {

                            $produk = $item['produk'];

                            $pesanan->item()->create([
                                'produk_id' => $produk->id,
                                'nama_produk' => $produk->nama,
                                'harga_per_kg' => $produk->harga_per_kg,
                                'jumlah_kg' => $item['jumlah'],
                                'subtotal' => $item['subtotal'],
                            ]);

                            // Kurangi stok produk
                            $produk->decrement('stok_kg', $item['jumlah']);
                        }

                        // Simpan pembayaran
                        $pesanan->pembayaran()->create([
                            'metode' => $metodePembayaran[$metodeBayar],
                            'jumlah' => $total,
                            'status' => 'belum_bayar',
                        ]);

                        // Catat riwayat status pesanan
                        $pesanan->riwayat()->create([
                            'status' => 'menunggu_konfirmasi',
                            'keterangan' => 'Pesanan berhasil dibuat oleh pembeli.',
                            'diubah_oleh' => Auth::id(),
                        ]);
                    }
                });

            } catch (\Illuminate\Validation\ValidationException $e) {
                throw $e;

            } catch (\Throwable $e) {
                report($e);

                return back()->with(
                    'error',
                    'Pesanan gagal disimpan. Silakan coba kembali.'
                );
            }

            // Hapus produk yang sudah berhasil dibeli dari keranjang database
            \App\Models\KeranjangItem::where('user_id', Auth::id())
                ->whereHas('produk', function ($query) use ($daftarCheckout) {
                    $query->whereIn('slug', array_keys($daftarCheckout));
                })
                ->delete();

            // Bersihkan data checkout
            session()->forget([
                'checkout_keranjang',
                'checkout_langsung',
            ]);

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
                $noWaPetani = $data->petani?->no_wa;

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

            $metodePengiriman = match ($data->metode_pengiriman) {
                'jnt' => 'J&T Express',
                'kendaraan' => 'Kendaraan Petani',
                'ambil' => 'Ambil Sendiri',
                default => 'Belum ditentukan',
            };

            $metodePembayaran = match ($data->pembayaran?->metode) {
                'transfer_bank' => 'Transfer Bank',
                'gopay' => 'GoPay',
                'ovo' => 'OVO',
                'cod' => 'Bayar di Tempat (COD)',
                default => 'Belum ditentukan',
            };

            $profilPetani = $data->petani?->profilPetani;

$lokasiPetani = implode(', ', array_filter([
                $profilPetani?->desa,
                $profilPetani?->kecamatan,
                $profilPetani?->kabupaten,
            ]));

            return [
                'id' => $data->kode,
                'petani_id' => $data->petani_id,
                'tanggal' => $data->created_at?->format('d M Y'),
                'status' => $data->status,
                'nama_petani' => $namaPetani,
                'no_wa_petani' => $noWaPetani,
                'produk' => $itemPertama?->nama_produk ?? '-',
                'gambar' => $gambar,
                'jumlah' => $jumlahLabel . ' kg',
                'total' => $data->total,
                'jumlah_pembayaran' => $data->pembayaran?->jumlah ?? $data->total,
                'subtotal' => $data->subtotal,
                'ongkir' => $data->ongkir,
                'biaya_layanan' => $data->biaya_layanan,
                'nama_penerima' => $data->nama_penerima,
                'no_hp_penerima' => $data->no_hp_penerima,
                'alamat_pengiriman' => $data->alamat_pengiriman,
                'metode_pengiriman' => $data->metode_pengiriman,
                'label_pengiriman' => $metodePengiriman,
                'lokasi_petani' => $lokasiPetani,
                'metode_pembayaran' => $data->pembayaran?->metode,
                'label_pembayaran' => $metodePembayaran,
                'status_pembayaran' => $data->pembayaran?->status,
                'bukti_path' => $data->pembayaran?->bukti_path,
                'notifikasi' => $notifikasi,
                'jenis_notifikasi' => in_array($data->status, [
                    'menunggu_konfirmasi',
                    'menunggu_pembayaran',
                ]) ? 'waktu' : null,
                'tindakan' => $data->status === 'menunggu_pembayaran'
                    ? 'Perlu tindakan: Unggah bukti pembayaran'
                    : null,
                'items' => $data->item,
                'sudah_dinilai' => $data->ulasan !== null,
                'rating' => $data->ulasan?->rating,
                'komentar_ulasan' => $data->ulasan?->komentar,
            ];
        }

    // Mengunggah bukti pembayaran
    public function uploadBukti(Request $request, string $id)
    {
        $request->validate([
            'bukti_transfer' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $pesanan = Pesanan::with('pembayaran')
            ->where('kode', $id)
            ->where('pengusaha_id', Auth::id())
            ->firstOrFail();

        if ($pesanan->status !== 'menunggu_pembayaran') {
            return back()->with('error', 'Pesanan tidak dapat menerima bukti pembayaran.');
        }

        $pembayaran = $pesanan->pembayaran;

        if (!$pembayaran) {
            abort(404, 'Data pembayaran tidak ditemukan.');
        }

        // Simpan foto ke storage
        $path = $request->file('bukti_transfer')
            ->store('bukti-pembayaran', 'public');

        // Hapus foto sebelumnya jika ada
        if ($pembayaran->bukti_path) {
            Storage::disk('public')->delete($pembayaran->bukti_path);
        }

        // Simpan lokasi foto ke database
        $pembayaran->update([
            'bukti_path' => $path,
            'status' => 'menunggu_verifikasi',
        ]);

        return redirect()
            ->route('pengusaha.pesanan.detail', $id)
            ->with('success', 'Bukti pembayaran berhasil diunggah.');
    }

    // Menyimpan penilaian dari pengusaha
    public function storeUlasan(Request $request, string $id)
    {
        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string|max:2000',
        ]);

        $pesanan = Pesanan::where('kode', $id)
            ->where('pengusaha_id', Auth::id())
            ->firstOrFail();

        // Penilaian hanya bisa diberikan untuk pesanan selesai
        if ($pesanan->status !== 'selesai') {
            return back()->with('error', 'Pesanan belum selesai dan belum dapat dinilai.');
        }

        // Cegah penilaian lebih dari satu kali
        if ($pesanan->ulasan()->exists()) {
            return back()->with('error', 'Pesanan ini sudah diberikan penilaian.');
        }

        // Simpan penilaian ke database
        $pesanan->ulasan()->create([
            'pengusaha_id' => Auth::id(),
            'petani_id' => $pesanan->petani_id,
            'rating' => $data['rating'],
            'komentar' => $data['komentar'],
        ]);

        return redirect()
            ->route('pengusaha.pesanan.detail', $id)
            ->with('success', 'Terima kasih! Penilaian berhasil dikirim.');
    }
}
