<?php
// app/Models/Pesan.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesan extends Model
{
    protected $table = 'pesan';
    protected $guarded = ['id'];
    protected $casts = ['dibaca_pada' => 'datetime'];

    public function percakapan() { return $this->belongsTo(Percakapan::class); }
    public function pengirim() { return $this->belongsTo(User::class, 'pengirim_id'); }
}