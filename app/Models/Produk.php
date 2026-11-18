<?php
// app/Models/Produk.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produk extends Model
{
    use SoftDeletes;

    protected $table = 'produk';
    protected $guarded = ['id'];
    protected $casts = [
        'stok_kg' => 'decimal:2',
        'min_order_kg' => 'decimal:2',
        'tanggal_panen' => 'date',
        'aktif' => 'boolean',
    ];

    public function petani() { return $this->belongsTo(User::class, 'petani_id'); }
    public function kategori() { return $this->belongsTo(Kategori::class); }
    public function foto() { return $this->hasMany(FotoProduk::class)->orderBy('urutan'); }
    public function fotoUtama() { return $this->hasOne(FotoProduk::class)->orderBy('urutan'); }

    // Produk::aktif()->get() hanya menampilkan produk aktif yang masih ada stoknya
    public function scopeAktif($query)
    {
        return $query->where('aktif', true)->where('stok_kg', '>', 0);
    }

        public function sedangFlashSale(): bool
    {
        if (is_null($this->harga_flash_sale) || is_null($this->flash_sale_sampai)) {
            return false;
        }

        return now()->lte(\Illuminate\Support\Carbon::parse($this->flash_sale_sampai)->endOfDay());
    }

    public function hargaAktif()
    {
        return $this->sedangFlashSale() ? $this->harga_flash_sale : $this->harga_per_kg;
    }
}