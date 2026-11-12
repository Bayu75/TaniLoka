<?php
// app/Models/Alamat.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alamat extends Model
{
    protected $table = 'alamat';
    protected $guarded = ['id'];
    protected $casts = ['utama' => 'boolean'];

    public function user() { return $this->belongsTo(User::class); }
}