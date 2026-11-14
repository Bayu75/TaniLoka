<?php
// app/Models/Pesan.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesan extends Model
{
    protected $table = 'pesan';
    protected $guarded = ['id'];
    protected $casts = [
        'dibaca_pada' => 'datetime',
        'harga_penawaran' => 'decimal:2',
        'jumlah_penawaran' => 'integer',
        'total_penawaran' => 'decimal:2',
    ];

    public function percakapan() { return $this->belongsTo(Percakapan::class); }
    public function pengirim() { return $this->belongsTo(User::class, 'pengirim_id'); }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
