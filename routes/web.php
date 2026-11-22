<?php

use App\Http\Controllers\AuthController;

use App\Http\Controllers\Petani\AkunController as PetaniAkunController;
use App\Http\Controllers\Petani\AlamatController as AlamatPetaniController;
use App\Http\Controllers\Petani\DashboardController as PetaniDashboardController;
use App\Http\Controllers\Petani\PesananController as PetaniPesananController;
use App\Http\Controllers\Petani\FlashSaleController as PetaniFlashSaleController;
use App\Http\Controllers\Petani\ProdukController as PetaniProdukController;

use App\Http\Controllers\Petani\KatalogController as PetaniKatalogController;
use App\Http\Controllers\Petani\PesanController as PetaniPesanController;
use App\Http\Controllers\Petani\ProfilController as ProfilPetaniController;
use App\Http\Controllers\Petani\VerifikasiController as VerifikasiPetaniController;
use App\Http\Controllers\Petani\RatingController as PetaniRatingController;


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\VerifikasiController;
use App\Http\Controllers\Pengusaha\VerifikasiController as VerifikasiPengusahaController;
use App\Http\Controllers\Pengusaha\AlamatController;
use App\Http\Controllers\Pengusaha\ProdukController;
use App\Http\Controllers\Pengusaha\ProfilUsahaController;
use App\Http\Controllers\Pengusaha\KeranjangController;
use App\Http\Controllers\Pengusaha\ChatController;
use App\Http\Controllers\Pengusaha\CheckoutController;
use App\Http\Controllers\Pengusaha\PesananController;

Route::view('/', 'auth.landing')->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'formLogin'])->name('masuk');
    Route::post('/masuk', [AuthController::class, 'login'])->name('masuk.proses');
    Route::get('/daftar', [AuthController::class, 'formRegister'])->name('daftar');
    Route::post('/daftar', [AuthController::class, 'register'])->name('daftar.proses');

    // ini untuk demo masuk/login cepat (cuma aktif di local)
    if (app()->environment('local')) {
        Route::post('/masuk/demo', [AuthController::class, 'masukDemo'])->name('masuk.demo');
    }
});

Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('keluar');

Route::middleware(['auth', 'peran:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi');
    Route::get('/verifikasi/{verifikasi}', [VerifikasiController::class, 'show'])->name('verifikasi.show');
    Route::patch('/verifikasi/{verifikasi}', [VerifikasiController::class, 'putuskan'])->name('verifikasi.putuskan');
});

