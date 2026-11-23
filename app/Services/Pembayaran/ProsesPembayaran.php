<?php

namespace App\Services\Pembayaran;

use App\Models\Pembayaran;
use App\Models\Pencairan;
use App\Models\Pesanan;
use App\Services\Petani\AlurPesanan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Menerapkan status transaksi Midtrans ke pembayaran + pesanan + ledger.
 * Dipanggil dari webhook DAN tombol "Cek Status", jadi harus idempotent.
 * Input = respons GET /v2/{order_id}/status (bukan body notifikasi mentah).
 */
class ProsesPembayaran
{
    /** Status Midtrans yang membatalkan pesanan (lewat AlurPesanan, stok ikut kembali). */
    public const BATALKAN_PESANAN = ['cancel', 'expire'];

      public static function pesananIdDariOrderId(string $orderId): ?int
    {
        // Format baru: {kode pesanan}-{timestamp}, mis. TL-261123-AB12C-1732321234
        if (preg_match('/^(.+)-\d{9,}$/', $orderId, $m)) {
            $id = Pesanan::query()->where('kode', $m[1])->value('id');

            if ($id) {
                return (int) $id;
            }
        }

        // Format lama: TL-{id}-{timestamp}, untuk transaksi yang sudah terlanjur dibuat
        return preg_match('/^TL-(\d+)-\d+$/', $orderId, $m) ? (int) $m[1] : null;
    }

