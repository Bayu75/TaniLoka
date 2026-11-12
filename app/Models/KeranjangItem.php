<?php
// app/Models/KeranjangItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeranjangItem extends Model
{
    protected $table = 'keranjang_item';
    protected $guarded = ['id'];
    protected $casts = ['jumlah_kg' => 'decimal:2'];

    public function user() { return $this->belongsTo(User::class); }
    public function produk() { return $this->belongsTo(Produk::class); }

    public function getSubtotalAttribute(): int
    {
        return (int) round($this->produk->harga_per_kg * $this->jumlah_kg);
    }
}