<?php
// app/Models/PesananItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesananItem extends Model
{
    protected $table = 'pesanan_item';
    protected $guarded = ['id'];
    protected $casts = ['jumlah_kg' => 'decimal:2'];

    public function pesanan() { return $this->belongsTo(Pesanan::class); }
    public function produk() { return $this->belongsTo(Produk::class)->withTrashed(); }
}