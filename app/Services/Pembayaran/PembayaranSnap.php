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

            // order_id = kode pesanan + timestamp, supaya tetap unik tiap percobaan bayar
            // tapi awalannya sama dengan #kode yang dilihat pembeli.
            $kodeAman = preg_replace('/[^A-Za-z0-9\-_.~]/', '', (string) $pesanan->kode);
            $orderId = $kodeAman.'-'.now()->timestamp;
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

        /**
     * Dipanggil sebelum siapkan() saat pembeli ingin ganti metode bayar.
     * Melepas transaksi lama (pending) supaya siapkan() membuat token + order_id baru.
     */
    public static function lepasTransaksiLama(int $pesananId, User $pembeli): void
    {
        $pesanan = Pesanan::query()
            ->where('id', $pesananId)
            ->where('pengusaha_id', $pembeli->id)
            ->firstOrFail();

        $pembayaran = Pembayaran::query()->where('pesanan_id', $pesanan->id)->first();

        if (! $pembayaran?->midtrans_order_id || $pembayaran->status === 'lunas') {
            return;
        }

        $lama = $pembayaran->midtrans_order_id;

        // Jangan sampai bayar dobel: cek dulu apakah transaksi lama ternyata sudah dibayar.
        $status = Midtrans::status($lama); // null = token belum pernah dipakai memilih metode
        $transaksi = $status['transaction_status'] ?? null;

        if (in_array($transaksi, ['settlement', 'capture'], true)) {
            ProsesPembayaran::terapkan($status);

            throw ValidationException::withMessages([
                'pembayaran' => 'Pembayaran sebelumnya sudah masuk, jadi metode tidak perlu diganti. Silakan muat ulang halaman.',
            ]);
        }

        // Lepas dulu dari pesanan, BARU batalkan di Midtrans. Notifikasi "cancel" dari
        // order_id lama nanti dianggap bukan transaksi terkini, jadi pesanan tidak ikut batal.
        DB::transaction(function () use ($pesanan, $lama) {
            Pesanan::query()->whereKey($pesanan->id)->lockForUpdate()->first();

            Pembayaran::query()
                ->where('pesanan_id', $pesanan->id)
                ->where('midtrans_order_id', $lama)
                ->where('status', '!=', 'lunas')
                ->lockForUpdate()
                ->first()
                ?->update([
                    'midtrans_order_id' => null,
                    'snap_token' => null,
                    'snap_kedaluwarsa' => null,
                    'status_midtrans' => null,
                ]);
        });

        if ($transaksi === 'pending') {
            Midtrans::batalkan($lama);
        }
    }
}