<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Pesanan extends Model
{
    protected $table = 'pesanan';

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::updated(function (Pesanan $pesanan) {
            if ($pesanan->wasChanged('status')) {
                Pencairan::sinkronDenganStatus($pesanan);
            }
        });
    }

    public function pengusaha()
    {
        return $this->belongsTo(User::class, 'pengusaha_id');
    }

    public function petani()
    {
        return $this->belongsTo(User::class, 'petani_id');
    }

    public function item()
    {
        return $this->hasMany(PesananItem::class);
    }

    public function riwayat()
    {
        return $this->hasMany(RiwayatStatusPesanan::class)
            ->orderBy('created_at');
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class);
    }

    public function ulasan()
    {
        return $this->hasOne(Ulasan::class);
    }

    // Mengubah status sekaligus mencatat riwayatnya.
    public function ubahStatus(
        string $status,
        ?string $keterangan = null
    ): void {
        DB::transaction(function () use ($status, $keterangan) {
            $this->update([
                'status' => $status,
            ]);

            $this->riwayat()->create([
                'status' => $status,
                'keterangan' => $keterangan,
                'diubah_oleh' => Auth::id(),
            ]);
        });
    }

    public static function buatKode(): string
    {
        return 'TL-' . now()->format('ymd') . '-'
            . strtoupper(substr(uniqid(), -5));
    }
}
