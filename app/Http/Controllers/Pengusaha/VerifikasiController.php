<?php

namespace App\Http\Controllers\Pengusaha;

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
        $profil = $user->profilPengusaha;

        return [
            'profil' => filled($user->name) && filled($user->email) && filled($user->no_wa)
                && filled($profil?->nama_usaha) && filled($profil?->jenis_usaha),
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

        return view('pengusaha.verifikasi', compact('user', 'cek', 'verifikasi', 'bisaAjukan'));
    }

    public function ajukan(Request $request)
    {
        $user = auth()->user();
        $cek = $this->cekKelengkapan($user);

        if (! $cek['profil'] || ! $cek['alamat']) {
            return back()->with('error', 'Lengkapi profil usaha dan alamat terlebih dahulu.');
        }

        $terakhir = VerifikasiAkun::where('user_id', $user->id)->latest()->first();
        if ($terakhir && in_array($terakhir->status, ['menunggu', 'terverifikasi'])) {
            return back()->with('error', 'Pengajuan verifikasi kamu sudah ada.');
        }

               $request->validate([
            'nib'               => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'foto_tempat_usaha' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'setuju_sk'         => ['accepted'],
        ], [
            'nib.required'               => 'Dokumen NIB wajib diunggah.',
            'nib.file'                   => 'Dokumen NIB harus berupa file.',
            'nib.mimes'                  => 'Dokumen NIB harus berformat JPG, PNG, atau PDF.',
            'nib.max'                    => 'Ukuran dokumen NIB maksimal 2 MB.',
            'foto_tempat_usaha.required' => 'Foto tempat usaha wajib diunggah.',
            'foto_tempat_usaha.image'    => 'Foto tempat usaha harus berupa gambar.',
            'foto_tempat_usaha.mimes'    => 'Foto tempat usaha harus berformat JPG atau PNG.',
            'foto_tempat_usaha.max'      => 'Ukuran foto tempat usaha maksimal 2 MB.',
            'setuju_sk.accepted'         => 'Kamu harus menyetujui Syarat & Ketentuan.',
        ]);

        DB::transaction(function () use ($request, $user) {
            $verifikasi = VerifikasiAkun::create([
                'user_id' => $user->id,
                'status'  => 'menunggu',
            ]);
            $verifikasi = VerifikasiAkun::create([
                'user_id' => $user->id,
                'status'  => 'menunggu',
            ]);

            $user->forceFill(['status_verifikasi' => 'menunggu'])->save();
            foreach (['nib', 'foto_tempat_usaha'] as $jenis) {
                BuktiVerifikasi::create([
                    'verifikasi_id' => $verifikasi->id,
                    'jenis'         => $jenis,
                    'path'          => $request->file($jenis)->store("verifikasi/{$user->id}", 'public'),
                ]);
            }
        });

        return redirect()->route('pengusaha.verifikasi')
            ->with('sukses', 'Pengajuan verifikasi terkirim! Admin akan meninjau datamu.');
    }
}