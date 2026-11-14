<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerifikasiAkun extends Model
{
    protected $table = 'verifikasi_akun';
    protected $guarded = ['id'];
    protected $casts = ['ditinjau_pada' => 'datetime'];

    public function user()     { return $this->belongsTo(User::class); }
    public function peninjau() { return $this->belongsTo(User::class, 'ditinjau_oleh'); }
    public function bukti()    { return $this->hasMany(BuktiVerifikasi::class, 'verifikasi_id'); }
}