    /**
     * @param  array<string, mixed>  $data  respons status Midtrans
     * @return array{ok: bool, hasil: string, status_pesanan: ?string}
     */
    public static function terapkan(array $data): array
    {
        $orderId = (string) ($data['order_id'] ?? '');
        $pesananId = self::pesananIdDariOrderId($orderId);

        if (! $pesananId) {
            return ['ok' => false, 'hasil' => 'order_id_tidak_dikenal', 'status_pesanan' => null];
        }

        $transaksi = (string) ($data['transaction_status'] ?? '');
        $fraud = $data['fraud_status'] ?? null;

        $hasil = match (true) {
            $transaksi === 'settlement' => 'lunas',
            $transaksi === 'capture' && $fraud === 'accept' => 'lunas',
            in_array($transaksi, ['capture', 'pending', 'authorize'], true) => 'pending',
            in_array($transaksi, self::BATALKAN_PESANAN, true) => 'batal',
            in_array($transaksi, ['deny', 'failure'], true) => 'ditolak',
            in_array($transaksi, ['refund', 'chargeback'], true) => 'refund',
            default => 'abaikan',
        };

        return DB::transaction(function () use ($data, $orderId, $pesananId, $transaksi, $hasil) {
            // Urutan kunci sama dengan alur lain: pesanan dulu, baru pembayaran.
            $pesanan = Pesanan::query()->whereKey($pesananId)->lockForUpdate()->first();
            $pembayaran = $pesanan ? Pembayaran::query()->where('pesanan_id', $pesananId)->lockForUpdate()->first() : null;

            if (! $pesanan || ! $pembayaran) {
                Log::warning('Midtrans: pesanan/pembayaran tidak ditemukan', ['order_id' => $orderId]);

                return ['ok' => false, 'hasil' => 'pesanan_tidak_ada', 'status_pesanan' => null];
            }

            // Nominal dari Midtrans harus sama dengan nominal pembayaran kita.
            $gross = (int) round((float) ($data['gross_amount'] ?? 0));
            if ($gross !== (int) $pembayaran->jumlah) {
                Log::error('Midtrans: nominal tidak cocok', ['order_id' => $orderId, 'midtrans' => $gross, 'lokal' => $pembayaran->jumlah]);

                return ['ok' => false, 'hasil' => 'nominal_tidak_cocok', 'status_pesanan' => $pesanan->status];
            }

            $terkini = $orderId === $pembayaran->midtrans_order_id;
            $sudahFinal = in_array($pembayaran->status, ['lunas', 'perlu_refund', 'dikembalikan'], true);

            // Notifikasi lama/terlambat tidak boleh menurunkan pembayaran yang sudah lunas,
            // dan order_id lama tidak boleh membatalkan pesanan kalau sudah ada token baru.
            if ($hasil !== 'lunas' && $hasil !== 'refund' && ($sudahFinal || ! $terkini)) {
                return ['ok' => true, 'hasil' => 'diabaikan', 'status_pesanan' => $pesanan->status];
            }

            $catatan = array_filter([
                'status_midtrans' => $transaksi,
                'transaction_id' => $data['transaction_id'] ?? null,
                'metode' => $data['payment_type'] ?? null,
                'payload' => $data,
            ], fn ($v) => $v !== null);

            switch ($hasil) {
                case 'lunas':
                    if ($pembayaran->status === 'lunas') {
                        return ['ok' => true, 'hasil' => 'sudah_lunas', 'status_pesanan' => $pesanan->status];
                    }

                    $pembayaran->update($catatan + [
                        'midtrans_order_id' => $orderId,
                        'status' => 'lunas',
                        'dibayar_pada' => now(),
                    ]);
                    Pencairan::buatDariPesanan($pesanan);

                    if ($pesanan->status === 'menunggu_pembayaran') {
                        $metode = $data['payment_type'] ?? null;
                        AlurPesanan::pindah(
                            $pesanan->id, 'diproses',
                            'Pembayaran diterima via Midtrans'.($metode ? " ({$metode})" : ''),
                            null, ['menunggu_pembayaran']
                        );
                        $pesanan->refresh();
                    }

                    // Sudah dibatalkan/ditolak sebelum uangnya masuk: tandai perlu refund.
                    if (in_array($pesanan->status, ['dibatalkan', 'ditolak'], true)) {
                        Pencairan::sinkronDenganStatus($pesanan);
                    }
                    break;

                case 'pending':
                    $pembayaran->update($catatan);
                    break;

                case 'batal':
                    $pembayaran->update($catatan + ['status' => 'gagal', 'snap_token' => null, 'snap_kedaluwarsa' => null]);

                    if ($pesanan->status === 'menunggu_pembayaran') {
                        AlurPesanan::pindah(
                            $pesanan->id, 'dibatalkan',
                            'Pembayaran Midtrans '.($transaksi === 'expire' ? 'kedaluwarsa' : 'dibatalkan'),
                            null, ['menunggu_pembayaran']
                        );
                        $pesanan->refresh();
                    }
                    break;

                case 'ditolak':
                    // Pesanan TIDAK dibatalkan: pembeli boleh coba lagi sampai tenggat.
                    $pembayaran->update($catatan + ['status' => 'gagal', 'snap_token' => null, 'snap_kedaluwarsa' => null]);
                    break;

                case 'refund':
                    $pembayaran->update($catatan + ['status' => 'dikembalikan']);

                    $ledger = Pencairan::where('pesanan_id', $pesanan->id)->first();
                    if ($ledger?->status === 'sudah_dicairkan') {
                        $ledger->update(['catatan' => 'REFUND setelah dana petani dicairkan. Perlu ditindaklanjuti manual.']);
                    } elseif ($ledger) {
                        $ledger->update(['status' => 'dibatalkan', 'catatan' => 'Dana dikembalikan ke pembeli (refund Midtrans).']);
                    }

                    if ($pesanan->status === 'diproses') {
                        AlurPesanan::pindah($pesanan->id, 'dibatalkan', 'Dibatalkan: pembayaran direfund via Midtrans', null, ['diproses']);
                        $pesanan->refresh();
                    } else {
                        Log::warning('Midtrans: refund pada pesanan yang sudah berjalan, cek manual', ['pesanan_id' => $pesanan->id, 'status' => $pesanan->status]);
                    }
                    break;

                default:
                    $pembayaran->update(array_intersect_key($catatan, ['status_midtrans' => 1, 'payload' => 1]));
                    Log::info('Midtrans: status dicatat tanpa aksi', ['order_id' => $orderId, 'status' => $transaksi]);
            }

            return ['ok' => true, 'hasil' => $hasil, 'status_pesanan' => $pesanan->status];
        });
    }
}