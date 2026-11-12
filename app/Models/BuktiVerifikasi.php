<?php
// app/Models/BuktiVerifikasi.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuktiVerifikasi extends Model
{
    protected $table = 'bukti_verifikasi';
    protected $guarded = ['id'];

    public function verifikasi() { return $this->belongsTo(VerifikasiAkun::class, 'verifikasi_id'); }
}