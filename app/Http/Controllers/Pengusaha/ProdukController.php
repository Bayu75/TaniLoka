<?php
namespace App\Http\Controllers\Pengusaha;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\User;
use App\Models\Kategori;
use Illuminate\Http\Request;
use App\Models\ProfilPetani;

class ProdukController extends Controller
{

    // Halaman beranda pengusaha
    public function beranda()
    {
        $kategoriList = Kategori::all();

        $produkUnggulan = Produk::with([
            'petani',
            'petani.profilPetani',
            'kategori',
            'fotoUtama'
        ])
        ->aktif()
        ->latest()
        ->take(4)
        ->get();

        $produkNego = collect();

        return view('pengusaha.beranda', compact(
            'kategoriList',
            'produkUnggulan',
            'produkNego'
        ));
    }

    
public function katalog(Request $request)
{
    $query = Produk::with([
        'petani',
        'petani.profilPetani',
        'kategori',
        'fotoUtama'
    ])->aktif();

    // Pencarian produk, petani, kategori, dan kabupaten
    if ($request->filled('q')) {
        $keyword = $request->q;

        $query->where(function ($q) use ($keyword) {
            $q->where('nama', 'like', "%{$keyword}%")
                ->orWhereHas('petani', function ($petani) use ($keyword) {
                    $petani->where('name', 'like', "%{$keyword}%");
                })
                ->orWhereHas('kategori', function ($kategori) use ($keyword) {
                    $kategori->where('nama', 'like', "%{$keyword}%");
                })
                                ->orWhereHas('petani.alamatUtama', function ($alamat) use ($keyword) {
                    $alamat->where('kabupaten', 'like', "%{$keyword}%");
                });
        });
    }

    // Filter kategori
    if ($request->filled('cat')) {
        $query->whereHas('kategori', function ($kategori) use ($request) {
            $kategori->where('slug', $request->cat);
        });
    }

    // Filter kabupaten
    if ($request->filled('district')) {
            $query->whereHas('petani.alamatUtama', function ($alamat) use ($request) {
            $alamat->where('kabupaten', $request->district);
        });
    }

    // Filter harga minimum
    if ($request->filled('min_price')) {
        $query->where('harga_per_kg', '>=', $request->min_price);
    }

    // Filter harga maksimum
    if ($request->filled('max_price')) {
        $query->where('harga_per_kg', '<=', $request->max_price);
    }

    // Produk tersedia ditampilkan lebih dahulu
    $query->orderByRaw('CASE WHEN stok_kg > 0 THEN 0 ELSE 1 END');

    // Pengurutan produk
    switch ($request->input('sort', 'newest')) {
        case 'price-asc':
            $query->orderBy('harga_per_kg', 'asc');
            break;

        case 'price-desc':
            $query->orderBy('harga_per_kg', 'desc');
            break;

        default:
            $query->latest();
            break;
    }

    $produkList = $query->get();

    // Kategori dari database
    $kategoriList = Kategori::orderBy('nama')->get();

        $kabupatenList = \App\Models\Alamat::whereNotNull('kabupaten')
        ->where('kabupaten', '!=', '')
        ->whereIn('user_id', \App\Models\User::where('peran', 'petani')->select('id'))
        ->distinct()
        ->orderBy('kabupaten')
        ->pluck('kabupaten');

    // Nilai filter untuk ditampilkan kembali di Blade
    $q = trim($request->input('q', ''));
    $cat = $request->input('cat', '');
    $district = $request->input('district', '');
    $minPrice = $request->input('min_price', '');
    $maxPrice = $request->input('max_price', '');
    $sort = $request->input('sort', 'newest');

    $activeCount = (int) ($cat !== '')
        + (int) ($district !== '')
        + (int) ($minPrice !== '')
        + (int) ($maxPrice !== '')
        + (int) ($sort !== 'newest');

    return view('pengusaha.katalog', compact(
        'produkList',
        'kategoriList',
        'kabupatenList',
        'q',
        'cat',
        'district',
        'minPrice',
        'maxPrice',
        'sort',
        'activeCount'
    ));
    }

    // Detail produk
public function detail(Produk $produk)
{
    abort_unless($produk->aktif, 404);

    $produk->load(['petani.profilPetani', 'kategori', 'foto']);

    $produkLain = Produk::with(['fotoUtama', 'kategori'])
        ->aktif()
        ->where('petani_id', $produk->petani_id)
        ->where('id', '!=', $produk->id)
        ->latest()
        ->take(4)
        ->get();

    $sudahDiKeranjang = auth()->user()->keranjang()
        ->where('produk_id', $produk->id)
        ->first();

    return view('pengusaha.detail-produk', compact('produk', 'produkLain', 'sudahDiKeranjang'));
}

public function toko(User $petani)
    {
        abort_unless($petani->isPetani(), 404);

        $petani->load('profilPetani');

        $produkToko = Produk::with(['kategori', 'fotoUtama', 'petani.profilPetani'])
            ->where('petani_id', $petani->id)
            ->aktif()
            ->latest()
            ->get();

        return view('pengusaha.toko-petani', compact('petani', 'produkToko'));
    }
}
