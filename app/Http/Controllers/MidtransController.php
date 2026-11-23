<?php

namespace App\Http\Controllers;

use App\Services\Pembayaran\Midtrans;
use App\Services\Pembayaran\ProsesPembayaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Webhook Midtrans. Route POST /midtrans/notifikasi, tanpa auth dan tanpa CSRF.
 *   200 diterima/diabaikan, 400 body tidak lengkap, 403 signature salah,
 *   503 gagal cek ulang/memproses (Midtrans akan kirim ulang).
 */
class MidtransController extends Controller
{
    public function notifikasi(Request $request): JsonResponse
    {
        $data = $request->json()->all() ?: $request->all();

        foreach (['order_id', 'status_code', 'gross_amount', 'signature_key'] as $kolom) {
            if (! isset($data[$kolom]) || ! is_scalar($data[$kolom])) {
                return response()->json(['pesan' => 'Data notifikasi tidak lengkap.'], 400);
            }
        }

        try {
            $sah = Midtrans::signatureValid($data);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['pesan' => 'Konfigurasi Midtrans belum lengkap.'], 503);
        }

        if (! $sah) {
            Log::warning('Midtrans: signature_key tidak cocok', ['order_id' => $data['order_id']]);

            return response()->json(['pesan' => 'Signature tidak valid.'], 403);
        }

        if (! ProsesPembayaran::pesananIdDariOrderId((string) $data['order_id'])) {
            return response()->json(['pesan' => 'order_id bukan milik TaniLoka, diabaikan.']);
        }

        try {
            // Jangan percaya isi notifikasi: tanya ulang status resminya ke Midtrans.
            $status = Midtrans::status((string) $data['order_id']);

            if ($status === null) {
                return response()->json(['pesan' => 'Transaksi belum ada di Midtrans, diabaikan.']);
            }

            $hasil = ProsesPembayaran::terapkan($status);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['pesan' => 'Gagal memproses notifikasi.'], 503);
        }

        return response()->json(['pesan' => 'OK', 'hasil' => $hasil['hasil']]);
    }
}