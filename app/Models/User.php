<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'peran', 'no_wa', 'foto', 'password',
        'status_verifikasi', 'terverifikasi_pada',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'terverifikasi_pada' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Peran
    public function isPetani(): bool { return $this->peran === 'petani'; }
    public function isPengusaha(): bool { return $this->peran === 'pengusaha'; }
    public function isAdmin(): bool { return $this->peran === 'admin'; }

    public function terverifikasi(): bool
    {
        return $this->status_verifikasi === 'terverifikasi';
    }

    // Profil
    public function profilPetani() { return $this->hasOne(ProfilPetani::class); }
    public function profilPengusaha() { return $this->hasOne(ProfilPengusaha::class); }
    public function alamat() { return $this->hasMany(Alamat::class); }
    public function alamatUtama() { return $this->hasOne(Alamat::class)->where('utama', true); }

    // Petani
    public function produk() { return $this->hasMany(Produk::class, 'petani_id'); }
    public function pesananMasuk() { return $this->hasMany(Pesanan::class, 'petani_id'); }
    public function verifikasi() { return $this->hasMany(VerifikasiAkun::class); }

    // Pengusaha
    public function keranjang() { return $this->hasMany(KeranjangItem::class); }
    public function pesanan() { return $this->hasMany(Pesanan::class, 'pengusaha_id'); }
}