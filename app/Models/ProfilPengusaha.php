<?php
// app/Models/ProfilPengusaha.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilPengusaha extends Model
{
    protected $table = 'profil_pengusaha';
    protected $guarded = ['id'];

    public function user() { return $this->belongsTo(User::class); }
}