Route::middleware(['auth', 'peran:pengusaha'])->prefix('pengusaha')->name('pengusaha.')->group(function () {

    Route::get('/', [ProdukController::class, 'beranda'])->name('beranda');
    Route::get('/katalog', [ProdukController::class, 'katalog'])->name('katalog');
    Route::get('/produk/{produk}', [ProdukController::class, 'detail'])->name('produk.detail');
    Route::get('/toko/{petani}', [ProdukController::class, 'toko'])->name('toko');

    Route::get('/keranjang', [KeranjangController::class, 'index'])->name('keranjang');
    Route::post('/keranjang/{produk}', [KeranjangController::class, 'tambah'])->name('keranjang.tambah');
    Route::patch('/keranjang/item/{item}', [KeranjangController::class, 'ubah'])->name('keranjang.ubah');
    Route::delete('/keranjang/item/{item}', [KeranjangController::class, 'hapus'])->name('keranjang.hapus');

    Route::get('/pesan', [ChatController::class, 'index'])->name('pesan');
    Route::get('/pesan/{id}', [ChatController::class, 'show'])->name('pesan.detail');
    Route::post('/pesan/{id}/kirim', [ChatController::class, 'kirim'])->name('pesan.kirim');

    // Negosiasi harga
    Route::post('/pesan/{id}/penawaran', [ChatController::class, 'kirimPenawaran'])
        ->name('pesan.penawaran');
    Route::post('/pesan/{id}/penawaran/{pesan}/terima', [ChatController::class, 'terimaPenawaran'])
        ->name('pesan.penawaran.terima');
    Route::post('/pesan/{id}/penawaran/{pesan}/tolak', [ChatController::class, 'tolakPenawaran'])
        ->name('pesan.penawaran.tolak');

    Route::middleware('terverifikasi')->group(function () {
        Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
        Route::get('/pembayaran', [CheckoutController::class, 'pembayaran'])->name('pembayaran');
        Route::get('/konfirmasi', [CheckoutController::class, 'konfirmasi'])->name('konfirmasi');
        Route::post('/konfirmasi', [PesananController::class, 'store'])->name('konfirmasi.store');
    });

    Route::get('/pesanan', [PesananController::class, 'index'])->name('pesanan');
    Route::get('/pesanan/{id}', [PesananController::class, 'show'])->name('pesanan.detail');
    Route::post('/pesanan/{id}/batalkan', [PesananController::class, 'cancel'])->name('pesanan.cancel');
    Route::post('/pesanan/{id}/bukti', [PesananController::class, 'uploadBukti'])
    ->name('pesanan.bukti.upload');
    Route::post('/pesanan/{id}/selesai', [PesananController::class, 'konfirmasiSelesai'])
    ->name('pesanan.selesai');
    Route::post('/pesanan/{id}/ulasan', [PesananController::class, 'simpanUlasan'])
    ->name('pesanan.ulasan');

    Route::get('/verifikasi', [VerifikasiPengusahaController::class, 'index'])->name('verifikasi');
    Route::post('/verifikasi', [VerifikasiPengusahaController::class, 'ajukan'])->name('verifikasi.ajukan');
    Route::view('/akun', 'pengusaha.akun')->name('akun');

    // Profil usaha
    Route::get('/profil-usaha', [ProfilUsahaController::class, 'edit'])->name('profil');
    Route::post('/profil-usaha', [ProfilUsahaController::class, 'simpan'])->name('profil.simpan');

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
    Route::patch('/produk/{produk}/tersedia', [PetaniProdukController::class, 'toggleTersedia'])
        ->whereNumber('produk')
        ->name('produk.tersedia');
    Route::resource('produk', PetaniProdukController::class)
        ->except('show')
        ->whereNumber('produk');

    // Flash Sale
    Route::get('/flash-sale', [PetaniFlashSaleController::class, 'index'])->name('flash-sale');
    Route::post('/produk/{produk}/flash-sale', [PetaniFlashSaleController::class, 'store'])->whereNumber('produk')->name('flash-sale.store');
    Route::delete('/produk/{produk}/flash-sale', [PetaniFlashSaleController::class, 'destroy'])->whereNumber('produk')->name('flash-sale.destroy');

    // Halaman yang belum disambungkan ke database
    Route::get('/katalog', [PetaniKatalogController::class, 'index'])->name('katalog');

    Route::get('/pesan', [PetaniPesanController::class, 'index'])->name('pesan');
    Route::get('/pesan/{percakapan}', [PetaniPesanController::class, 'show'])->whereNumber('percakapan')->name('pesan.show');
    Route::post('/pesan/{percakapan}/kirim', [PetaniPesanController::class, 'kirim'])->whereNumber('percakapan')->name('pesan.kirim');
    Route::post('/pesan/{percakapan}/penawaran', [PetaniPesanController::class, 'kirimPenawaran'])
        ->whereNumber('percakapan')
        ->name('pesan.penawaran');
    Route::post('/pesan/{percakapan}/penawaran/{pesan}/terima', [PetaniPesanController::class, 'terimaPenawaran'])
        ->whereNumber(['percakapan', 'pesan'])
        ->name('pesan.penawaran.terima');
    Route::post('/pesan/{percakapan}/penawaran/{pesan}/tolak', [PetaniPesanController::class, 'tolakPenawaran'])
        ->whereNumber(['percakapan', 'pesan'])
        ->name('pesan.penawaran.tolak');
    Route::post('/pesanan/{pesanan}/hubungi', [PetaniPesananController::class, 'hubungi'])->whereNumber('pesanan')->name('pesanan.hubungi');

    Route::get('/rating', [PetaniRatingController::class, 'index'])->name('rating');
    Route::view('/bantuan', 'petani.bantuan')->name('bantuan');


    // Pesanan Masuk
    Route::get('/pesanan', [PetaniPesananController::class, 'index'])->name('pesanan');
    Route::get('/pesanan/{pesanan}', [PetaniPesananController::class, 'show'])->whereNumber('pesanan')->name('pesanan.show');
    Route::post('/pesanan/{pesanan}/terima', [PetaniPesananController::class, 'terima'])->whereNumber('pesanan')->name('pesanan.terima');
    Route::post('/pesanan/{pesanan}/tolak', [PetaniPesananController::class, 'tolak'])->whereNumber('pesanan')->name('pesanan.tolak');
    Route::post('/pesanan/{pesanan}/kirim', [PetaniPesananController::class, 'kirim'])->whereNumber('pesanan')->name('pesanan.kirim');
    Route::post('/pesanan/{pesanan}/terkirim', [PetaniPesananController::class, 'terkirim'])->whereNumber('pesanan')->name('pesanan.terkirim');
Route::post('/pesanan/{pesanan}/hubungi', [PetaniPesananController::class, 'hubungi'])->whereNumber('pesanan')->name('pesanan.hubungi');

    // Akun
    Route::get('/akun', [PetaniAkunController::class, 'index'])->name('akun');
    Route::get('/verifikasi', [VerifikasiPetaniController::class, 'index'])->name('verifikasi');
    Route::post('/verifikasi', [VerifikasiPetaniController::class, 'ajukan'])->name('verifikasi.ajukan');

    // Profil
    Route::get('/profil', [ProfilPetaniController::class, 'edit'])->name('profil');
    Route::post('/profil', [ProfilPetaniController::class, 'simpan'])->name('profil.simpan');

    // Alamat
    Route::get('/alamat', [AlamatPetaniController::class, 'index'])->name('alamat');
    Route::post('/alamat', [AlamatPetaniController::class, 'simpan'])->name('alamat.simpan');
    Route::post('/alamat/hapus', [AlamatPetaniController::class, 'hapus'])->name('alamat.hapus');
});
