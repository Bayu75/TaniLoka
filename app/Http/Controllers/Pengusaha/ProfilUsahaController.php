<?php

namespace App\Http\Controllers\Pengusaha;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfilUsahaController extends Controller
{
    public function edit()
    {
        $user = auth()->user()->load('profilPengusaha');

        return view('pengusaha.profil-usaha', [
            'user' => $user,
            'profil' => $user->profilPengusaha,
            'jenisList' => [
                'restoran' => 'Restoran',
                'kafe' => 'Kafe',
                'hotel' => 'Hotel',
                'katering' => 'Katering',
                'warung' => 'Warung Makan',
                'lainnya' => 'Usaha Kuliner Lainnya',
            ],
        ]);
    }

    public function simpan(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'nama_usaha' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'no_wa' => ['nullable', 'string', 'max:20'],
            'jenis_usaha' => ['required', Rule::in(['restoran', 'kafe', 'hotel', 'katering', 'warung', 'lainnya'])],
        ], [
            'nama.required' => 'Nama pemilik wajib diisi.',
            'nama_usaha.required' => 'Nama usaha wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah dipakai akun lain.',
            'jenis_usaha.required' => 'Jenis usaha wajib dipilih.',
            'jenis_usaha.in' => 'Jenis usaha tidak valid.',
        ]);

        $user->update([
            'name' => $data['nama'],
            'email' => $data['email'],
            'no_wa' => $data['no_wa'] ?? null,
        ]);

        $user->profilPengusaha()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'nama_usaha' => $data['nama_usaha'],
                'jenis_usaha' => $data['jenis_usaha'],
            ]
        );

        return redirect()
            ->route('pengusaha.profil')
            ->with('sukses', 'Profil usaha berhasil diperbarui!');
    }
}