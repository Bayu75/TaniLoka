<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Buku besar bagian petani per pesanan.
 * Status: tertahan -> siap_dicairkan -> sudah_dicairkan, atau dibatalkan (pesanan batal setelah dibayar).
 */
class Pencairan extends Model
{
    protected $table = 'pencairan';

    protected $guarded = ['id'];

    protected $casts = [
        'komisi_persen' => 'float',
        'siap_pada' => 'datetime',
        'dicairkan_pada' => 'datetime',
    ];

    public const STATUS = ['tertahan', 'siap_dicairkan', 'sudah_dicairkan', 'dibatalkan'];

    public function pesanan() { return $this->belongsTo(Pesanan::class); }
    public function petani() { return $this->belongsTo(User::class, 'petani_id'); }
    public function pencair() { return $this->belongsTo(User::class, 'dicairkan_oleh'); }

    /** Dipanggil saat pembayaran lunas. Aman dipanggil berulang. */
    public static function buatDariPesanan(Pesanan $pesanan): self
    {
        $persen = (float) config('taniloka.komisi_persen');
        $subtotal = (int) $pesanan->subtotal;
        $komisi = (int) round($subtotal * $persen / 100);
        $ongkir = config('taniloka.ongkir_ke_petani') ? (int) $pesanan->ongkir : 0;

        return static::firstOrCreate(
            ['pesanan_id' => $pesanan->id],
            [
                'petani_id' => $pesanan->petani_id,
                'subtotal' => $subtotal,
                'ongkir' => $ongkir,
                'komisi_persen' => $persen,
                'komisi' => $komisi,
                'bagian_petani' => $subtotal - $komisi + $ongkir,
                'status' => 'tertahan',
            ]
        );
    }

    /**
     * Dipanggil otomatis dari Pesanan::booted() setiap status pesanan berubah.
     *   selesai              : tertahan -> siap_dicairkan
     *   dibatalkan / ditolak : kalau sudah dibayar, pembayaran jadi 'perlu_refund' dan ledger 'dibatalkan'
     */
    public static function sinkronDenganStatus(Pesanan $pesanan): void
    {
        if ($pesanan->status === 'selesai') {
            static::where('pesanan_id', $pesanan->id)
                ->where('status', 'tertahan')
                ->update(['status' => 'siap_dicairkan', 'siap_pada' => now()]);

            return;
        }

        if (in_array($pesanan->status, ['dibatalkan', 'ditolak'], true)) {
            Pembayaran::where('pesanan_id', $pesanan->id)
                ->where('status', 'lunas')
                ->update(['status' => 'perlu_refund']);

            static::where('pesanan_id', $pesanan->id)
                ->whereIn('status', ['tertahan', 'siap_dicairkan'])
                ->update([
                    'status' => 'dibatalkan',
                    'catatan' => 'Pesanan batal setelah dibayar. Dana dikembalikan ke pembeli secara manual.',
                ]);
        }
    }
}