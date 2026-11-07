
<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'auth.landing')->name('landing');

Route::get('/masuk', [AuthController::class, 'masuk'])->name('masuk');
Route::post('/masuk', [AuthController::class, 'prosesMasuk'])->name('masuk.proses');
Route::get('/daftar', [AuthController::class, 'daftar'])->name('daftar');
Route::post('/daftar', [AuthController::class, 'prosesDaftar'])->name('daftar.proses');
Route::post('/keluar', [AuthController::class, 'keluar'])->name('keluar');

Route::middleware('peran:pengusaha')->prefix('pengusaha')->name('pengusaha.')->group(function () {

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
    Route::view('/pesanan', 'pengusaha.pesanan')->name('pesanan');
    Route::view('/akun', 'pengusaha.akun')->name('akun');
});

Route::middleware('peran:petani')->prefix('petani')->name('petani.')->group(function () {
    Route::view('/', 'petani.dashboard')->name('dashboard');
    Route::view('/produk', 'petani.produk')->name('produk');
    Route::view('/katalog', 'petani.katalog')->name('katalog');
    Route::view('/pesan', 'petani.pesan')->name('pesan');
    Route::view('/pesanan', 'petani.pesanan')->name('pesanan');
    Route::view('/akun', 'petani.akun')->name('akun');
});
