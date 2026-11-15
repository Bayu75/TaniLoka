<?php

namespace App\Http\Controllers\Pengusaha;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AlamatController extends Controller
{
    public function index(Request $request)
    {
        $alamat = $request->user()->alamat()->where('utama', true)->first();

        return view('pengusaha.alamat', compact('alamat'));
    }

    public function simpan(Request $request)
    {
        $data = $request->validate([
            'label'          => 'required|string|max:50',
            'nama_penerima'  => 'required|string|max:100',
            'no_hp'          => 'required|string|max:20',
            'alamat_lengkap' => 'required|string|max:500',
            'kabupaten'      => 'required|string|max:100',
            'kecamatan'      => 'required|string|max:100',
            'kode_pos'       => 'nullable|string|max:10',
            'catatan'        => 'nullable|string|max:255',
            'latitude'       => 'required|numeric|between:-90,90',
            'longitude'      => 'required|numeric|between:-180,180',
        ], [
            'latitude.required' => 'Pilih lokasi usaha di peta terlebih dahulu.',
        ]);

        $request->user()->alamat()->updateOrCreate(['utama' => true], $data);

        return redirect()->route('pengusaha.alamat')->with('sukses', 'Alamat berhasil disimpan.');
    }

    public function hapus(Request $request)
    {
        $request->user()->alamat()->where('utama', true)->delete();

        return redirect()->route('pengusaha.alamat')->with('sukses', 'Alamat berhasil dihapus.');
    }
}