<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VerifikasiAkun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VerifikasiController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'menunggu');

        $daftar = VerifikasiAkun::with('user')
            ->when($status !== 'semua', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $jumlah = VerifikasiAkun::selectRaw('status, count(*) as total')
            ->groupBy('status')->pluck('total', 'status');

        return view('admin.verifikasi.index', compact('daftar', 'status', 'jumlah'));
    }

    public function show(VerifikasiAkun $verifikasi)
    {
        $verifikasi->load(['user.profilPetani', 'user.profilPengusaha', 'bukti', 'peninjau']);

        return view('admin.verifikasi.show', compact('verifikasi'));
    }

    public function putuskan(Request $request, VerifikasiAkun $verifikasi)
    {
        $data = $request->validate([
            'keputusan'     => 'required|in:terverifikasi,perlu_perbaikan,ditolak',
            'catatan_admin' => 'required_unless:keputusan,terverifikasi|nullable|string|max:500',
        ], [
            'catatan_admin.required_unless' => 'Tuliskan alasan agar pengguna tahu apa yang harus diperbaiki.',
        ]);

        if ($verifikasi->status !== 'menunggu') {
            return back()->with('peringatan', 'Pengajuan ini sudah ditinjau sebelumnya.');
        }

        DB::transaction(function () use ($verifikasi, $data) {
            $verifikasi->update([
                'status'        => $data['keputusan'],
                'catatan_admin' => $data['catatan_admin'] ?? null,
                'ditinjau_oleh' => Auth::id(),
                'ditinjau_pada' => now(),
            ]);

            $verifikasi->user->update([
                'status_verifikasi'  => $data['keputusan'],
                'terverifikasi_pada' => $data['keputusan'] === 'terverifikasi' ? now() : null,
            ]);
        });

        $pesan = [
            'terverifikasi'   => 'Akun berhasil diverifikasi.',
            'perlu_perbaikan' => 'Permintaan perbaikan dikirim ke pengguna.',
            'ditolak'         => 'Pengajuan verifikasi ditolak.',
        ][$data['keputusan']];

        return redirect()->route('admin.verifikasi')->with('sukses', $pesan);
    }
}