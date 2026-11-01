<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'auth.landing')->name('landing');

Route::get('/masuk', [AuthController::class, 'masuk'])->name('masuk');
Route::post('/masuk', [AuthController::class, 'prosesMasuk'])->name('masuk.proses');
Route::get('/daftar', [AuthController::class, 'daftar'])->name('daftar');
Route::post('/daftar', [AuthController::class, 'prosesDaftar'])->name('daftar.proses');
Route::post('/keluar', [AuthController::class, 'keluar'])->name('keluar');

// Placeholder, nanti diisi tim
Route::view('/petani', 'petani.dashboard')->name('petani.dashboard');
Route::view('/pengusaha', 'pengusaha.beranda')->name('pengusaha.beranda');