
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Pengusaha\PesananController;
use App\Http\Controllers\Pengusaha\ChatController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'auth.landing')->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'formLogin'])->name('masuk');
    Route::post('/masuk', [AuthController::class, 'login'])->name('masuk.proses');
    Route::get('/daftar', [AuthController::class, 'formRegister'])->name('daftar');
    Route::post('/daftar', [AuthController::class, 'register'])->name('daftar.proses');

    // ini untuk demo masuk/login cepat
    Route::post('/masuk/demo', [AuthController::class, 'masukDemo'])->name('masuk.demo');
    });


    Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('keluar');

    Route::middleware(['auth', 'peran:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::view('/verifikasi', 'admin.verifikasi')->name('verifikasi');
    });

    Route::middleware(['auth', 'peran:pengusaha'])->prefix('pengusaha')->name('pengusaha.')->group(function () {

    Route::view('/', 'pengusaha.beranda')->name('beranda');
    Route::get('/katalog', [\App\Http\Controllers\Pengusaha\KatalogController::class, 'index'])
    ->name('katalog');

    Route::get('/produk/{slug}', [
    \App\Http\Controllers\Pengusaha\KatalogController::class,
    'show'
    ])->name('produk.detail');

    // Halaman toko petani
    Route::get('/toko/{petani}', function ($petani) {

        $daftarPetani = [
            'pak-made-sukartha',
            'ibu-wayan-sari',
            'pak-ketut-wirasa',
            'ibu-nyoman-dewi',
            'pak-gede-setiawan',
        ];

        abort_unless(in_array($petani, $daftarPetani), 404);

        return view('pengusaha.toko-petani', compact('petani'));
    })->name('toko');

    Route::get('/pesan', [ChatController::class, 'index'])->name('pesan');
    Route::get('/pesan/{id}', [ChatController::class, 'show'])->name('pesan.detail');
    Route::post('/pesan/{id}', [ChatController::class, 'kirim'])->name('pesan.kirim');
    Route::post('/pesan/{id}/penawaran', [ChatController::class, 'kirimPenawaran'])->name('pesan.penawaran');

    // Menambahkan produk ke keranjang dengan validasi stok
    Route::post('/keranjang/tambah', function (\Illuminate\Http\Request $request) {

        $request->validate([
            'slug' => 'required|string',
            'jumlah' => 'required|integer|min:1',
        ]);

        // Ambil produk langsung dari database
        $produk = \App\Models\Produk::aktif()
            ->where('slug', $request->slug)
            ->first();

        if (!$produk) {
            return back()->withErrors([
                'jumlah' => 'Produk tidak ditemukan atau stok habis.',
            ])->withInput();
        }

        $jumlah = (int) $request->jumlah;

        // Validasi minimal pembelian
        if ($jumlah < $produk->min_order_kg) {
            return back()->withErrors([
                'jumlah' => 'Minimal pembelian adalah ' . $produk->min_order_kg . ' kg.',
            ])->withInput();
        }

        // Jika pengguna memilih Beli Sekarang
        if ($request->aksi === 'beli') {

            if ($jumlah > $produk->stok_kg) {
                return back()->withErrors([
                    'jumlah' => 'Jumlah pembelian melebihi stok yang tersedia.',
                ])->withInput();
            }

            session()->put('checkout_langsung', [
                'slug' => $produk->slug,
                'jumlah' => $jumlah,
            ]);

            return redirect()->route('pengusaha.checkout');
        }

        // Jika pengguna memilih Tambah ke Keranjang
        $keranjang = session()->get('keranjang', []);

        $totalJumlah = ($keranjang[$produk->slug] ?? 0) + $jumlah;

        if ($totalJumlah > $produk->stok_kg) {
            return back()->withErrors([
                'jumlah' => 'Jumlah pembelian melebihi stok yang tersedia.',
            ])->withInput();
        }

        $keranjang[$produk->slug] = $totalJumlah;

        session()->put('keranjang', $keranjang);

        return redirect()
            ->route('pengusaha.keranjang')
            ->with('sukses', 'Produk berhasil ditambahkan ke keranjang!');

    })->name('keranjang.tambah');

    // Mengubah jumlah produk di keranjang dengan validasi stok
    Route::post('/keranjang/update', function (\Illuminate\Http\Request $request) {

        $request->validate([
            'slug' => 'required|string',
            'jumlah' => 'required|integer|min:1',
        ]);

        // Data minimal pembelian dan stok produk
        $produkList = [
            'bayam-hijau-organik' => ['minimal' => 5, 'stok' => 120],
            'cabai-rawit-merah' => ['minimal' => 2, 'stok' => 85],
            'tomat-segar-grade-a' => ['minimal' => 10, 'stok' => 300],
            'kangkung-segar' => ['minimal' => 10, 'stok' => 200],
            'bawang-putih-lokal' => ['minimal' => 5, 'stok' => 150],
            'kembang-kol-putih' => ['minimal' => 5, 'stok' => 60],
            'wortel-organik' => ['minimal' => 5, 'stok' => 90],
            'paprika-merah' => ['minimal' => 3, 'stok' => 0],
        ];

        $slug = $request->slug;
        $jumlah = (int) $request->jumlah;

        // Pastikan produk terdaftar
        if (!isset($produkList[$slug])) {
            return back()->withErrors([
                'jumlah' => 'Produk tidak ditemukan.',
            ]);
        }

        $produk = $produkList[$slug];

        // Pastikan stok tersedia
        if ($produk['stok'] <= 0) {
            return back()->withErrors([
                'jumlah' => 'Maaf, stok produk sedang habis.',
            ]);
        }

        // Validasi minimal pembelian
        if ($jumlah < $produk['minimal']) {
            return back()->withErrors([
                'jumlah' => 'Minimal pembelian adalah ' . $produk['minimal'] . ' kg.',
            ]);
        }

        // Validasi maksimal stok
        if ($jumlah > $produk['stok']) {
            return back()->withErrors([
                'jumlah' => 'Jumlah pembelian melebihi stok yang tersedia.',
            ]);
        }

        $keranjang = session()->get('keranjang', []);

        if (!isset($keranjang[$slug])) {
            return back()->withErrors([
                'jumlah' => 'Produk tidak ditemukan di keranjang.',
            ]);
        }

        $keranjang[$slug] = $jumlah;

        session()->put('keranjang', $keranjang);

        return redirect()
            ->route('pengusaha.keranjang')
            ->with('sukses', 'Jumlah produk berhasil diperbarui!');
    })->name('keranjang.update');

    // Menghapus produk dari keranjang
    Route::post('/keranjang/hapus', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'slug' => 'required|string',
        ]);

        $keranjang = session()->get('keranjang', []);

        unset($keranjang[$request->slug]);

        session()->put('keranjang', $keranjang);

        return redirect()
            ->route('pengusaha.keranjang')
            ->with('sukses', 'Produk berhasil dihapus dari keranjang!');
    })->name('keranjang.hapus');

    Route::view('/keranjang', 'pengusaha.keranjang')->name('keranjang');

    Route::get('/checkout', function () {

        // Prioritaskan data checkout dari keranjang
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
                ->with('error', 'Silakan pilih produk terlebih dahulu.');
        }

        // Ambil semua produk dari database
        $produkDatabase = \App\Models\Produk::with([
            'petani.profilPetani',
            'fotoUtama',
        ])
        ->aktif()
        ->whereIn('slug', array_keys($daftarCheckout))
        ->get()
        ->keyBy('slug');

        // Validasi produk dan jumlah pembelian
        $itemsCheckout = collect($daftarCheckout)->map(
            function ($jumlah, $slug) use ($produkDatabase) {

                $produk = $produkDatabase->get($slug);
                $jumlah = (float) $jumlah;

                if (!$produk) {
                    abort(redirect()
                        ->route('pengusaha.keranjang')
                        ->with('error', 'Ada produk yang sudah tidak tersedia.'));
                }

                if (
                    $jumlah < $produk->min_order_kg ||
                    $jumlah > $produk->stok_kg
                ) {
                    abort(redirect()
                        ->route('pengusaha.keranjang')
                        ->with('error', 'Jumlah pesanan tidak sesuai dengan stok atau minimum pembelian.'));
                }

                return [
                    'produk' => $produk,
                    'jumlah' => $jumlah,
                    'subtotal' => $produk->harga_per_kg * $jumlah,
                ];
            }
        )->values();

        // Hitung subtotal seluruh produk
        $subtotal = $itemsCheckout->sum('subtotal');

        // Data alamat pembeli
        $daftarAlamat = \App\Models\Alamat::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->orderByDesc('utama')
            ->get()
            ->map(function ($alamat) {
                return [
                    'id' => $alamat->id,
                    'nama' => $alamat->label ?: $alamat->nama_penerima,
                    'label' => $alamat->utama ? 'Utama' : '',
                    'kontak' => $alamat->nama_penerima . ' · ' . $alamat->no_hp,
                    'alamat' => implode(', ', array_filter([
                        $alamat->alamat_lengkap,
                        $alamat->kecamatan,
                        $alamat->kabupaten,
                        $alamat->kode_pos,
                    ])),
                ];
            })
            ->values();

        // Variabel produk pertama dipertahankan sementara
        // agar tampilan lama tetap bisa dibaca saat kita memperbarui Blade
        $produkCheckout = $itemsCheckout->first()['produk'];
        $jumlahCheckout = $itemsCheckout->first()['jumlah'];

        return view('pengusaha.checkout', compact(
            'itemsCheckout',
            'produkCheckout',
            'jumlahCheckout',
            'subtotal',
            'daftarAlamat'
        ));

    })->name('checkout');

    Route::post('/checkout/proses', [PesananController::class, 'prosesCheckoutKeranjang'])
    ->name('checkout.proses');

    Route::get('/pembayaran', function (\Illuminate\Http\Request $request) {

        // Ambil data checkout
        $checkoutKeranjang = session()->get('checkout_keranjang', []);
        $checkoutLangsung = session()->get('checkout_langsung');

        if (!empty($checkoutKeranjang)) {
            $daftarCheckout = $checkoutKeranjang;
        } elseif ($checkoutLangsung) {
            $daftarCheckout = [
                $checkoutLangsung['slug'] => $checkoutLangsung['jumlah'],
            ];
        } else {
            return redirect()->route('pengusaha.keranjang');
        }

        // Ambil produk dari database
        $produkDatabase = \App\Models\Produk::with([
            'petani.profilPetani',
            'fotoUtama',
        ])
        ->aktif()
        ->whereIn('slug', array_keys($daftarCheckout))
        ->get()
        ->keyBy('slug');

        // Siapkan seluruh produk checkout
        $itemsCheckout = collect($daftarCheckout)->map(
            function ($jumlah, $slug) use ($produkDatabase) {

                $produk = $produkDatabase->get($slug);
                $jumlah = (float) $jumlah;

                if (!$produk) {
                    abort(404, 'Produk tidak ditemukan.');
                }

                if (
                    $jumlah < $produk->min_order_kg ||
                    $jumlah > $produk->stok_kg
                ) {
                    abort(422, 'Jumlah pesanan tidak sesuai dengan stok atau minimum pembelian.');
                }

                return [
                    'produk' => $produk,
                    'jumlah' => $jumlah,
                    'subtotal' => $produk->harga_per_kg * $jumlah,
                ];
            }
        )->values();

        $subtotal = $itemsCheckout->sum('subtotal');

        $alamat = \App\Models\Alamat::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->where('id', $request->alamat_id)
            ->first();

        if (!$alamat) {
            return redirect()
                ->route('pengusaha.checkout')
                ->with('error', 'Silakan pilih alamat pengiriman.');
        }

        // Ambil pilihan pengiriman setiap petani
        $pilihanPengiriman = json_decode(
            $request->query('pengiriman', ''),
            true
        );

        if (!is_array($pilihanPengiriman)) {
            return redirect()
                ->route('pengusaha.checkout')
                ->with('error', 'Data metode pengiriman tidak valid.');
        }

        // Kelompokkan produk berdasarkan petani
        $kelompokPetani = $itemsCheckout->groupBy(
            fn($item) => $item['produk']->petani_id
        );

        $pengirimanPerPetani = [];
        $ongkirPerPetani = [];
        $ongkir = 0;

        foreach ($kelompokPetani as $petaniId => $itemsPetani) {

            $pilihan = $pilihanPengiriman[$petaniId] ?? null;

            if (
                !is_array($pilihan) ||
                !in_array($pilihan['metode'] ?? '', ['diantar', 'ambil'])
            ) {
                return redirect()
                    ->route('pengusaha.checkout')
                    ->with('error', 'Pilihan pengiriman salah satu petani belum valid.');
            }

            $metode = $pilihan['metode'];
            $kurir = $pilihan['kurir'] ?? 'kendaraan';

            if (
                $metode === 'diantar' &&
                !in_array($kurir, ['kendaraan', 'jnt'])
            ) {
                return redirect()
                    ->route('pengusaha.checkout')
                    ->with('error', 'Pilihan kurir tidak valid.');
            }

            $biayaOngkir = $metode === 'ambil'
                ? 0
                : ($kurir === 'jnt' ? 12000 : 15000);

            $pengirimanPerPetani[$petaniId] = [
                'metode' => $metode,
                'kurir' => $metode === 'ambil' ? null : $kurir,
                'ongkir' => $biayaOngkir,
            ];

            $ongkirPerPetani[$petaniId] = $biayaOngkir;

            $ongkir += $biayaOngkir;
        }

        // Hitung total seluruh checkout
        $total = $subtotal + $ongkir;

        // Variabel kompatibilitas sementara
        $pengirimanPertama = reset($pengirimanPerPetani);
        $metodePengiriman = $pengirimanPertama['metode'];
        $kurir = $pengirimanPertama['kurir'] ?? 'kendaraan';

        $produkCheckout = $itemsCheckout->first()['produk'];
        $jumlahCheckout = $itemsCheckout->first()['jumlah'];

        return view('pengusaha.pembayaran', compact(
            'itemsCheckout',
            'produkCheckout',
            'jumlahCheckout',
            'subtotal',
            'ongkir',
            'ongkirPerPetani',
            'pengirimanPerPetani',
            'total',
            'alamat',
            'metodePengiriman',
            'kurir'
        ));

    })->name('pembayaran');

    Route::get('/konfirmasi', function (\Illuminate\Http\Request $request) {

        // Ambil data checkout
        $checkoutKeranjang = session()->get('checkout_keranjang', []);
        $checkoutLangsung = session()->get('checkout_langsung');

        if (!empty($checkoutKeranjang)) {
            $daftarCheckout = $checkoutKeranjang;
        } elseif ($checkoutLangsung) {
            $daftarCheckout = [
                $checkoutLangsung['slug'] => $checkoutLangsung['jumlah'],
            ];
        } else {
            return redirect()->route('pengusaha.keranjang');
        }

        // Ambil produk dari database
        $produkDatabase = \App\Models\Produk::with([
            'petani.profilPetani',
            'fotoUtama',
        ])
        ->aktif()
        ->whereIn('slug', array_keys($daftarCheckout))
        ->get()
        ->keyBy('slug');

        // Siapkan produk checkout
        $itemsCheckout = collect($daftarCheckout)->map(
            function ($jumlah, $slug) use ($produkDatabase) {

                $produk = $produkDatabase->get($slug);
                $jumlah = (float) $jumlah;

                if (!$produk) {
                    abort(404, 'Produk tidak ditemukan.');
                }

                if (
                    $jumlah < $produk->min_order_kg ||
                    $jumlah > $produk->stok_kg
                ) {
                    abort(422, 'Jumlah pesanan tidak sesuai dengan stok atau minimum pembelian.');
                }

                return [
                    'produk' => $produk,
                    'jumlah' => $jumlah,
                    'subtotal' => $produk->harga_per_kg * $jumlah,
                ];
            }
        )->values();

        $subtotal = $itemsCheckout->sum('subtotal');

        // Periksa alamat pengguna
        $alamat = \App\Models\Alamat::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->where('id', $request->alamat_id)
            ->first();

        if (!$alamat) {
            return redirect()
                ->route('pengusaha.checkout')
                ->with('error', 'Silakan pilih alamat pengiriman.');
        }

        // Ambil pilihan pengiriman per petani
        $pilihanPengiriman = json_decode(
            $request->query('pengiriman', ''),
            true
        );

        if (!is_array($pilihanPengiriman)) {
            return redirect()
                ->route('pengusaha.checkout')
                ->with('error', 'Data pengiriman tidak valid.');
        }

        // Kelompokkan produk berdasarkan petani
        $kelompokPetani = $itemsCheckout->groupBy(
            fn($item) => $item['produk']->petani_id
        );

        $pengirimanPerPetani = [];
        $ongkirPerPetani = [];
        $ongkir = 0;

        foreach ($kelompokPetani as $petaniId => $itemsPetani) {

            $pilihan = $pilihanPengiriman[$petaniId] ?? null;

            if (
                !is_array($pilihan) ||
                !in_array($pilihan['metode'] ?? '', ['diantar', 'ambil'])
            ) {
                return redirect()
                    ->route('pengusaha.checkout')
                    ->with('error', 'Pilihan pengiriman petani belum lengkap.');
            }

            $metode = $pilihan['metode'];
            $kurir = $pilihan['kurir'] ?? null;

            if (
                $metode === 'diantar' &&
                !in_array($kurir, ['kendaraan', 'jnt'])
            ) {
                return redirect()
                    ->route('pengusaha.checkout')
                    ->with('error', 'Pilihan kurir tidak valid.');
            }

            $biayaOngkir = $metode === 'ambil'
                ? 0
                : ($kurir === 'jnt' ? 12000 : 15000);

            $pengirimanPerPetani[$petaniId] = [
                'metode' => $metode,
                'kurir' => $metode === 'ambil' ? null : $kurir,
                'ongkir' => $biayaOngkir,
            ];

            $ongkirPerPetani[$petaniId] = $biayaOngkir;
            $ongkir += $biayaOngkir;
        }

        // Validasi metode pembayaran
        $metodeBayar = $request->query('pembayaran', 'transfer');

        if (!in_array($metodeBayar, ['transfer', 'gopay', 'ovo', 'cod'])) {
            return redirect()
                ->route('pengusaha.pembayaran')
                ->with('error', 'Metode pembayaran tidak valid.');
        }

        // COD hanya jika semua petani menggunakan Kendaraan Petani
        $codTersedia = collect($pengirimanPerPetani)->isNotEmpty()
            && collect($pengirimanPerPetani)->every(function ($pilihan) {
                return $pilihan['metode'] === 'diantar'
                    && $pilihan['kurir'] === 'kendaraan';
            });

        if ($metodeBayar === 'cod' && !$codTersedia) {
            return redirect()
                ->route('pengusaha.checkout')
                ->with('error', 'COD hanya tersedia jika semua petani menggunakan Kendaraan Petani.');
        }

        // Hitung total pembayaran
        $total = $subtotal + $ongkir;

        return view('pengusaha.konfirmasi', compact(
            'itemsCheckout',
            'subtotal',
            'ongkir',
            'ongkirPerPetani',
            'pengirimanPerPetani',
            'total',
            'alamat',
            'metodeBayar',
            'codTersedia'
        ));

    })->name('konfirmasi');

    Route::post('/konfirmasi', [PesananController::class, 'store'])->name('konfirmasi.store');
    Route::get('/pesanan', [PesananController::class, 'index'])->name('pesanan');
    Route::get('/pesanan/{id}', [PesananController::class, 'show'])->name('pesanan.detail');
    Route::post('/pesanan/{id}/batalkan', [PesananController::class, 'cancel'])->name('pesanan.cancel');

    Route::view('/akun', 'pengusaha.akun')->name('akun');
    // HALAMAN PROFIL USAHA
    Route::get('/profil-usaha', function () {

        $profil = session()->get('profil_usaha', [
            'nama' => session('nama', 'Sari Kitchen'),
            'nama_usaha' => 'Sari Kitchen',
            'email' => 'pengusaha@taniloka.id',
            'telepon' => '',
            'jenis_usaha' => 'Restoran',
            'deskripsi' => '',
        ]);

        return view('pengusaha.profil-usaha', compact('profil'));
    })->name('profil');


    // SIMPAN / PERBARUI PROFIL USAHA
    Route::post('/profil-usaha', function (\Illuminate\Http\Request $request) {

        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'nama_usaha' => 'required|string|max:100',
            'email' => 'required|email',
            'telepon' => 'nullable|string|max:20',
            'jenis_usaha' => 'required|string|max:100',
            'deskripsi' => 'nullable|string|max:500',
        ], [
            'nama.required' => 'Nama pemilik wajib diisi.',
            'nama_usaha.required' => 'Nama usaha wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'jenis_usaha.required' => 'Jenis usaha wajib dipilih.',
        ]);

        session()->put('nama', $data['nama']);

        session()->put('profil_usaha', $data);

        return redirect()
            ->route('pengusaha.profil')
            ->with('sukses', 'Profil usaha berhasil diperbarui!');
    })->name('profil.simpan');
    Route::post('/profil-usaha', function (\Illuminate\Http\Request $request) {

        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'nama_usaha' => 'required|string|max:100',
            'email' => 'required|email',
            'telepon' => 'nullable|string|max:20',
            'jenis_usaha' => 'nullable|string|max:100',
            'deskripsi' => 'nullable|string|max:500',
        ], [
            'nama.required' => 'Nama pemilik wajib diisi.',
            'nama_usaha.required' => 'Nama usaha wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        session()->put('nama', $data['nama']);

        session()->put('profil_usaha', [
            'nama_usaha' => $data['nama_usaha'],
            'email' => $data['email'],
            'telepon' => $data['telepon'] ?? '',
            'jenis_usaha' => $data['jenis_usaha'] ?? '',
            'deskripsi' => $data['deskripsi'] ?? '',
        ]);

        return redirect()
            ->route('pengusaha.profil')
            ->with('sukses', 'Profil usaha berhasil diperbarui!');
    })->name('profil.simpan');
    // Menampilkan alamat
    Route::get('/alamat', function () {
        return view('pengusaha.alamat');
    })->name('alamat');

    // Menyimpan atau mengubah alamat
    Route::post('/alamat', function (\Illuminate\Http\Request $request) {

        $data = $request->validate([
            'label' => 'required|string|max:50',
            'nama' => 'required|string|max:100',
            'telepon' => 'required|string|max:20',
            'alamat' => 'required|string|max:500',
            'kota' => 'required|string|max:100',
            'catatan' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        session()->put('alamat_pengusaha', $data);

        return redirect()
            ->route('pengusaha.alamat')
            ->with('sukses', 'Alamat berhasil disimpan!');
    })->name('alamat.simpan');

    // Menghapus alamat
    Route::post('/alamat/hapus', function () {

        session()->forget('alamat_pengusaha');

        return redirect()
            ->route('pengusaha.alamat')
            ->with('sukses', 'Alamat berhasil dihapus!');
    })->name('alamat.hapus');
    Route::view('/bantuan', 'pengusaha.bantuan')->name('bantuan');
});

