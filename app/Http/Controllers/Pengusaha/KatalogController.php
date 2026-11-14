<?php

namespace App\Http\Controllers\Pengusaha;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Kategori;

class KatalogController extends Controller
{
    public function index()
    {
        $produkList = Produk::with([
            'petani.profilPetani',
            'kategori',
            'fotoUtama',
        ])
        ->where('aktif', true)
        ->latest()
        ->get()
        ->map(function ($produk) {
            return [
                'id' => $produk->id,
                'nama' => $produk->nama,
                'slug' => $produk->slug,
                'gambar' => $produk->fotoUtama?->path
                    ? asset($produk->fotoUtama->path)
                    : asset('images/no-image.png'),
                'kategori' => $produk->kategori?->nama ?? 'Lainnya',
                'harga' => $produk->harga_per_kg,
                'minimal' => $produk->min_order_kg,
                'stok' => $produk->stok_kg,
                'petani' => $produk->petani?->profilPetani?->nama_usaha_tani
                    ?? $produk->petani?->name
                    ?? 'Petani',
                'rating' => 0,
                'kabupaten' => $produk->petani?->profilPetani?->kabupaten
                    ?? '-',
                'badge' => $produk->grade,
                'nego' => true,
            ];
        })
        ->all();

        $kategoriList = Kategori::all()
            ->map(fn ($kategori) => [
                'nama' => $kategori->nama,
                'ikon' => $kategori->ikon ?? '🌱',
            ])
            ->all();

        $kabupatenList = [
            'Badung', 'Bangli', 'Buleleng', 'Denpasar',
            'Gianyar', 'Jembrana', 'Karangasem',
            'Klungkung', 'Tabanan',
        ];

        return view('pengusaha.katalog', compact(
            'produkList',
            'kategoriList',
            'kabupatenList'
        ));
    }

    public function show($slug)
    {
        $data = Produk::with([
            'petani.profilPetani',
            'kategori',
            'fotoUtama',
        ])
        ->where('slug', $slug)
        ->where('aktif', true)
        ->firstOrFail();

        $produk = [
            'nama' => $data->nama,
            'gambar' => $data->fotoUtama?->path
                ? asset($data->fotoUtama->path)
                : asset('images/no-image.png'),
            'kategori' => $data->kategori?->nama ?? 'Lainnya',
            'harga' => $data->harga_per_kg,
            'stok' => $data->stok_kg,
            'minimal' => $data->min_order_kg,
            'petani' => $data->petani?->profilPetani?->nama_usaha_tani
                ?? $data->petani?->name
                ?? 'Petani',
            'rating' => 0,
            'kabupaten' => $data->petani?->profilPetani?->kabupaten ?? '-',
            'deskripsi' => $data->deskripsi,
        ];

        return view('pengusaha.detail-produk', [
            'produk' => $produk,
            'slug' => $data->slug,
        ]);
    }
}
