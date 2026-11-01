<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    // Akun demo (sementara, tanpa database)
    private array $akunDemo = [
        'petani'    => ['nama' => 'Pak Made', 'email' => 'petani@taniloka.id'],
        'pengusaha' => ['nama' => 'Sari Kitchen', 'email' => 'pengusaha@taniloka.id'],
    ];

    public function masuk(Request $request)
    {
        return view('auth.masuk', ['peran' => $request->query('peran', 'petani')]);
    }

    public function prosesMasuk(Request $request)
    {
        $data = $request->validate([
            'peran'    => 'required|in:petani,pengusaha',
            'email'    => 'nullable|email',
            'password' => 'nullable|min:8',
            'demo'     => 'nullable',
        ], [
            'email.email'  => 'Masukkan email yang valid.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        // Login biasa wajib isi email & password, "Masuk Cepat" tidak
        if (! $request->has('demo') && (empty($data['email']) || empty($data['password']))) {
            return back()->withInput()->withErrors(['email' => 'Email dan password wajib diisi.']);
        }

        return $this->simpanSesi($request, $data['peran'], $this->akunDemo[$data['peran']]['nama']);
    }

    public function daftar(Request $request)
    {
        return view('auth.daftar', ['peran' => $request->query('peran', 'pengusaha')]);
    }

    public function prosesDaftar(Request $request)
    {
        $data = $request->validate([
            'peran'      => 'required|in:petani,pengusaha',
            'nama'       => 'required|string|max:100',
            'email'      => 'required|email',
            'telepon'    => 'nullable|string|max:20',
            'nama_usaha' => 'nullable|string|max:100',
            'password'   => 'required|min:8',
        ], [
            'nama.required'     => 'Nama lengkap wajib diisi.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Masukkan email yang valid, contoh: nama@contoh.com.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
        ]);

        // Petani baru belum terverifikasi
        $request->session()->put('terverifikasi', $data['peran'] !== 'petani');

        return $this->simpanSesi($request, $data['peran'], $data['nama'], 'Akun berhasil dibuat.');
    }

    public function keluar(Request $request)
    {
        $request->session()->flush();
        return redirect()->route('landing');
    }

    private function simpanSesi(Request $request, string $peran, string $nama, ?string $pesan = null)
    {
        $request->session()->put(['peran' => $peran, 'nama' => $nama]);

        $tujuan = $peran === 'petani' ? 'petani.dashboard' : 'pengusaha.beranda';
        return redirect()->route($tujuan)->with('sukses', $pesan ?? "Selamat datang, {$nama}!");
    }
}