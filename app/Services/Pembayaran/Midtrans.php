<?php

namespace App\Services\Pembayaran;

use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Klien tipis Midtrans (Snap + Core API) memakai Http client Laravel, tanpa paket composer.
 * URL otomatis ikut MIDTRANS_IS_PRODUCTION. Kunci dibaca dari config('services.midtrans').
 */
class Midtrans
{
    public static function produksi(): bool
    {
        return (bool) config('services.midtrans.is_production');
    }

    public static function clientKey(): string
    {
        return (string) config('services.midtrans.client_key');
    }

    private static function serverKey(): string
    {
        $kunci = (string) config('services.midtrans.server_key');

        if ($kunci === '') {
            throw new MidtransException('MIDTRANS_SERVER_KEY belum diisi.');
        }

        return $kunci;
    }

    public static function urlSnapJs(): string
    {
        return self::produksi()
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    private static function urlSnapApi(): string
    {
        return self::produksi()
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    private static function urlCoreApi(): string
    {
        return self::produksi()
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';
    }

    private static function http(): PendingRequest
    {
        return Http::withBasicAuth(self::serverKey(), '')
            ->acceptJson()
            ->asJson()
            ->timeout(15);
    }

    /**
     * signature_key = SHA512(order_id + status_code + gross_amount + ServerKey)
     * Nilai dipakai persis sebagai string seperti yang dikirim Midtrans (gross_amount "150000.00").
     */
    public static function signatureValid(array $data): bool
    {
        $diterima = strtolower((string) ($data['signature_key'] ?? ''));

        if ($diterima === '') {
            return false;
        }

        $seharusnya = hash('sha512',
            ($data['order_id'] ?? '')
            .($data['status_code'] ?? '')
            .($data['gross_amount'] ?? '')
            .(config('services.midtrans.signature_key') ?: self::serverKey())
        );

        return hash_equals($seharusnya, $diterima);
    }

    /**
     * Minta Snap token.
     *
     * @return array{token: string, redirect_url: ?string}
     */
    public static function buatToken(Pesanan $pesanan, ?User $pembeli, string $orderId, int $menitKedaluwarsa): array
    {
        $total = (int) $pesanan->total;

        // item_details harus berjumlah persis gross_amount, kalau tidak Midtrans menolak.
        $baris = array_values(array_filter([
            ['id' => 'SUBTOTAL', 'price' => (int) $pesanan->subtotal, 'quantity' => 1, 'name' => 'Subtotal produk'],
            ['id' => 'ONGKIR', 'price' => (int) $pesanan->ongkir, 'quantity' => 1, 'name' => 'Ongkos kirim'],
            ['id' => 'PPN', 'price' => (int) $pesanan->ppn, 'quantity' => 1, 'name' => 'PPN (10%)'],
            ['id' => 'LAYANAN', 'price' => (int) $pesanan->biaya_layanan, 'quantity' => 1, 'name' => 'Biaya layanan'],
        ], fn ($b) => $b['price'] > 0));

        if (array_sum(array_column($baris, 'price')) !== $total) {
            $baris = [['id' => 'PESANAN', 'price' => $total, 'quantity' => 1, 'name' => 'Pesanan '.$pesanan->kode]];
        }

        $pelanggan = array_filter([
            'first_name' => mb_substr((string) $pesanan->nama_penerima, 0, 255),
            'email' => $pembeli?->email,
            'phone' => $pesanan->no_hp_penerima,
        ], fn ($v) => filled($v));

        $respons = self::http()->post(self::urlSnapApi(), [
            'transaction_details' => ['order_id' => $orderId, 'gross_amount' => $total],
            'item_details' => $baris,
            'customer_details' => $pelanggan,
            // start_time eksplisit supaya masa berlaku VA/QRIS ikut tenggat pesanan.
            'expiry' => [
                'start_time' => now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s O'),
                'unit' => 'minutes',
                'duration' => $menitKedaluwarsa,
            ],
        ]);

        if (! $respons->successful() || blank($respons->json('token'))) {
            Log::error('Midtrans: gagal membuat Snap token', [
                'order_id' => $orderId,
                'http' => $respons->status(),
                'body' => $respons->json() ?? $respons->body(),
            ]);

            throw new MidtransException('Gagal membuat transaksi Midtrans (HTTP '.$respons->status().').');
        }

        return ['token' => $respons->json('token'), 'redirect_url' => $respons->json('redirect_url')];
    }

    /**
     * Status transaksi dari Midtrans (sumber kebenaran).
     * Kalau transaksi belum ada (pembeli belum pilih metode), dikembalikan null.
     *
     * @return array<string, mixed>|null
     */
    public static function status(string $orderId): ?array
    {
        $respons = self::http()->get(self::urlCoreApi().'/'.rawurlencode($orderId).'/status');

        if ($respons->status() === 404 || (string) $respons->json('status_code') === '404') {
            return null;
        }

        if (! $respons->successful() || ! is_array($respons->json()) || blank($respons->json('transaction_status'))) {
            Log::error('Midtrans: gagal membaca status', ['order_id' => $orderId, 'http' => $respons->status(), 'body' => $respons->body()]);

            throw new MidtransException('Gagal membaca status Midtrans (HTTP '.$respons->status().').');
        }

        return $respons->json();
    }

    /** Batalkan transaksi yang masih pending (best effort, kegagalan hanya dicatat di log). */
    public static function batalkan(string $orderId): void
    {
        try {
            $respons = self::http()->post(self::urlCoreApi().'/'.rawurlencode($orderId).'/cancel');
            Log::info('Midtrans: cancel', ['order_id' => $orderId, 'http' => $respons->status(), 'body' => $respons->json()]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}