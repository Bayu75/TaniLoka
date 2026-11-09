
@extends('layouts.pengusaha')

@section('title', 'Toko Petani - TaniLoka')

@section('content')

@php
    // DATA PRODUK
    $produkList = [
        'bayam-hijau-organik' => [
            'nama' => 'Bayam Hijau Organik',
            'gambar' => 'https://images.unsplash.com/photo-1768734837803-54eb8c83e080?w=800',
            'kategori' => 'Sayuran',
            'harga' => 8500,
            'stok' => 120,
            'minimal' => 5,
            'petani' => 'Pak Made Sukartha',
            'kabupaten' => 'Tabanan',
            'rating' => 4.8,
            'deskripsi' => 'Bayam hijau segar hasil pertanian lokal Bali.',
        ],
        'cabai-rawit-merah' => [
            'nama' => 'Cabai Rawit Merah',
            'gambar' => 'https://images.unsplash.com/photo-1768734837679-9b3180433384?w=800',
            'kategori' => 'Bumbu & Rempah',
            'harga' => 45000,
            'stok' => 85,
            'minimal' => 2,
            'petani' => 'Ibu Wayan Sari',
            'kabupaten' => 'Bangli',
            'rating' => 4.9,
            'deskripsi' => 'Cabai rawit merah segar dengan rasa pedas alami.',
        ],
        'tomat-segar-grade-a' => [
            'nama' => 'Tomat Segar Grade A',
            'gambar' => 'https://images.unsplash.com/photo-1768734837464-c5045c6a98c4?w=800',
            'kategori' => 'Sayuran',
            'harga' => 12000,
            'stok' => 300,
            'minimal' => 10,
            'petani' => 'Pak Ketut Wirasa',
            'kabupaten' => 'Tabanan',
            'rating' => 4.7,
            'deskripsi' => 'Tomat segar Grade A dengan kualitas pilihan.',
        ],
        'kangkung-segar' => [
            'nama' => 'Kangkung Segar',
            'gambar' => 'https://images.unsplash.com/photo-1768734836079-040ec2543a65?w=800',
            'kategori' => 'Sayuran',
            'harga' => 5500,
            'stok' => 200,
            'minimal' => 10,
            'petani' => 'Ibu Nyoman Dewi',
            'kabupaten' => 'Gianyar',
            'rating' => 4.6,
            'deskripsi' => 'Kangkung segar hasil panen petani lokal.',
        ],
        'bawang-putih-lokal' => [
            'nama' => 'Bawang Putih Lokal',
            'gambar' => 'https://images.unsplash.com/photo-1765862857099-cb03e1c3de07?w=800',
            'kategori' => 'Bumbu & Rempah',
            'harga' => 35000,
            'stok' => 150,
            'minimal' => 5,
            'petani' => 'Pak Gede Setiawan',
            'kabupaten' => 'Karangasem',
            'rating' => 4.5,
            'deskripsi' => 'Bawang putih lokal segar dari petani Karangasem.',
        ],
        'kembang-kol-putih' => [
            'nama' => 'Kembang Kol Putih',
            'gambar' => 'https://images.unsplash.com/photo-1770782912836-82b4d67444d5?w=800',
            'kategori' => 'Sayuran',
            'harga' => 15000,
            'stok' => 60,
            'minimal' => 5,
            'petani' => 'Pak Ketut Wirasa',
            'kabupaten' => 'Tabanan',
            'rating' => 4.7,
            'deskripsi' => 'Kembang kol putih segar dengan kualitas pilihan.',
        ],
        'wortel-organik' => [
            'nama' => 'Wortel Organik',
            'gambar' => 'https://images.unsplash.com/photo-1768119901638-75c6edd64c0b?w=800',
            'kategori' => 'Umbi-umbian',
            'harga' => 14000,
            'stok' => 90,
            'minimal' => 5,
            'petani' => 'Pak Made Sukartha',
            'kabupaten' => 'Tabanan',
            'rating' => 4.8,
            'deskripsi' => 'Wortel organik segar dengan kualitas pilihan.',
        ],
        'paprika-merah' => [
            'nama' => 'Paprika Merah',
            'gambar' => 'https://images.unsplash.com/photo-1777791374501-ff28ce93d2cb?w=800',
            'kategori' => 'Sayuran',
            'harga' => 55000,
            'stok' => 0,
            'minimal' => 3,
            'petani' => 'Ibu Wayan Sari',
            'kabupaten' => 'Bangli',
            'rating' => 4.9,
            'deskripsi' => 'Paprika merah segar dengan warna menarik.',
        ],
    ];

    // DATA PROFIL PETANI
    $petaniList = [
        'pak-made-sukartha' => [
            'nama' => 'Pak Made Sukartha',
            'kabupaten' => 'Tabanan',
            'rating' => 4.8,
            'deskripsi' => 'Menyediakan hasil pertanian lokal yang segar dan berkualitas. Berkomitmen menghadirkan produk pilihan langsung dari petani untuk mendukung kebutuhan usaha.',
            'inisial' => 'M',
        ],
        'ibu-wayan-sari' => [
            'nama' => 'Ibu Wayan Sari',
            'kabupaten' => 'Bangli',
            'rating' => 4.9,
            'deskripsi' => 'Menyediakan berbagai hasil pertanian lokal, khususnya sayuran dan bumbu segar dengan kualitas pilihan.',
            'inisial' => 'W',
        ],
        'pak-ketut-wirasa' => [
            'nama' => 'Pak Ketut Wirasa',
            'kabupaten' => 'Tabanan',
            'rating' => 4.7,
            'deskripsi' => 'Menghadirkan hasil panen segar dari pertanian lokal untuk memenuhi kebutuhan pengusaha kuliner.',
            'inisial' => 'K',
        ],
        'ibu-nyoman-dewi' => [
            'nama' => 'Ibu Nyoman Dewi',
            'kabupaten' => 'Gianyar',
            'rating' => 4.6,
            'deskripsi' => 'Menyediakan hasil panen sayuran segar dari petani lokal dengan kualitas yang terjaga.',
            'inisial' => 'N',
        ],

        'pak-gede-setiawan' => [
        'nama' => 'Pak Gede Setiawan',
        'kabupaten' => 'Karangasem',
        'rating' => 4.5,
        'deskripsi' => 'Menyediakan bawang putih lokal segar dari hasil pertanian Karangasem.',
        'inisial' => 'G',
        ],
    ];

    // MEMERIKSA TOKO
    abort_unless(isset($petaniList[$petani]), 404);

    $toko = $petaniList[$petani];

    // MENGAMBIL PRODUK MILIK PETANI
    $produkToko = collect($produkList)->filter(function ($item) use ($toko) {
        return $item['petani'] === $toko['nama'];
    });
