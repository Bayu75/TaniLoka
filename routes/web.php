
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Pengusaha\PesananController;

use App\Http\Controllers\Petani\AkunController as PetaniAkunController;
use App\Http\Controllers\Petani\AlamatController as PetaniAlamatController;
use App\Http\Controllers\Petani\DashboardController as PetaniDashboardController;
use App\Http\Controllers\Petani\PesananController as PetaniPesananController;
use App\Http\Controllers\Petani\ProdukController as PetaniProdukController;

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
    Route::view('/katalog', 'pengusaha.katalog')->name('katalog');

    Route::get('/produk/{slug}', function ($slug) {
        return view('pengusaha.detail-produk', compact('slug'));
    })->name('produk.detail');

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

    Route::view('/pesan', 'pengusaha.pesan')->name('pesan');

    // Menambahkan produk ke keranjang dengan validasi stok
    Route::post('/keranjang/tambah', function (\Illuminate\Http\Request $request) {

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
            ])->withInput();
        }

        $produk = $produkList[$slug];

        // Pastikan stok tersedia
        if ($produk['stok'] <= 0) {
            return back()->withErrors([
                'jumlah' => 'Maaf, stok produk sedang habis.',
            ])->withInput();
        }

        // Validasi minimal pembelian
        if ($jumlah < $produk['minimal']) {
            return back()->withErrors([
                'jumlah' => 'Minimal pembelian adalah ' . $produk['minimal'] . ' kg.',
            ])->withInput();
        }

        $keranjang = session()->get('keranjang', []);

        // Periksa total jumlah setelah ditambahkan
        $totalJumlah = ($keranjang[$slug] ?? 0) + $jumlah;

        if ($totalJumlah > $produk['stok']) {
            return back()->withErrors([
                'jumlah' => 'Jumlah pembelian melebihi stok yang tersedia.',
            ])->withInput();
        }

        $keranjang[$slug] = $totalJumlah;

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
    Route::view('/checkout', 'pengusaha.checkout')->name('checkout');
    Route::view('/pembayaran', 'pengusaha.pembayaran')->name('pembayaran');
    Route::view('/konfirmasi', 'pengusaha.konfirmasi')->name('konfirmasi');
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
    // Dashboard
    Route::get('/', [PetaniDashboardController::class, 'index'])->name('dashboard');

    // Kelola Produk
    Route::get('/produk', [PetaniProdukController::class, 'index'])->name('produk');
    Route::post('/produk', [PetaniProdukController::class, 'store'])->name('produk.store');
    Route::put('/produk/{produk}', [PetaniProdukController::class, 'update'])->whereNumber('produk')->name('produk.update');
    Route::patch('/produk/{produk}/tersedia', [PetaniProdukController::class, 'tersedia'])->whereNumber('produk')->name('produk.tersedia');
    Route::delete('/produk/{produk}', [PetaniProdukController::class, 'destroy'])->whereNumber('produk')->name('produk.destroy');

    // Halaman yang belum disambungkan ke database
    Route::view('/katalog', 'petani.katalog')->name('katalog');
    Route::view('/pesan', 'petani.pesan')->name('pesan');
    Route::view('/bantuan', 'petani.bantuan')->name('bantuan');

    // Pesanan Masuk
    Route::get('/pesanan', [PetaniPesananController::class, 'index'])->name('pesanan');
    Route::get('/pesanan/{pesanan}', [PetaniPesananController::class, 'show'])->whereNumber('pesanan')->name('pesanan.show');
    Route::post('/pesanan/{pesanan}/terima', [PetaniPesananController::class, 'terima'])->whereNumber('pesanan')->name('pesanan.terima');
    Route::post('/pesanan/{pesanan}/tolak', [PetaniPesananController::class, 'tolak'])->whereNumber('pesanan')->name('pesanan.tolak');
    Route::post('/pesanan/{pesanan}/kirim', [PetaniPesananController::class, 'kirim'])->whereNumber('pesanan')->name('pesanan.kirim');

    // Akun
    Route::get('/akun', [PetaniAkunController::class, 'index'])->name('akun');
    Route::post('/akun', [PetaniAkunController::class, 'simpan'])->name('akun.simpan');

    // Alamat
    Route::get('/alamat', [PetaniAlamatController::class, 'index'])->name('alamat');
    Route::post('/alamat', [PetaniAlamatController::class, 'store'])->name('alamat.store');
    Route::put('/alamat/{alamat}', [PetaniAlamatController::class, 'update'])->whereNumber('alamat')->name('alamat.update');
    Route::patch('/alamat/{alamat}/utama', [PetaniAlamatController::class, 'utama'])->whereNumber('alamat')->name('alamat.utama');
    Route::delete('/alamat/{alamat}', [PetaniAlamatController::class, 'destroy'])->whereNumber('alamat')->name('alamat.destroy');
});