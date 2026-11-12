
@extends('layouts.pengusaha')

@section('title', 'Beranda - TaniLoka')

@section('content')

@php
    $kategori = [
        ['nama' => 'Sayuran', 'ikon' => '🥬'],
        ['nama' => 'Bumbu & Rempah', 'ikon' => '🌶️'],
        ['nama' => 'Umbi-umbian', 'ikon' => '🥕'],
        ['nama' => 'Buah-buahan', 'ikon' => '🍅'],
        ['nama' => 'Serealia', 'ikon' => '🌾'],
        ['nama' => 'Jamur', 'ikon' => '🍄'],
    ];

    $produkUnggulan = [
        [
            'nama' => 'Bayam Hijau Organik',
            'gambar' => 'https://images.unsplash.com/photo-1768734837803-54eb8c83e080?w=400&h=300&fit=crop&auto=format',
            'kategori' => 'Sayuran',
            'harga' => 8500,
            'minimal' => 5,
            'petani' => 'Pak Made Sukartha',
            'rating' => 4.8,
            'kabupaten' => 'Tabanan',
            'badge' => 'Organik',
            'nego' => true,
            'tersedia' => true,
        ],
        [
            'nama' => 'Cabai Rawit Merah',
            'gambar' => 'https://images.unsplash.com/photo-1768734837679-9b3180433384?w=400&h=300&fit=crop&auto=format',
            'kategori' => 'Bumbu & Rempah',
            'harga' => 45000,
            'minimal' => 2,
            'petani' => 'Ibu Wayan Sari',
            'rating' => 4.9,
            'kabupaten' => 'Bangli',
            'badge' => null,
            'nego' => false,
            'tersedia' => true,
        ],
        [
            'nama' => 'Tomat Segar Grade A',
            'gambar' => 'https://images.unsplash.com/photo-1768734837464-c5045c6a98c4?w=400&h=300&fit=crop&auto=format',
            'kategori' => 'Sayuran',
            'harga' => 12000,
            'minimal' => 10,
            'petani' => 'Pak Ketut Wirasa',
            'rating' => 4.7,
            'kabupaten' => 'Tabanan',
            'badge' => 'Grade A',
            'nego' => true,
            'tersedia' => true,
        ],
        [
            'nama' => 'Kangkung Segar',
            'gambar' => 'https://images.unsplash.com/photo-1768734836079-040ec2543a65?w=400&h=300&fit=crop&auto=format',
            'kategori' => 'Sayuran',
            'harga' => 5500,
            'minimal' => 10,
            'petani' => 'Ibu Nyoman Dewi',
            'rating' => 4.6,
            'kabupaten' => 'Gianyar',
            'badge' => null,
            'nego' => true,
            'tersedia' => true,
        ],
    ];

    $produkNego = array_values(array_filter(
        $produkUnggulan,
        fn ($produk) => $produk['nego']
    ));
@endphp

