
@extends('layouts.pengusaha')

@section('title', 'Detail Produk - TaniLoka')

@section('content')

@php
    $produkList = [
        'bayam-hijau-organik' => [
            'nama' => 'Bayam Hijau Organik',
            'gambar' => 'https://images.unsplash.com/photo-1768734837803-54eb8c83e080?w=800',
            'kategori' => 'Sayuran',
            'harga' => 8500,
            'stok' => 120,
            'minimal' => 5,
            'petani' => 'Pak Made Sukartha',
            'rating' => 4.8,
            'kabupaten' => 'Tabanan',
            'deskripsi' => 'Bayam hijau segar hasil pertanian lokal Bali, ditanam secara organik dan dipanen langsung dari kebun petani.',
        ],

        'cabai-rawit-merah' => [
            'nama' => 'Cabai Rawit Merah',
            'gambar' => 'https://images.unsplash.com/photo-1768734837679-9b3180433384?w=800',
            'kategori' => 'Bumbu & Rempah',
            'harga' => 45000,
            'stok' => 85,
            'minimal' => 2,
            'petani' => 'Ibu Wayan Sari',
            'rating' => 4.9,
            'kabupaten' => 'Bangli',
            'deskripsi' => 'Cabai rawit merah segar dengan warna cerah dan rasa pedas alami, cocok untuk kebutuhan dapur usaha kuliner.',
        ],

        'tomat-segar-grade-a' => [
            'nama' => 'Tomat Segar Grade A',
            'gambar' => 'https://images.unsplash.com/photo-1768734837464-c5045c6a98c4?w=800',
            'kategori' => 'Sayuran',
            'harga' => 12000,
            'stok' => 300,
            'minimal' => 10,
            'petani' => 'Pak Ketut Wirasa',
            'rating' => 4.7,
            'kabupaten' => 'Tabanan',
            'deskripsi' => 'Tomat segar Grade A dengan kualitas pilihan, cocok untuk kebutuhan usaha kuliner.',
        ],

    'kangkung-segar' => [
            'nama' => 'Kangkung Segar',
            'gambar' => 'https://images.unsplash.com/photo-1768734836079-040ec2543a65?w=800',
            'kategori' => 'Sayuran',
            'harga' => 5500,
            'stok' => 200,
            'minimal' => 10,
            'petani' => 'Ibu Nyoman Dewi',
            'rating' => 4.6,
            'kabupaten' => 'Gianyar',
            'deskripsi' => 'Kangkung segar hasil panen petani lokal, cocok untuk berbagai hidangan sayuran.',
        ],

    'bawang-putih-lokal' => [
        'nama' => 'Bawang Putih Lokal',
        'gambar' => 'https://images.unsplash.com/photo-1765862857099-cb03e1c3de07?w=800',
        'kategori' => 'Bumbu & Rempah',
        'harga' => 35000,
        'stok' => 150,
        'minimal' => 5,
        'petani' => 'Pak Gede Setiawan',
        'rating' => 4.5,
        'kabupaten' => 'Karangasem',
        'deskripsi' => 'Bawang putih lokal segar dari petani Karangasem, cocok untuk berbagai kebutuhan masakan.',
        ],

    'kembang-kol-putih' => [
        'nama' => 'Kembang Kol Putih',
        'gambar' => 'https://images.unsplash.com/photo-1770782912836-82b4d67444d5?w=800',
        'kategori' => 'Sayuran',
        'harga' => 15000,
        'stok' => 60,
        'minimal' => 5,
        'petani' => 'Pak Ketut Wirasa',
        'rating' => 4.7,
        'kabupaten' => 'Tabanan',
        'deskripsi' => 'Kembang kol putih segar dengan kualitas pilihan dari petani lokal.',
        ],

    'wortel-organik' => [
        'nama' => 'Wortel Organik',
        'gambar' => 'https://images.unsplash.com/photo-1768119901638-75c6edd64c0b?w=800',
        'kategori' => 'Umbi-umbian',
        'harga' => 14000,
        'stok' => 90,
        'minimal' => 5,
        'petani' => 'Pak Made Sukartha',
        'rating' => 4.8,
        'kabupaten' => 'Tabanan',
        'deskripsi' => 'Wortel organik segar dengan warna cerah dan kualitas pilihan.',
         ],

    'paprika-merah' => [
        'nama' => 'Paprika Merah',
        'gambar' => 'https://images.unsplash.com/photo-1777791374501-ff28ce93d2cb?w=800',
        'kategori' => 'Sayuran',
        'harga' => 55000,
        'stok' => 0,
        'minimal' => 3,
        'petani' => 'Ibu Wayan Sari',
        'rating' => 4.9,
        'kabupaten' => 'Bangli',
        'deskripsi' => 'Paprika merah segar dengan warna menarik, cocok untuk aneka hidangan.',
        ],
    ];

    abort_unless(isset($produkList[$slug]), 404);

    $produk = $produkList[$slug];