Route::middleware(['auth', 'peran:petani'])->prefix('petani')->name('petani.')->group(function () {
        Route::view('/', 'petani.dashboard')->name('dashboard');
        Route::view('/produk', 'petani.produk')->name('produk');
        Route::view('/katalog', 'petani.katalog')->name('katalog');
        Route::view('/pesan', 'petani.pesan')->name('pesan');
        Route::view('/pesanan', 'petani.pesanan')->name('pesanan');
        Route::view('/akun', 'petani.akun')->name('akun');
        Route::view('/alamat', 'petani.alamat')->name('alamat');
        Route::view('/bantuan', 'petani.bantuan')->name('bantuan');

        Route::post('/akun', function (\Illuminate\Http\Request $request) {
            $aksi = $request->input('aksi');

            if ($aksi === 'profil') {
                return redirect()->route('petani.akun')
                    ->with('pesan_akun', 'Profil disimpan (simulasi)');
            }

            if ($aksi === 'tambah_alamat') {
                return redirect(route('petani.akun') . '#tambah-alamat')
                    ->with('pesan_akun', 'Alamat ditambahkan (simulasi)');
            }

            if ($aksi === 'utama') {
                return redirect()->route('petani.akun')
                    ->with('pesan_akun', 'Alamat utama diperbarui (simulasi)');
            }

            if ($aksi === 'hapus_alamat') {
                return redirect()->route('petani.akun')
                    ->with('pesan_akun', 'Alamat dihapus (simulasi)');
            }

            return redirect()->route('petani.akun');
        })->name('akun.simpan');
    });
