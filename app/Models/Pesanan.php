<?php
// app/Models/Pesanan.php
namespace App\Models;
use Illuminate\Support\Facades\Auth;

use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    protected $table = 'pesanan';
    protected $guarded = ['id'];

    public function pengusaha() { return $this->belongsTo(User::class, 'pengusaha_id'); }
    public function petani() { return $this->belongsTo(User::class, 'petani_id'); }
    public function item() { return $this->hasMany(PesananItem::class); }
    public function riwayat() { return $this->hasMany(RiwayatStatusPesanan::class)->orderBy('created_at'); }
    public function pembayaran() { return $this->hasOne(Pembayaran::class); }
    public function ulasan() { return $this->hasOne(Ulasan::class); }

    // Mengubah status sekaligus mencatat riwayatnya untuk timeline
    public function ubahStatus(string $status, ?string $keterangan = null): void
    {
        $this->update(['status' => $status]);

        $this->riwayat()->create([
            'status' => $status,
            'keterangan' => $keterangan,
            'diubah_oleh' => Auth::id(),
        ]);
    }

    public static function buatKode(): string
    {
        return 'TL-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -5));
    }
}