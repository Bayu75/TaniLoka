<?php
// app/Models/RiwayatStatusPesanan.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatStatusPesanan extends Model
{
    protected $table = 'riwayat_status_pesanan';
    protected $guarded = ['id'];

    public $timestamps = false; // tabel ini hanya punya kolom created_at
    protected $casts = ['created_at' => 'datetime'];

    public function pesanan() { return $this->belongsTo(Pesanan::class); }
    public function pengubah() { return $this->belongsTo(User::class, 'diubah_oleh'); }
}