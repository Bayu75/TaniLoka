<?php
// app/Models/ProfilPetani.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilPetani extends Model
{
    protected $table = 'profil_petani';
    protected $guarded = ['id'];
    protected $casts = ['terverifikasi_pada' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
}