<div class="pb-10">

    {{-- BAGIAN SAMBUTAN DAN PENCARIAN --}}
   <section class="-mx-4 -mt-4 bg-gradient-to-r from-[#1E5F3E] to-[#2E7D32] px-4 pb-16 pt-6 md:-mx-6 md:px-6 lg:relative lg:left-1/2 lg:right-1/2 lg:ml-[-50vw] lg:mr-[-50vw] lg:w-screen">
        <div class="mx-auto max-w-6xl py-5 md:py-6">

            <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <p class="text-base font-medium text-white/80">
                        Selamat datang
                    </p>

                    <h1 class="truncate text-xl font-bold text-white">
                        {{ auth()->user()->name ?? 'Pengusaha' }}
                    </h1>
                </div>

                <div class="flex shrink-0 gap-2">
                    <a href="{{ route('pengusaha.pesan') }}"
                       class="flex min-h-12 items-center gap-2 rounded-xl bg-white/15 px-3 font-semibold text-white hover:bg-white/25">
                        <span>💬</span>
                        Pesan
                    </a>

                    <a href="{{ route('pengusaha.pesanan') }}"
                       class="flex min-h-12 items-center gap-2 rounded-xl bg-white/15 px-3 font-semibold text-white hover:bg-white/25">
                        <span>🔔</span>
                        Pemberitahuan
                    </a>
                </div>
            </div>

            <form action="{{ route('pengusaha.katalog') }}" method="GET">
                <div class="flex gap-2">
                    <input
                        type="text"
                        name="q"
                        placeholder="Cari sayuran, bumbu, buah..."
                        class="min-h-12 min-w-0 flex-1 rounded-xl bg-white px-4 text-base text-[#1A2A1A] outline-none focus:ring-2 focus:ring-[#F4B400]"
                    >

                    <button type="submit"
                            class="min-h-12 rounded-xl bg-[#F4B400] px-5 font-bold text-[#1A2A00] hover:bg-[#E6A800]">
                        Cari
                    </button>
                </div>
            </form>

        </div>
    </section>


    <div class="mx-auto -mt-8 max-w-6xl">

        {{-- BANNER PROMO --}}
        <section class="relative mb-6 overflow-hidden rounded-2xl bg-gradient-to-r from-[#1E5F3E] to-[#2E7D32] p-5 shadow-lg">
            <div class="absolute inset-y-0 right-0 w-1/2">
                <img
                    src="https://images.unsplash.com/photo-1768734837803-54eb8c83e080?w=300&h=150&fit=crop&auto=format"
                    alt=""
                    class="h-full w-full object-cover opacity-30"
                >
            </div>

            <div class="relative">
                <p class="mb-1 font-semibold text-[#F4B400]">
                    Promo Musim Panen
                </p>

                <h2 class="mb-3 text-xl font-extrabold leading-tight text-white">
                    Hemat lewat negosiasi<br>
                    langsung dengan petani
                </h2>

                <a href="{{ route('pengusaha.katalog') }}"
                   class="inline-flex min-h-12 items-center rounded-xl bg-[#F4B400] px-5 font-bold text-[#1A2A00] hover:bg-[#E6A800]">
                    Belanja Sekarang
                </a>
            </div>
        </section>


        {{-- KATEGORI --}}
        <section class="mb-6">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-bold text-[#1A2A1A]">
                    Kategori
                </h2>

                <a href="{{ route('pengusaha.katalog') }}"
                   class="px-2 py-2 font-semibold text-[#2E7D32] hover:underline">
                    Lihat Semua
                </a>
            </div>

            <div class="grid grid-cols-3 gap-3 md:grid-cols-6">
                @foreach ($kategori as $item)
                    <a href="{{ route('pengusaha.katalog', ['cat' => $item['nama']]) }}"
                       class="flex min-h-24 flex-col items-center justify-center gap-2 rounded-2xl border border-[#D4E8D0] bg-white p-3 transition hover:border-[#66BB6A] hover:bg-[#EBF5EB]">

                        <span class="text-2xl">
                            {{ $item['ikon'] }}
                        </span>

                        <span class="text-center text-sm font-semibold text-[#1A2A1A]">
                            {{ $item['nama'] }}
                        </span>
                    </a>
                @endforeach
            </div>
        </section>


        {{-- PRODUK UNGGULAN --}}
        <section class="mb-6">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-bold text-[#1A2A1A]">
                    Produk Unggulan
                </h2>

                <a href="{{ route('pengusaha.katalog') }}"
                   class="px-2 py-2 font-semibold text-[#2E7D32] hover:underline">
                    Lihat Semua
                </a>
            </div>

            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                @foreach ($produkUnggulan as $produk)
                    <x-pengusaha.product-card
                        :nama="$produk['nama']"
                        :gambar="$produk['gambar']"
                        :kategori="$produk['kategori']"
                        :harga="$produk['harga']"
                        :minimal-pesan="$produk['minimal']"
                        :petani="$produk['petani']"
                        :rating="$produk['rating']"
                        :kabupaten="$produk['kabupaten']"
                        :badge="$produk['badge']"
                        :bisa-nego="$produk['nego']"
                        :tersedia="$produk['tersedia']"
                        :detail-url="route('pengusaha.produk.detail', ['slug' => \Illuminate\Support\Str::slug($produk['nama'])])"
                    />
                @endforeach
            </div>
        </section>


        {{-- PRODUK BISA NEGO --}}
        <section class="mb-6">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-bold text-[#1A2A1A]">
                    Bisa Nego — Ajukan Penawaran
                </h2>

                <a href="{{ route('pengusaha.katalog') }}"
                   class="px-2 py-2 font-semibold text-[#2E7D32] hover:underline">
                    Lihat Semua
                </a>
            </div>

            <div class="-mx-1 flex gap-4 overflow-x-auto px-1 pb-2">
                @foreach ($produkNego as $produk)
                  <a href="{{ route('pengusaha.produk.detail', ['slug' => \Illuminate\Support\Str::slug($produk['nama'])]) }}"
                    class="w-60 shrink-0 overflow-hidden rounded-2xl border border-[#D4E8D0] bg-white transition hover:shadow-md">

                        <div class="relative h-32 bg-[#EBF5EB]">
                            <img
                                src="{{ $produk['gambar'] }}"
                                alt="{{ $produk['nama'] }}"
                                class="h-full w-full object-cover"
                            >

                            <span class="absolute left-2 top-2 rounded-full bg-[#F4B400] px-2 py-0.5 text-xs font-bold text-[#1A2A00]">
                                Bisa Nego
                            </span>
                        </div>

                        <div class="p-3">
                            <h3 class="mb-1 font-semibold text-[#1A2A1A]">
                                {{ $produk['nama'] }}
                            </h3>

                            <p class="font-bold text-[#2E7D32]">
                                Rp{{ number_format($produk['harga'], 0, ',', '.') }}/kg
                            </p>

                            <p class="mt-1 text-sm text-[#5A7A5A]">
                                {{ $produk['petani'] }} · {{ $produk['kabupaten'] }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>


        {{-- TIPS NEGOSIASI --}}
        <section class="mb-4 flex items-center gap-4 rounded-2xl bg-[#EBF5EB] p-5">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#2E7D32] text-xl text-white">
                💡
            </div>

            <div>
                <h2 class="mb-1 font-bold text-[#1A2A1A]">
                    Tips: Cara Negosiasi Harga
                </h2>

                <p class="text-sm leading-relaxed text-[#5A7A5A]">
                    Ajukan penawaran wajar berdasarkan jumlah pembelian.
                    Petani lebih senang dengan pembeli yang berkomitmen
                    jangka panjang.
                </p>
            </div>
        </section>

    </div>
</div>

@endsection
