<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Pengusaha\PesananController;

use App\Http\Controllers\Petani\AkunController as PetaniAkunController;
use App\Http\Controllers\Petani\AlamatController as PetaniAlamatController;
use App\Http\Controllers\Petani\DashboardController as PetaniDashboardController;
use App\Http\Controllers\Petani\PesananController as PetaniPesananController;
use App\Http\Controllers\Petani\ProdukController as PetaniProdukController;

use App\Http\Controllers\Petani\KatalogController as PetaniKatalogController;
use App\Http\Controllers\Petani\PesanController as PetaniPesanController;


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pengusaha\ProdukController;
use App\Http\Controllers\Pengusaha\ProfilUsahaController;
use App\Http\Controllers\Pengusaha\KeranjangController;
use App\Http\Controllers\Admin\VerifikasiController;
use App\Http\Controllers\Pengusaha\VerifikasiController as VerifikasiPengusahaController;
use App\Http\Controllers\Pengusaha\AlamatController;
use App\Http\Controllers\Pengusaha\ChatController;

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
    Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi');
    Route::get('/verifikasi/{verifikasi}', [VerifikasiController::class, 'show'])->name('verifikasi.show');
    Route::patch('/verifikasi/{verifikasi}', [VerifikasiController::class, 'putuskan'])->name('verifikasi.putuskan');
});

Route::middleware(['auth', 'peran:pengusaha'])->prefix('pengusaha')->name('pengusaha.')->group(function () {

    Route::get('/', [ProdukController::class, 'beranda'])
    ->name('beranda');

    Route::get('/katalog', [ProdukController::class, 'katalog'])
    ->name('katalog');


    Route::get('/produk/{produk}', [ProdukController::class, 'detail'])->name('produk.detail');

    Route::get('/toko/{petani}', [ProdukController::class, 'toko'])->name('toko');

    Route::get('/keranjang', [KeranjangController::class, 'index'])->name('keranjang');
    Route::post('/keranjang/{produk}', [KeranjangController::class, 'tambah'])->name('keranjang.tambah');
    Route::patch('/keranjang/item/{item}', [KeranjangController::class, 'ubah'])->name('keranjang.ubah');
    Route::delete('/keranjang/item/{item}', [KeranjangController::class, 'hapus'])->name('keranjang.hapus');

    // Chat dan penawaran pengusaha
    Route::get('/pesan', [ChatController::class, 'index'])->name('pesan');
    Route::get('/pesan/{id}', [ChatController::class, 'show'])->name('pesan.detail');
    Route::post('/pesan/{id}', [ChatController::class, 'kirim'])->name('pesan.kirim');
    Route::post('/pesan/{id}/penawaran', [ChatController::class, 'kirimPenawaran'])->name('pesan.penawaran');
    Route::post('/pesan/{id}/penawaran/{pesan}/terima', [ChatController::class, 'terimaPenawaran'])->name('pesan.penawaran.terima');
    Route::post('/pesan/{id}/penawaran/{pesan}/tolak', [ChatController::class, 'tolakPenawaran'])->name('pesan.penawaran.tolak');

    Route::view('/checkout', 'pengusaha.checkout')->name('checkout');
    Route::view('/pembayaran', 'pengusaha.pembayaran')->name('pembayaran');
    Route::view('/konfirmasi', 'pengusaha.konfirmasi')->name('konfirmasi');
    Route::post('/konfirmasi', [PesananController::class, 'store'])->name('konfirmasi.store');
    Route::get('/pesanan', [PesananController::class, 'index'])->name('pesanan');
    Route::get('/pesanan/{id}', [PesananController::class, 'show'])->name('pesanan.detail');
    Route::post('/pesanan/{id}/batalkan', [PesananController::class, 'cancel'])->name('pesanan.cancel');
   Route::get('/verifikasi', [VerifikasiPengusahaController::class, 'index'])->name('verifikasi');
    Route::post('/verifikasi', [VerifikasiPengusahaController::class, 'ajukan'])->name('verifikasi.ajukan');
    Route::view('/akun', 'pengusaha.akun')->name('akun');
    Route::get('/profil-usaha', [ProfilUsahaController::class, 'edit'])->name('profil');
    Route::post('/profil-usaha', [ProfilUsahaController::class, 'simpan'])->name('profil.simpan');

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
    });

       // Alamat usaha
    Route::get('/alamat', [AlamatController::class, 'index'])->name('alamat');
    Route::post('/alamat', [AlamatController::class, 'simpan'])->name('alamat.simpan');
    Route::post('/alamat/hapus', [AlamatController::class, 'hapus'])->name('alamat.hapus');
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
Route::get('/katalog', [PetaniKatalogController::class, 'index'])->name('katalog');

    Route::get('/pesan', [PetaniPesanController::class, 'index'])->name('pesan');
    Route::get('/pesan/{percakapan}', [PetaniPesanController::class, 'show'])->whereNumber('percakapan')->name('pesan.show');
    Route::post('/pesan/{percakapan}/kirim', [PetaniPesanController::class, 'kirim'])->whereNumber('percakapan')->name('pesan.kirim');

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
