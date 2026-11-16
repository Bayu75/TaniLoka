<?php

namespace App\Http\Controllers\Petani;

use App\Models\Alamat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Alamat Saya petani. Selalu ada tepat satu alamat utama selama daftar tidak kosong. */
class AlamatController extends PetaniController
{
    public function index(Request $request): View
    {
        $user = $this->petani($request);

        $daftar = $user->alamat()->orderByDesc('utama')->orderBy('id')->get()
            ->map(fn (Alamat $a) => [
                'id' => $a->id,
                'label' => $a->label,
                'penerima' => $a->nama_penerima,
                'telepon' => $a->no_hp,
                'jalan' => $a->alamat_lengkap,
                'kabupaten' => $a->kabupaten,
                'kecamatan' => $a->kecamatan,
                'kode_pos' => $a->kode_pos ?? '',
                'utama' => $a->utama,
            ])->values();

        return view('petani.alamat', [
            'daftarAlamat' => $daftar,
            'bawaan' => [
                'penerima' => $user->name,
                'telepon' => $user->no_wa ?? '',
                'kabupaten' => $user->profilPetani?->kabupaten ?? '',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $this->petani($request);
        $data = $this->validasi($request);

        DB::transaction(function () use ($user, $data) {
            $adaUtama = $user->alamat()->where('utama', true)->lockForUpdate()->exists();
            $user->alamat()->create($data + ['utama' => ! $adaUtama]); // alamat pertama otomatis jadi utama
        });

        return redirect()->route('petani.alamat')->with('sukses', 'Alamat ditambahkan.');
    }

    public function update(Request $request, int $alamat): RedirectResponse
    {
        $user = $this->petani($request);
        $alamat = $this->pastikanMilik(Alamat::find($alamat), $user, 'user_id');

        $alamat->update($this->validasi($request));

        return redirect()->route('petani.alamat')->with('sukses', 'Alamat diperbarui.');
    }

    public function destroy(Request $request, int $alamat): RedirectResponse
    {
        $user = $this->petani($request);
        $alamat = $this->pastikanMilik(Alamat::find($alamat), $user, 'user_id');

        DB::transaction(function () use ($user, $alamat) {
            $alamat->delete();

            if ($alamat->utama) {
                $user->alamat()->orderBy('id')->first()?->update(['utama' => true]);
            }
        });

        return redirect()->route('petani.alamat')->with('sukses', 'Alamat dihapus.');
    }

    public function utama(Request $request, int $alamat): RedirectResponse
    {
        $user = $this->petani($request);
        $alamat = $this->pastikanMilik(Alamat::find($alamat), $user, 'user_id');

        DB::transaction(function () use ($user, $alamat) {
            $user->alamat()->where('id', '!=', $alamat->id)->update(['utama' => false]);
            $alamat->update(['utama' => true]);
        });

        return redirect()->route('petani.alamat')->with('sukses', 'Alamat utama diperbarui.');
    }

    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'nama_penerima' => ['required', 'string', 'max:255'],
            'no_hp' => ['required', 'string', 'max:20', 'regex:/^(\+62|62|0)[0-9\-\s]{8,16}$/'],
            'alamat_lengkap' => ['required', 'string', 'max:1000'],
            'kabupaten' => ['required', 'string', 'max:255'],
            'kecamatan' => ['required', 'string', 'max:255'],
            'kode_pos' => ['nullable', 'regex:/^[0-9]{5}$/'],
        ], $this->pesanValidasi() + [
            'kode_pos.regex' => 'Kode pos harus 5 digit angka.',
        ], [
            'label' => 'label',
            'nama_penerima' => 'nama penerima',
            'no_hp' => 'nomor HP',
            'alamat_lengkap' => 'alamat lengkap',
            'kabupaten' => 'kabupaten/kota',
            'kecamatan' => 'kecamatan',
            'kode_pos' => 'kode pos',
        ]);

        return array_map(fn ($v) => is_string($v) ? trim($v) : $v, $data) + ['kode_pos' => null];
    }
}
