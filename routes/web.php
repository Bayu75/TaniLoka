<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::view('/', 'auth.landing')->name('landing');

// Sementara placeholder, nanti diganti saat feat/auth
Route::view('/masuk', 'auth.masuk')->name('masuk');
Route::view('/daftar', 'auth.daftar')->name('daftar');