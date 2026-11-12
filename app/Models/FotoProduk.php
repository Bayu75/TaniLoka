<?php
// app/Models/FotoProduk.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FotoProduk extends Model
{
    protected $table = 'foto_produk';
    protected $guarded = ['id'];

    public function produk() { return $this->belongsTo(Produk::class); }

    // Dipakai di Blade: <img src="{{ $foto->url }}">
    public function getUrlAttribute(): string
    {
        return str_starts_with($this->path, 'http')
            ? $this->path
            : asset('storage/' . $this->path);
    }
}