<?php
// app/Models/Ulasan.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ulasan extends Model
{
    protected $table = 'ulasan';
    protected $guarded = ['id'];

    public function pesanan() { return $this->belongsTo(Pesanan::class); }
    public function pengusaha() { return $this->belongsTo(User::class, 'pengusaha_id'); }
    public function petani() { return $this->belongsTo(User::class, 'petani_id'); }
}