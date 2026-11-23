<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Pencairan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PencairanController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'siap_dicairkan');

        $daftar = Pencairan::with(['pesanan.pembayaran', 'petani.profilPetani', 'pencair'])
            ->when($status !== 'semua', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $jumlah = Pencairan::selectRaw('status, count(*) as total')
            ->groupBy('status')->pluck('total', 'status');

        $nominal = Pencairan::selectRaw('status, sum(bagian_petani) as total')
            ->groupBy('status')->pluck('total', 'status');

        return view('admin.pencairan.index', compact('daftar', 'status', 'jumlah', 'nominal'));
    }

    public function transfer(Request $request, Pencairan $pencairan)
    {
        $data = $request->validate([
            'catatan' => ['nullable', 'string', 'max:200'],
        ]);

        $berhasil = DB::transaction(function () use ($pencairan, $data) {
            $baris = Pencairan::whereKey($pencairan->id)->lockForUpdate()->first();

            if (! $baris || $baris->status !== 'siap_dicairkan') {
                return false;
            }

            $baris->update([
                'status' => 'sudah_dicairkan',
                'dicairkan_pada' => now(),
                'dicairkan_oleh' => Auth::id(),
                'catatan' => $data['catatan'] ?? null,
            ]);

            return true;
        });

        return back()->with(
            $berhasil ? 'sukses' : 'peringatan',
            $berhasil ? 'Pencairan ditandai sudah ditransfer.' : 'Pencairan ini sudah diproses atau belum siap dicairkan.'
        );
    }

    /** Pesanan batal setelah dibayar: admin sudah mengembalikan dana ke pembeli. */
    public function refund(Pencairan $pencairan)
    {
        $berhasil = DB::transaction(function () use ($pencairan) {
            $baris = Pencairan::whereKey($pencairan->id)->lockForUpdate()->first();
            $bayar = $baris ? Pembayaran::where('pesanan_id', $baris->pesanan_id)->lockForUpdate()->first() : null;

            if (! $baris || $baris->status !== 'dibatalkan' || $bayar?->status !== 'perlu_refund') {
                return false;
            }

            $bayar->update(['status' => 'dikembalikan']);
            $baris->update(['catatan' => 'Refund ke pembeli selesai ('.now()->format('d M Y H.i').').']);

            return true;
        });

        return back()->with(
            $berhasil ? 'sukses' : 'peringatan',
            $berhasil ? 'Refund ditandai selesai.' : 'Refund ini sudah ditandai atau tidak diperlukan.'
        );
    }
}