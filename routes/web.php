<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Pengusaha\PesananController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\VerifikasiController;
use App\Http\Controllers\Pengusaha\VerifikasiController as VerifikasiPengusahaController;
use App\Http\Controllers\Pengusaha\AlamatController;
use App\Http\Controllers\Pengusaha\ProdukController;
use App\Http\Controllers\Pengusaha\ProfilUsahaController;
use App\Http\Controllers\Pengusaha\KeranjangController;

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

    Route::get('/', [ProdukController::class, 'beranda'])->name('beranda');
    Route::get('/katalog', [ProdukController::class, 'katalog'])->name('katalog');
    Route::get('/produk/{produk}', [ProdukController::class, 'detail'])->name('produk.detail');
    Route::get('/toko/{petani}', [ProdukController::class, 'toko'])->name('toko');

    Route::get('/keranjang', [KeranjangController::class, 'index'])->name('keranjang');
    Route::post('/keranjang/{produk}', [KeranjangController::class, 'tambah'])->name('keranjang.tambah');
    Route::patch('/keranjang/item/{item}', [KeranjangController::class, 'ubah'])->name('keranjang.ubah');
    Route::delete('/keranjang/item/{item}', [KeranjangController::class, 'hapus'])->name('keranjang.hapus');

    Route::view('/pesan', 'pengusaha.pesan')->name('pesan');

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