<?php
// app/Models/Pembayaran.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';
    protected $guarded = ['id'];
    protected $casts = ['dibayar_pada' => 'datetime'];

    public function pesanan() { return $this->belongsTo(Pesanan::class); }
}