@endphp

<div class="mx-auto max-w-6xl">

    {{-- KEMBALI --}}
    <a href="{{ route('pengusaha.katalog') }}"
       class="mb-5 inline-flex items-center gap-2 text-sm font-semibold text-[#2E7D32] hover:underline">
        ← Kembali ke Katalog
    </a>

    {{-- PROFIL TOKO --}}
    <div class="overflow-hidden rounded-2xl border border-[#D4E8D0] bg-white">

        <div class="h-32 bg-gradient-to-r from-[#1E5F3E] to-[#66BB6A]"></div>

        <div class="px-5 pb-6 md:px-7">

            <div class="mt-5 flex flex-wrap items-center gap-4">

                <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl border-4 border-white bg-[#2E7D32] text-3xl font-extrabold text-white shadow-md">
                {{ $toko['inisial'] }}
                </div>

                <div class="flex-1 pb-1">
                    <h1 class="text-2xl font-extrabold text-[#1A2A1A]">
                        {{ $toko['nama'] }}
                    </h1>

                    <p class="mt-1 text-sm text-[#5A7A5A]">
                        📍 {{ $toko['kabupaten'] }}, Bali
                    </p>
                </div>

                <div class="rounded-xl bg-[#EBF5EB] px-4 py-2">
                    <p class="text-xs text-[#5A7A5A]">Rating Toko</p>
                    <p class="mt-1 font-extrabold text-[#EAB308]">
                        ★★★★★
                        <span class="text-sm text-[#1A2A1A]">{{ $toko['rating'] }}</span>
                    </p>
                </div>

            </div>

            <div class="mt-6 border-t border-[#E5EEE5] pt-5">
                <h2 class="font-bold text-[#1A2A1A]">Tentang Toko</h2>

                <p class="mt-2 max-w-3xl text-sm leading-relaxed text-[#5A7A5A]">
                    {{ $toko['deskripsi'] }}
                </p>
            </div>

            <div class="mt-5 flex flex-wrap gap-3">

                <div class="rounded-xl bg-[#F1F7F1] px-4 py-3">
                    <p class="text-xs text-[#789078]">Jumlah Produk</p>
                    <p class="mt-1 font-bold text-[#2E7D32]">
                        {{ $produkToko->count() }} Produk
                    </p>
                </div>

                <div class="rounded-xl bg-[#F1F7F1] px-4 py-3">
                    <p class="text-xs text-[#789078]">Lokasi Toko</p>
                    <p class="mt-1 font-bold text-[#2E7D32]">
                        {{ $toko['kabupaten'] }}, Bali
                    </p>
                </div>

            </div>
        </div>
    </div>

    {{-- DAFTAR PRODUK --}}
    <div class="mt-8">

        <div class="mb-5">
            <h2 class="text-xl font-extrabold text-[#1A2A1A]">
                Produk dari {{ $toko['nama'] }}
            </h2>

            <p class="mt-1 text-sm text-[#789078]">
                Temukan hasil pertanian yang tersedia di toko ini.
            </p>
        </div>

        @if ($produkToko->isNotEmpty())

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">

                @foreach ($produkToko as $slugProduk => $produk)

                    <div class="overflow-hidden rounded-2xl border border-[#D4E8D0] bg-white transition hover:-translate-y-1 hover:shadow-lg">

                        <div class="relative aspect-[4/3] overflow-hidden bg-[#EBF5EB]">

                            <img
                                src="{{ $produk['gambar'] }}"
                                alt="{{ $produk['nama'] }}"
                                class="h-full w-full object-cover"
                            >

                            <span class="absolute left-3 top-3 rounded-full bg-white/95 px-3 py-1 text-xs font-bold text-[#2E7D32]">
                                {{ $produk['kategori'] }}
                            </span>

                            @if ($produk['stok'] <= 0)
                                <span class="absolute right-3 top-3 rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-600">
                                    Stok Habis
                                </span>
                            @endif

                        </div>

                        <div class="p-4">

                            <h3 class="font-bold text-[#1A2A1A]">
                                {{ $produk['nama'] }}
                            </h3>

                            <p class="mt-2 text-lg font-extrabold text-[#2E7D32]">
                                Rp{{ number_format($produk['harga'], 0, ',', '.') }}
                                <span class="text-xs font-normal text-[#789078]">/kg</span>
                            </p>

                            <div class="mt-3 flex justify-between text-xs text-[#789078]">
                                <span>Minimal {{ $produk['minimal'] }} kg</span>
                                <span>Stok: {{ $produk['stok'] }} kg</span>
                            </div>

                            <a href="{{ route('pengusaha.produk.detail', ['slug' => $slugProduk]) }}"
                               class="mt-4 flex w-full items-center justify-center rounded-xl bg-[#2E7D32] px-4 py-3 text-sm font-bold text-white transition hover:bg-[#1E5F3E]">
                                Lihat Detail Produk →
                            </a>

                        </div>
                    </div>

                @endforeach

            </div>

        @else

            <div class="rounded-2xl border border-dashed border-[#D4E8D0] bg-white p-10 text-center">
                <p class="font-bold text-[#5A7A5A]">
                    Belum ada produk tersedia.
                </p>
            </div>

        @endif

    </div>

</div>

@endsection
