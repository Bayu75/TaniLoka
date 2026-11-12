<?php
// app/Models/Percakapan.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Percakapan extends Model
{
    protected $table = 'percakapan';
    protected $guarded = ['id'];
    protected $casts = ['pesan_terakhir_pada' => 'datetime'];

    public function pengusaha() { return $this->belongsTo(User::class, 'pengusaha_id'); }
    public function petani() { return $this->belongsTo(User::class, 'petani_id'); }
    public function pesanan() { return $this->belongsTo(Pesanan::class); }
    public function pesan() { return $this->hasMany(Pesan::class)->orderBy('created_at'); }
}