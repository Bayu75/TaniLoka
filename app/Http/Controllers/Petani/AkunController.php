<?php

namespace App\Http\Controllers\Petani;

use Illuminate\Http\Request;
use Illuminate\View\View;

/** Halaman Akun petani: ringkasan akun, profil, alamat utama, dan status verifikasi. */
class AkunController extends PetaniController
{
    public const LABEL_VERIFIKASI = [
        'belum_diajukan'  => 'Belum diajukan',
        'menunggu'        => 'Menunggu verifikasi',
        'perlu_perbaikan' => 'Perlu perbaikan',
        'terverifikasi'   => 'Terverifikasi',
        'ditolak'         => 'Verifikasi ditolak',
    ];

    private const KOLOM_PROFIL = [
        'nama_usaha_tani', 'deskripsi',
        'nama_bank', 'no_rekening', 'atas_nama_rekening',
    ];

    public function index(Request $request): View
    {
        $user = $this->petani($request)->loadMissing(['profilPetani', 'alamatUtama']);
        $profil = $user->profilPetani;

        return view('petani.akun', [
            'user' => $user,
            'akun' => [
                'nama'              => $user->name,
                'email'             => $user->email,
                'no_wa'             => $user->no_wa,
                'foto'              => $this->urlFile($user->foto),
                'status_verifikasi' => $user->status_verifikasi,
                'label_verifikasi'  => self::LABEL_VERIFIKASI[$user->status_verifikasi] ?? $user->status_verifikasi,
            ],
            'profil' => collect(self::KOLOM_PROFIL)
                ->mapWithKeys(fn ($k) => [$k => $profil?->{$k} ?? ''])
                ->all(),
            'alamatUtama' => $user->alamatUtama,
            'jumlah' => [
                'produk'  => $user->produk()->count(),
                'pesanan' => $user->pesananMasuk()->where('status', '!=', 'menunggu_pembayaran')->count(),
                'selesai' => $user->pesananMasuk()->where('status', 'selesai')->count(),
                'alamat'  => $user->alamat()->count(),
            ],
        ]);
    }
}