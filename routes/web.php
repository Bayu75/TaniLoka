
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Pengusaha\PesananController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pengusaha\ProdukController;
use App\Http\Controllers\Pengusaha\KeranjangController;

Route::view('/', 'auth.landing')->name('landing');

Route::get('/masuk', [AuthController::class, 'masuk'])->name('masuk');
Route::post('/masuk', [AuthController::class, 'prosesMasuk'])->name('masuk.proses');
Route::get('/daftar', [AuthController::class, 'daftar'])->name('daftar');
Route::post('/daftar', [AuthController::class, 'prosesDaftar'])->name('daftar.proses');
Route::post('/keluar', [AuthController::class, 'keluar'])->name('keluar');

Route::middleware('peran:pengusaha')->prefix('pengusaha')->name('pengusaha.')->group(function () {

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

    Route::view('/pesan', 'pengusaha.pesan')->name('pesan');

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

Route::middleware('peran:petani')
    ->prefix('petani')
    ->name('petani.')
    ->group(function () {
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