@endphp

<div class="mb-6">
    <a href="{{ route('pengusaha.katalog') }}"
       class="font-semibold text-[#2E7D32] hover:underline">
        ← Kembali ke Katalog
    </a>
</div>

<div class="grid gap-8 md:grid-cols-2">

    {{-- FOTO PRODUK --}}
    <div class="overflow-hidden rounded-3xl bg-[#EBF5EB]">
        <img
            src="{{ $produk['gambar'] }}"
            alt="{{ $produk['nama'] }}"
            class="h-80 w-full object-cover md:h-[450px]"
        >
    </div>

    {{-- INFORMASI PRODUK --}}
    <div class="flex flex-col gap-5">

        <div>
            <span class="rounded-full bg-[#EBF5EB] px-3 py-1 text-sm font-semibold text-[#1E5F3E]">
                {{ $produk['kategori'] }}
            </span>

            <h1 class="mt-4 text-3xl font-extrabold text-[#1A2A1A]">
                {{ $produk['nama'] }}
            </h1>

            <div class="mt-3 text-3xl font-extrabold text-[#2E7D32]">
                Rp{{ number_format($produk['harga'], 0, ',', '.') }}
                <span class="text-base font-normal text-[#5A7A5A]">/kg</span>
            </div>
        </div>

        <div class="rounded-2xl border border-[#D4E8D0] bg-white p-4">
            <div class="flex justify-between py-2">
                <span class="text-[#5A7A5A]">Stok tersedia</span>
                <strong>{{ $produk['stok'] }} kg</strong>
            </div>

            <div class="flex justify-between border-t border-[#F0F4EF] py-2">
                <span class="text-[#5A7A5A]">Minimal pemesanan</span>
                <strong>{{ $produk['minimal'] }} kg</strong>
            </div>

            <div class="flex justify-between border-t border-[#F0F4EF] py-2">
                <span class="text-[#5A7A5A]">Rating</span>
                <strong class="text-[#F4B400]">★ {{ $produk['rating'] }}</strong>
            </div>
        </div>

        <div>
            <h2 class="mb-2 font-bold text-[#1A2A1A]">
                Informasi Petani
            </h2>

            <div class="rounded-2xl bg-[#EBF5EB] p-4">
                <p class="font-bold text-[#1E5F3E]">
                    {{ $produk['petani'] }}
                </p>
                <p class="text-sm text-[#5A7A5A]">
                    📍 {{ $produk['kabupaten'] }}, Bali
                </p>
            </div>
        </div>

        <div>
            <h2 class="mb-2 font-bold text-[#1A2A1A]">
                Deskripsi Produk
            </h2>
            <p class="leading-relaxed text-[#5A7A5A]">
                {{ $produk['deskripsi'] }}
            </p>
        </div>

{{-- INPUT JUMLAH --}}
@if ($produk['stok'] > 0)
    <form action="{{ route('pengusaha.keranjang.tambah') }}" method="POST"
          class="rounded-2xl border border-[#D4E8D0] bg-white p-4">

        @csrf

        <input type="hidden" name="slug" value="{{ $slug }}">

        <label for="jumlah" class="mb-3 block font-bold">
            Jumlah Pembelian (kg)
        </label>

        <input
            type="number"
            id="jumlah"
            name="jumlah"
            min="{{ $produk['minimal'] }}"
            max="{{ $produk['stok'] }}"
            value="{{ $produk['minimal'] }}"
            class="min-h-12 w-full rounded-xl border-2 border-[#D4E8D0] px-4 outline-none focus:border-[#2E7D32]"
            required
        >

        <p class="mt-2 text-xs text-[#5A7A5A]">
            Minimal {{ $produk['minimal'] }} kg.
        </p>

        <button
            type="submit"
            class="mt-4 min-h-12 w-full rounded-xl bg-[#2E7D32] font-bold text-white hover:bg-[#1E5F3E]"
        >
            Tambahkan ke Keranjang
        </button>
    </form>
@else
    <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
        <p class="font-bold text-red-600">
            Stok Habis
        </p>
        <p class="mt-1 text-sm text-red-500">
            Maaf, produk ini sedang tidak tersedia.
        </p>
        <button
            type="button"
            disabled
            class="mt-4 min-h-12 w-full cursor-not-allowed rounded-xl bg-gray-300 font-bold text-gray-600"
        >
            Stok Habis
        </button>
    </div>
@endif
    </div>
</div>

@endsection
       
