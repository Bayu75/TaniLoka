<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfilController extends Controller
{
    public function edit()
    {
        $user = auth()->user()->load('profilPetani');

        return view('petani.profil', [
            'user'   => $user,
            'profil' => $user->profilPetani,
        ]);
    }

    public function simpan(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'nama'               => ['required', 'string', 'max:100'],
            'nama_usaha_tani'    => ['required', 'string', 'max:100'],
            'email'              => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'no_wa'              => ['nullable', 'string', 'max:20'],
            'deskripsi'          => ['nullable', 'string', 'max:500'],
            'nama_bank'          => ['nullable', 'string', 'max:50'],
            'no_rekening'        => ['nullable', 'string', 'max:30'],
            'atas_nama_rekening' => ['nullable', 'string', 'max:100'],
        ], [
            'nama.required'            => 'Nama petani wajib diisi.',
            'nama_usaha_tani.required' => 'Nama usaha tani wajib diisi.',
            'email.required'           => 'Email wajib diisi.',
            'email.email'              => 'Format email tidak valid.',
            'email.unique'             => 'Email sudah dipakai akun lain.',
            'deskripsi.max'            => 'Deskripsi maksimal 500 karakter.',
        ]);

        $user->update([
            'name'  => $data['nama'],
            'email' => $data['email'],
            'no_wa' => $data['no_wa'] ?? null,
        ]);

        $user->profilPetani()->updateOrCreate(
            ['user_id' => $user->id],
            collect($data)->only([
                'nama_usaha_tani', 'deskripsi',
                'nama_bank', 'no_rekening', 'atas_nama_rekening',
            ])->all()
        );

        if ($request->input('dari') === 'verifikasi') {
            return redirect()
                ->route('petani.verifikasi')
                ->with('sukses', 'Profil berhasil disimpan. Lanjutkan pengajuan verifikasi.');
        }

        return redirect()
            ->route('petani.profil')
            ->with('sukses', 'Profil petani berhasil diperbarui!');
    }    
}