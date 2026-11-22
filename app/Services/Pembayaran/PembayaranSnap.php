<?php

namespace App\Services\Pembayaran;

use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\User;
use App\Services\Petani\AlurPesanan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Menyiapkan Snap token untuk pesanan yang sedang menunggu_pembayaran.
 *  - Token hanya dibuat saat status menunggu_pembayaran dan tenggat belum lewat.
 *  - Masa berlaku token = sisa waktu ke tenggat (AlurPesanan::tenggat) dikurangi 1 menit.
 *  - Token yang masih berlaku dipakai ulang, tidak bikin order_id baru.
 */
class PembayaranSnap
{
    /** Midtrans menolak expiry terlalu pendek, jadi sisa waktu di bawah ini tidak dilayani. */
    public const MIN_MENIT = 6;

    /**
     * @return array{token: string, order_id: string, kedaluwarsa: \Illuminate\Support\Carbon}
     */
    public static function siapkan(int $pesananId, User $pembeli): array
    {
        return DB::transaction(function () use ($pesananId, $pembeli) {
            $pesanan = Pesanan::query()
                ->where('id', $pesananId)
                ->where('pengusaha_id', $pembeli->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($pesanan->status !== 'menunggu_pembayaran') {
                throw ValidationException::withMessages(['pembayaran' => 'Pesanan tidak sedang menunggu pembayaran.']);
            }

            $pembayaran = Pembayaran::query()->where('pesanan_id', $pesanan->id)->lockForUpdate()->first()
                ?? Pembayaran::create([
                    'pesanan_id' => $pesanan->id,
                    'jumlah' => (int) $pesanan->total,
                    'status' => 'belum_bayar',
                ]);

            if ($pembayaran->status === 'lunas') {
                throw ValidationException::withMessages(['pembayaran' => 'Pesanan ini sudah dibayar.']);
            }

            // Pakai ulang token yang masih berlaku (sisa > 1 menit) dengan nominal yang sama.
            if (
                $pembayaran->snap_token
                && $pembayaran->midtrans_order_id
                && $pembayaran->snap_kedaluwarsa?->gt(now()->addMinute())
                && (int) $pembayaran->jumlah === (int) $pesanan->total
            ) {
                return [
                    'token' => $pembayaran->snap_token,
                    'order_id' => $pembayaran->midtrans_order_id,
                    'kedaluwarsa' => $pembayaran->snap_kedaluwarsa,
                ];
            }

            $batas = AlurPesanan::tenggat($pesanan);

            if (! $batas || now()->gte($batas)) {
                throw ValidationException::withMessages(['pembayaran' => 'Batas waktu pembayaran sudah lewat.']);
            }

            $menit = (int) floor(now()->diffInSeconds($batas, false) / 60) - 1;

            if ($menit < self::MIN_MENIT) {
                throw ValidationException::withMessages(['pembayaran' => 'Sisa waktu pembayaran terlalu singkat untuk membuat transaksi baru.']);
            }

            $orderId = 'TL-'.$pesanan->id.'-'.now()->timestamp;
            $hasil = Midtrans::buatToken($pesanan, $pembeli, $orderId, $menit);
            $kedaluwarsa = now()->addMinutes($menit);

            $pembayaran->update([
                'jumlah' => (int) $pesanan->total,
                'midtrans_order_id' => $orderId,
                'snap_token' => $hasil['token'],
                'snap_kedaluwarsa' => $kedaluwarsa,
                'status_midtrans' => null,
            ]);

            return ['token' => $hasil['token'], 'order_id' => $orderId, 'kedaluwarsa' => $kedaluwarsa];
        });
    }
}