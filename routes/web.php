<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Pengusaha\PesananController;
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
    Route::view('/pesan', 'pengusaha.pesan')->name('pesan');
    Route::view('/pesan/{id}', 'pengusaha.chat')->name('pesan.detail');
    Route::view('/keranjang', 'pengusaha.keranjang')->name('keranjang');
    Route::view('/checkout', 'pengusaha.checkout')->name('checkout');
    Route::view('/pembayaran', 'pengusaha.pembayaran')->name('pembayaran');
    Route::view('/konfirmasi', 'pengusaha.konfirmasi')->name('konfirmasi');
    Route::post('/konfirmasi', [PesananController::class, 'store'])->name('konfirmasi.store');
    Route::get('/pesanan', [PesananController::class, 'index'])->name('pesanan');
    Route::get('/pesanan/{id}', [PesananController::class, 'show'])->name('pesanan.detail');
    Route::post('/pesanan/{id}/batalkan', [PesananController::class, 'cancel'])->name('pesanan.cancel');

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
