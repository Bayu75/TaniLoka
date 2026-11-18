<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Controller;
use App\Models\Alamat;
use Illuminate\Http\Request;

class AlamatController extends Controller
{
    public function index()
    {
        $alamat = Alamat::where('user_id', auth()->id())->where('utama', true)->first();

        return view('petani.alamat', compact('alamat'));
    }

    public function simpan(Request $request)
    {
        $data = $request->validate([
            'label'          => 'required|string|max:50',
            'nama_penerima'  => 'required|string|max:100',
            'no_hp'          => 'required|string|max:20',
            'alamat_lengkap' => 'required|string|max:500',
            'kecamatan'      => 'required|string|max:100',
            'kabupaten'      => 'required|string|max:100',
            'kode_pos'       => 'nullable|string|max:10',
            'catatan'        => 'nullable|string|max:255',
            'latitude'       => 'nullable|numeric|between:-90,90',
            'longitude'      => 'nullable|numeric|between:-180,180',
        ]);

        Alamat::updateOrCreate(
            ['user_id' => auth()->id(), 'utama' => true],
            $data
        );

        if ($request->input('dari') === 'verifikasi') {
            return redirect()->route('petani.verifikasi')
                ->with('sukses', 'Alamat berhasil disimpan. Lanjutkan pengajuan verifikasi.');
        }

        return redirect()
            ->route('petani.alamat')
            ->with('sukses', 'Alamat berhasil disimpan.');
    }

    public function hapus(Request $request)
    {
        Alamat::where('user_id', auth()->id())->where('utama', true)->delete();

        return redirect()->route('petani.alamat', $request->input('dari') === 'verifikasi' ? ['dari' => 'verifikasi'] : [])
            ->with('sukses', 'Alamat kebun dihapus.');
    }
}