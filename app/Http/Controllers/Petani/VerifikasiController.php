<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Controller;
use App\Models\Alamat;
use App\Models\BuktiVerifikasi;
use App\Models\VerifikasiAkun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerifikasiController extends Controller
{
    private function cekKelengkapan($user): array
    {
        $profil = $user->profilPetani;

        return [
            'profil' => filled($user->name) && filled($user->email) && filled($user->no_wa)
                && filled($profil?->nama_usaha_tani),
            'alamat' => Alamat::where('user_id', $user->id)->exists(),
        ];
    }

    public function index()
    {
        $user = auth()->user();
        $cek = $this->cekKelengkapan($user);

        $verifikasi = VerifikasiAkun::with('bukti')
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        // Form upload hanya dibuka kalau belum pernah mengajukan,
        // atau pengajuan terakhir perlu perbaikan / ditolak
        $bisaAjukan = ! $verifikasi || in_array($verifikasi->status, ['perlu_perbaikan', 'ditolak']);

        return view('petani.verifikasi', compact('user', 'cek', 'verifikasi', 'bisaAjukan'));
    }

    public function ajukan(Request $request)
    {
        $user = auth()->user();
        $cek = $this->cekKelengkapan($user);

        if (! $cek['profil'] || ! $cek['alamat']) {
            return back()->with('error', 'Lengkapi profil usaha tani dan alamat kebun terlebih dahulu.');
        }

        $terakhir = VerifikasiAkun::where('user_id', $user->id)->latest()->first();
        if ($terakhir && in_array($terakhir->status, ['menunggu', 'terverifikasi'])) {
            return back()->with('error', 'Pengajuan verifikasi kamu sudah ada.');
        }

        $request->validate([
            'ktp'        => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'foto_lahan' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'setuju_sk'  => ['accepted'],
        ], [
            'ktp.required'        => 'Foto KTP wajib diunggah.',
            'ktp.file'            => 'Foto KTP harus berupa file.',
            'ktp.mimes'           => 'Foto KTP harus berformat JPG, PNG, atau PDF.',
            'ktp.max'             => 'Ukuran foto KTP maksimal 2 MB.',
            'foto_lahan.required' => 'Foto lahan wajib diunggah.',
            'foto_lahan.image'    => 'Foto lahan harus berupa gambar.',
            'foto_lahan.mimes'    => 'Foto lahan harus berformat JPG atau PNG.',
            'foto_lahan.max'      => 'Ukuran foto lahan maksimal 2 MB.',
            'setuju_sk.accepted'  => 'Kamu harus menyetujui Syarat & Ketentuan.',
        ]);

        DB::transaction(function () use ($request, $user) {
            $verifikasi = VerifikasiAkun::create([
                'user_id' => $user->id,
                'status'  => 'menunggu',
            ]);

            foreach (['ktp', 'foto_lahan'] as $jenis) {
                BuktiVerifikasi::create([
                    'verifikasi_id' => $verifikasi->id,
                    'jenis'         => $jenis,
                    'path'          => $request->file($jenis)->store("verifikasi/{$user->id}", 'public'),
                ]);
            }
            
            $user->update(['status_verifikasi' => 'menunggu']);
        });

        return redirect()->route('petani.verifikasi')
            ->with('sukses', 'Pengajuan verifikasi terkirim! Admin akan meninjau datamu.');
    }
}