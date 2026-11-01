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
    Route::view('/pesan', 'pengusaha.pesan')->name('pesan');
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