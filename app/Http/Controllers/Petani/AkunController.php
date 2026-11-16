<?php

namespace App\Http\Controllers\Petani;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

/** Halaman Akun petani: data user (nama, no_wa, foto) + profil_petani. */
class AkunController extends PetaniController
{
    public const LABEL_VERIFIKASI = [
        'belum_diajukan' => 'Belum diajukan',
        'menunggu' => 'Menunggu verifikasi',
        'perlu_perbaikan' => 'Perlu perbaikan',
        'terverifikasi' => 'Terverifikasi',
        'ditolak' => 'Verifikasi ditolak',
    ];

    private const KOLOM_PROFIL = [
        'nama_usaha_tani', 'kabupaten', 'kecamatan', 'desa', 'deskripsi',
        'nama_bank', 'no_rekening', 'atas_nama_rekening',
    ];

    public function index(Request $request): View
    {
        $user = $this->petani($request);
        $profil = $user->profilPetani;

        return view('petani.akun', [
            'akun' => [
                'nama' => $user->name,
                'email' => $user->email,
                'no_wa' => $user->no_wa,
                'foto' => $this->urlFile($user->foto),
                'status_verifikasi' => $user->status_verifikasi,
                'label_verifikasi' => self::LABEL_VERIFIKASI[$user->status_verifikasi] ?? $user->status_verifikasi,
            ],
            'profil' => collect(self::KOLOM_PROFIL)->mapWithKeys(fn ($k) => [$k => $profil?->{$k} ?? ''])->all(),
            'jumlah' => [
                'pesanan' => $user->pesananMasuk()->where('status', '!=', 'menunggu_pembayaran')->count(),
                'selesai' => $user->pesananMasuk()->where('status', 'selesai')->count(),
                'alamat' => $user->alamat()->count(),
            ],
        ]);
    }

    /** POST /petani/akun (route lama petani.akun.simpan, sekarang benar-benar menyimpan). */
    public function simpan(Request $request): RedirectResponse
    {
        $user = $this->petani($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'no_wa' => ['nullable', 'string', 'max:20', 'regex:/^(\+62|62|0)8[0-9\-\s]{7,15}$/', Rule::unique('users', 'no_wa')->ignore($user->id)],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'nama_usaha_tani' => ['nullable', 'string', 'max:255'],
            'kabupaten' => ['nullable', 'string', 'max:255'],
            'kecamatan' => ['nullable', 'string', 'max:255'],
            'desa' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'nama_bank' => ['nullable', 'string', 'max:255'],
            'no_rekening' => ['nullable', 'string', 'max:30', 'regex:/^[0-9\-\s]+$/'],
            'atas_nama_rekening' => ['nullable', 'string', 'max:255'],
        ], $this->pesanValidasi(), [
            'name' => 'nama',
            'no_wa' => 'nomor WhatsApp',
            'foto' => 'foto profil',
            'nama_usaha_tani' => 'nama usaha tani',
            'kabupaten' => 'kabupaten',
            'kecamatan' => 'kecamatan',
            'desa' => 'desa',
            'deskripsi' => 'deskripsi',
            'nama_bank' => 'nama bank',
            'no_rekening' => 'nomor rekening',
            'atas_nama_rekening' => 'atas nama rekening',
        ]);

        $fotoLama = $user->foto;
        $fotoBaru = $request->hasFile('foto') ? $request->file('foto')->store('foto-profil', 'public') : null;

        try {
            DB::transaction(function () use ($user, $data, $fotoBaru) {
                $user->fill([
                    'name' => $data['name'],
                    'no_wa' => filled($data['no_wa'] ?? null) ? preg_replace('/[\s\-]/', '', $data['no_wa']) : null,
                ]);
                if ($fotoBaru) {
                    $user->foto = $fotoBaru;
                }
                $user->save();

                // firstOrCreate + update untuk profil_petani
                $profil = collect($data)->only(self::KOLOM_PROFIL)->map(fn ($v) => filled($v) ? trim($v) : null)->all();
                $user->profilPetani()->updateOrCreate([], $profil);
            });
        } catch (Throwable $e) {
            if ($fotoBaru) {
                Storage::disk('public')->delete($fotoBaru);
            }
            throw $e;
        }

        if ($fotoBaru && $this->pathLokal($fotoLama)) {
            Storage::disk('public')->delete($fotoLama);
        }

        return redirect()->route('petani.akun')->with('sukses', 'Profil berhasil disimpan.');
    }
}
