<?php

namespace App\Http\Controllers\Pengusaha;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Services\Pembayaran\Midtrans;
use App\Services\Pembayaran\MidtransException;
use App\Services\Pembayaran\PembayaranSnap;
use App\Services\Pembayaran\ProsesPembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PembayaranController extends Controller
{
    public function bayar(Request $request, int $id)
    {
        try {
            $hasil = PembayaranSnap::siapkan($id, $request->user());
        } catch (MidtransException $e) {
            report($e);

            return response()->json(['pesan' => 'Pembayaran belum bisa dimulai. Coba lagi sebentar lagi.'], 502);
        }

        return response()->json([
            'token' => $hasil['token'],
            'order_id' => $hasil['order_id'],
            'kedaluwarsa' => $hasil['kedaluwarsa']->toIso8601String(),
        ]);
    }

    public function cekStatus(Request $request, int $id)
    {
        $pesanan = Pesanan::where('pengusaha_id', $request->user()->id)->findOrFail($id);
        $pembayaran = Pembayaran::where('pesanan_id', $pesanan->id)->first();

        $balas = function (string $kunci, string $pesan, int $kodeJson = 200) use ($request, $id) {
            if ($request->expectsJson()) {
                return response()->json(['pesan' => $pesan, 'ok' => $kunci === 'success'], $kodeJson);
            }

            return redirect()->route('pengusaha.pesanan.detail', $id)->with($kunci, $pesan);
        };

        if (! $pembayaran?->midtrans_order_id) {
            return $balas('error', 'Belum ada transaksi pembayaran untuk pesanan ini.', 422);
        }

        try {
            $status = Midtrans::status($pembayaran->midtrans_order_id);
        } catch (MidtransException $e) {
            Log::warning('Cek status pembayaran gagal', ['pesanan_id' => $id, 'error' => $e->getMessage()]);

            return $balas('error', 'Status pembayaran belum bisa dicek. Coba lagi sebentar lagi.', 502);
        }

        if ($status === null) {
            return $balas('error', 'Pembayaran belum terdeteksi. Selesaikan pembayaran di jendela Midtrans dulu.', 200);
        }

        $hasil = ProsesPembayaran::terapkan($status);

        $pesan = match ($hasil['hasil']) {
            'lunas', 'sudah_lunas' => 'Pembayaran diterima. Pesanan sedang diproses.',
            'pending' => 'Pembayaran masih menunggu. Selesaikan pembayaran sesuai instruksi Midtrans.',
            'batal', 'ditolak' => 'Pembayaran gagal atau kedaluwarsa.',
            default => 'Status pembayaran diperbarui.',
        };

        return $balas(in_array($hasil['hasil'], ['lunas', 'sudah_lunas'], true) ? 'success' : 'error', $pesan);
    }
}