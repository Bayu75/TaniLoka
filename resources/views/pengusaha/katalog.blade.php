
@extends('layouts.pengusaha')

@section('title', 'Katalog Produk - TaniLoka')

@section('content')

@php
    // Data dummy mengikuti data.ts dari ZIP
    $produkList = [
        [
            'nama' => 'Bayam Hijau Organik',
            'gambar' => 'https://images.unsplash.com/photo-1768734837803-54eb8c83e080?w=400&h=300&fit=crop&auto=format',
            'kategori' => 'Sayuran',
            'harga' => 8500,
            'minimal' => 5,
            'stok' => 120,
            'petani' => 'Pak Made Sukartha',
            'rating' => 4.8,
            'kabupaten' => 'Tabanan',
            'badge' => 'Organik',
            'nego' => true,
        ],
        [
            'nama' => 'Cabai Rawit Merah',
            'gambar' => 'https://images.unsplash.com/photo-1768734837679-9b3180433384?w=400&h=300&fit=crop&auto=format',
            'kategori' => 'Bumbu & Rempah',
            'harga' => 45000,
            'minimal' => 2,
            'stok' => 85,
            'petani' => 'Ibu Wayan Sari',
            'rating' => 4.9,
            'kabupaten' => 'Bangli',
            'badge' => null,
            'nego' => false,
        ],
        [
            'nama' => 'Tomat Segar Grade A',
            'gambar' => 'https://images.unsplash.com/photo-1768734837464-c5045c6a98c4?w=400&h=300&fit=crop&auto=format',
            'kategori' => 'Sayuran',
            'harga' => 12000,
            'minimal' => 10,
            'stok' => 300,
            'petani' => 'Pak Ketut Wirasa',
            'rating' => 4.7,
            'kabupaten' => 'Tabanan',
            'badge' => 'Grade A',
            'nego' => true,
        ],
        [
            'nama' => 'Kangkung Segar',
            'gambar' => 'https://images.unsplash.com/photo-1768734836079-040ec2543a65?w=400&h=300&fit=crop&auto=format',
            'kategori' => 'Sayuran',
            'harga' => 5500,
            'minimal' => 10,
            'stok' => 200,
            'petani' => 'Ibu Nyoman Dewi',
            'rating' => 4.6,
            'kabupaten' => 'Gianyar',
            'badge' => null,
            'nego' => true,
        ],
        [
            'nama' => 'Bawang Putih Lokal',
            'gambar' => 'https://images.unsplash.com/photo-1765862857099-cb03e1c3de07?w=400&h=300&fit=crop&auto=format',
            'kategori' => 'Bumbu & Rempah',
            'harga' => 35000,
            'minimal' => 5,
            'stok' => 150,
            'petani' => 'Pak Gede Setiawan',
            'rating' => 4.5,
            'kabupaten' => 'Karangasem',
            'badge' => null,
            'nego' => true,
        ],
        [
            'nama' => 'Kembang Kol Putih',
            'gambar' => 'https://images.unsplash.com/photo-1770782912836-82b4d67444d5?w=400&h=300&fit=crop&auto=format',
            'kategori' => 'Sayuran',
            'harga' => 15000,
            'minimal' => 5,
            'stok' => 60,
            'petani' => 'Pak Ketut Wirasa',
            'rating' => 4.7,
            'kabupaten' => 'Tabanan',
            'badge' => null,
            'nego' => false,
        ],
        [
            'nama' => 'Wortel Organik',
            'gambar' => 'https://images.unsplash.com/photo-1768119901638-75c6edd64c0b?w=400&h=300&fit=crop&auto=format',
            'kategori' => 'Umbi-umbian',
            'harga' => 14000,
            'minimal' => 5,
            'stok' => 90,
            'petani' => 'Pak Made Sukartha',
            'rating' => 4.8,
            'kabupaten' => 'Tabanan',
            'badge' => 'Organik',
            'nego' => true,
        ],
        [
            'nama' => 'Paprika Merah',
            'gambar' => 'https://images.unsplash.com/photo-1777791374501-ff28ce93d2cb?w=400&h=300&fit=crop&auto=format',
            'kategori' => 'Sayuran',
            'harga' => 55000,
            'minimal' => 3,
            'stok' => 0,
            'petani' => 'Ibu Wayan Sari',
            'rating' => 4.9,
            'kabupaten' => 'Bangli',
            'badge' => null,
            'nego' => true,
        ],
    ];

    $kategoriList = [
        ['nama' => 'Sayuran', 'ikon' => '🥬'],
        ['nama' => 'Bumbu & Rempah', 'ikon' => '🌶️'],
        ['nama' => 'Umbi-umbian', 'ikon' => '🥕'],
        ['nama' => 'Buah-buahan', 'ikon' => '🍅'],
        ['nama' => 'Serealia', 'ikon' => '🌾'],
        ['nama' => 'Jamur', 'ikon' => '🍄'],
    ];

    $kabupatenList = [
        'Badung', 'Bangli', 'Buleleng', 'Denpasar',
        'Gianyar', 'Jembrana', 'Karangasem',
        'Klungkung', 'Tabanan'
    ];

    // Mengambil parameter pencarian dan filter
    $q = trim(request('q', ''));
    $cat = request('cat', '');
    $district = request('district', '');
    $minPrice = (int) request('min_price', 0);
    $maxPrice = (int) request('max_price', 100000);
    $negoOnly = request('nego') == '1';
    $sort = request('sort', 'newest');

    $filtered = array_filter($produkList, function ($p) use (
        $q, $cat, $district, $minPrice, $maxPrice, $negoOnly
    ) {
        $keywordMatch = $q === '' ||
            stripos($p['nama'], $q) !== false ||
            stripos($p['petani'], $q) !== false ||
            stripos($p['kabupaten'], $q) !== false ||
            stripos($p['kategori'], $q) !== false;

        return $keywordMatch
            && ($cat === '' || $p['kategori'] === $cat)
            && ($district === '' || $p['kabupaten'] === $district)
            && $p['harga'] >= $minPrice
            && $p['harga'] <= $maxPrice
            && (!$negoOnly || $p['nego']);
    });

    // Produk tersedia ditampilkan terlebih dahulu
    usort($filtered, function ($a, $b) use ($sort) {
        if (($a['stok'] > 0) !== ($b['stok'] > 0)) {
            return $b['stok'] <=> $a['stok'];
        }

        return match ($sort) {
            'price-asc' => $a['harga'] <=> $b['harga'],
            'price-desc' => $b['harga'] <=> $a['harga'],
            'rating' => $b['rating'] <=> $a['rating'],
            default => 0,
        };
    });

    $activeCount = (int) ($cat !== '')
        + (int) ($district !== '')
        + (int) $negoOnly
        + (int) ($minPrice > 0 || $maxPrice < 100000)
        + (int) ($sort !== 'newest');
@endphp

<div class="pb-10" x-data="{ filterOpen: false }">
{{-- HEADER PUTIH KATALOG --}}
<div class="relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen border-b border-[#D4E8D0] bg-white">
    <div class="mx-auto max-w-6xl px-4 py-5 md:py-6">

    {{-- HEADER KATALOG --}}
    <section class="mb-5">
        <div class="mb-4">
            <h1 class="text-2xl font-extrabold text-[#1A2A1A]">
                Katalog Produk
            </h1>
            <p class="mt-1 text-sm text-[#5A7A5A]">
                Temukan hasil pertanian segar langsung dari petani Bali.
            </p>
        </div>

        {{-- SEARCH BAR --}}
<form action="{{ route('pengusaha.katalog') }}" method="GET">

    <div class="flex gap-2">

        {{-- INPUT PENCARIAN --}}
        <div class="relative min-w-0 flex-1">

            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#5A7A5A]">
                🔍
            </span>

            <input
                type="text"
                name="q"
                value="{{ $q }}"
                placeholder="Cari produk, petani, lokasi..."
                class="min-h-12 w-full rounded-xl border-2 border-[#D4E8D0] bg-white pl-11 pr-4 outline-none focus:border-[#2E7D32]"
            >

        </div>

        {{-- TOMBOL CARI --}}
        <button
            type="submit"
            class="min-h-12 rounded-xl bg-[#F4B400] px-5 font-bold text-[#1A2A00] transition hover:bg-[#E6A800]"
        >
            🔍 Cari
        </button>

        {{-- TOMBOL SARING --}}
        <button
            type="button"
            @click="filterOpen = true"
            class="flex min-h-12 items-center gap-2 rounded-xl border-2 border-[#D4E8D0] bg-white px-4 font-bold text-[#1A2A1A] hover:bg-[#EBF5EB]"
        >
            <span>☷</span>
            Saring

            @if ($activeCount > 0)
                <span class="rounded-full bg-[#F4B400] px-2 py-0.5 text-xs">
                    {{ $activeCount }}
                </span>
            @endif

        </button>

    </div>

    {{-- MEMPERTAHANKAN FILTER SAAT SEARCH --}}
    <input type="hidden" name="cat" value="{{ $cat }}">
    <input type="hidden" name="district" value="{{ $district }}">
    <input type="hidden" name="min_price" value="{{ $minPrice }}">
    <input type="hidden" name="max_price" value="{{ $maxPrice }}">
    <input type="hidden" name="nego" value="{{ $negoOnly ? 1 : 0 }}">
    <input type="hidden" name="sort" value="{{ $sort }}">

</form>
    </section>

    {{-- FILTER KATEGORI --}}
    <section class="mb-5">
        <div class="flex gap-2 overflow-x-auto pb-2">
            <a
                href="{{ route('pengusaha.katalog', request()->except('cat')) }}"
                class="flex min-h-11 shrink-0 items-center rounded-full border-2 px-4 font-semibold {{ $cat === '' ? 'border-[#2E7D32] bg-[#2E7D32] text-white' : 'border-[#D4E8D0] bg-white text-[#1A2A1A]' }}"
            >
                Semua
            </a>

            @foreach ($kategoriList as $item)
                <a
                    href="{{ route('pengusaha.katalog', array_merge(request()->query(), ['cat' => $item['nama']])) }}"
                    class="flex min-h-11 shrink-0 items-center gap-2 rounded-full border-2 px-4 font-semibold {{ $cat === $item['nama'] ? 'border-[#2E7D32] bg-[#2E7D32] text-white' : 'border-[#D4E8D0] bg-white text-[#1A2A1A]' }}"
                >
                    {{ $item['ikon'] }}
                    {{ $item['nama'] }}
                </a>
            @endforeach
        </div>
    </section>
     </div>
</div>

    {{-- HASIL DAN PENGURUTAN --}}
    <section>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <div class="text-sm text-[#5A7A5A]">
                <strong class="text-[#1A2A1A]">{{ count($filtered) }}</strong>
                produk ditemukan
            </div>

            <form method="GET" action="{{ route('pengusaha.katalog') }}">
                @foreach (request()->except('sort') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach

                <select
                    name="sort"
                    onchange="this.form.submit()"
                    class="min-h-11 rounded-xl border border-[#D4E8D0] bg-white px-3 text-sm font-semibold text-[#1A2A1A] outline-none focus:border-[#2E7D32]"
                >
                    <option value="newest" @selected($sort === 'newest')>Terbaru</option>
                    <option value="price-asc" @selected($sort === 'price-asc')>Harga Termurah</option>
                    <option value="price-desc" @selected($sort === 'price-desc')>Harga Termahal</option>
                    <option value="rating" @selected($sort === 'rating')>Rating Tertinggi</option>
                </select>
            </form>
        </div>

        @if ($q || $activeCount > 0)
            <div class="mb-4 flex flex-wrap items-center gap-2">
                <span class="text-sm text-[#5A7A5A]">Penyaringan aktif:</span>

                @if ($q)
                    <span class="rounded-full bg-[#EBF5EB] px-3 py-1 text-xs font-semibold text-[#1E5F3E]">
                        "{{ $q }}"
                    </span>
                @endif

                @if ($cat)
                    <span class="rounded-full bg-[#EBF5EB] px-3 py-1 text-xs font-semibold text-[#1E5F3E]">
                        {{ $cat }}
                    </span>
                @endif

                <a
                    href="{{ route('pengusaha.katalog') }}"
                    class="px-2 py-2 text-sm font-bold text-[#2E7D32] hover:underline"
                >
                    Hapus semua
                </a>
            </div>
        @endif

        {{-- GRID PRODUK --}}
        @if (count($filtered) > 0)
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($filtered as $produk)
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
                        :tersedia="$produk['stok'] > 0"
                        :detail-url="route('pengusaha.produk.detail', ['slug' => \Illuminate\Support\Str::slug($produk['nama'])])"
                    />
                @endforeach
            </div>
        @else
            {{-- EMPTY STATE --}}
            <div class="rounded-2xl border-2 border-dashed border-[#D4E8D0] bg-white px-5 py-12 text-center">
                <div class="mb-4 text-5xl">🔍</div>

                <h2 class="mb-2 text-xl font-extrabold text-[#1A2A1A]">
                    Produk tidak ditemukan
                </h2>

                <p class="mx-auto mb-5 max-w-sm text-sm text-[#5A7A5A]">
                    Coba ubah kata kunci pencarian atau hapus beberapa filter.
                </p>

                <a
                    href="{{ route('pengusaha.katalog') }}"
                    class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[#2E7D32] px-5 font-bold text-white hover:bg-[#1E5F3E]"
                >
                    Hapus Pencarian & Filter
                </a>
            </div>
        @endif
    </section>

    {{-- MODAL FILTER --}}
    <div
        x-cloak
        x-show="filterOpen"
        x-transition.opacity
        @keydown.escape.window="filterOpen = false"
        class="fixed inset-0 z-[80] flex items-end justify-center bg-black/50 md:items-center"
        role="dialog"
        aria-modal="true"
        aria-label="Saring produk"
    >
        <div
            @click.outside="filterOpen = false"
            x-transition
            class="relative max-h-[90vh] w-full overflow-y-auto rounded-t-3xl bg-white p-5 md:max-w-lg md:rounded-3xl"
        >
            <div class="mb-5 flex items-center justify-between border-b border-[#D4E8D0] pb-4">
                <h2 class="text-xl font-extrabold text-[#1A2A1A]">
                    Saring Produk
                </h2>

                <button
                    type="button"
                    @click="filterOpen = false"
                    class="min-h-11 rounded-xl px-3 font-bold text-[#2E7D32] hover:bg-[#EBF5EB]"
                >
                    Tutup
                </button>
            </div>

            <form method="GET" action="{{ route('pengusaha.katalog') }}" class="space-y-5">

                <div>
                    <label class="mb-2 block font-semibold text-[#1A2A1A]">
                        Kategori
                    </label>

                    <select name="cat" class="min-h-12 w-full rounded-xl border-2 border-[#D4E8D0] bg-white px-3">
                        <option value="">Semua kategori</option>
                        @foreach ($kategoriList as $item)
                            <option value="{{ $item['nama'] }}" @selected($cat === $item['nama'])>
                                {{ $item['nama'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block font-semibold text-[#1A2A1A]">
                        Kabupaten
                    </label>

                    <select name="district" class="min-h-12 w-full rounded-xl border-2 border-[#D4E8D0] bg-white px-3">
                        <option value="">Semua kabupaten</option>
                        @foreach ($kabupatenList as $kabupaten)
                            <option value="{{ $kabupaten }}" @selected($district === $kabupaten)>
                                {{ $kabupaten }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block font-semibold text-[#1A2A1A]">
                        Rentang Harga (Rp/kg)
                    </label>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <span class="mb-1 block text-xs text-[#5A7A5A]">Harga terendah</span>
                            <input
                                type="number"
                                name="min_price"
                                min="0"
                                step="1000"
                                value="{{ $minPrice }}"
                                class="min-h-12 w-full rounded-xl border-2 border-[#D4E8D0] px-3"
                            >
                        </div>

                        <div>
                            <span class="mb-1 block text-xs text-[#5A7A5A]">Harga tertinggi</span>
                            <input
                                type="number"
                                name="max_price"
                                min="0"
                                step="1000"
                                value="{{ $maxPrice }}"
                                class="min-h-12 w-full rounded-xl border-2 border-[#D4E8D0] px-3"
                            >
                        </div>
                    </div>
                </div>

                <label class="flex min-h-12 cursor-pointer items-center gap-3">
                    <input
                        type="checkbox"
                        name="nego"
                        value="1"
                        @checked($negoOnly)
                        class="h-5 w-5 accent-[#2E7D32]"
                    >

                    <span class="font-medium text-[#1A2A1A]">
                        Hanya produk yang bisa nego
                    </span>
                </label>

                <input type="hidden" name="q" value="{{ $q }}">
                <input type="hidden" name="sort" value="{{ $sort }}">

                <div class="flex gap-3 border-t border-[#D4E8D0] pt-4">
                    <a
                        href="{{ route('pengusaha.katalog') }}"
                        class="flex min-h-12 flex-1 items-center justify-center rounded-xl border-2 border-[#D4E8D0] font-bold text-[#1A2A1A]"
                    >
                        Atur Ulang
                    </a>

                    <button
                        type="submit"
                        class="min-h-12 flex-1 rounded-xl bg-[#F4B400] font-bold text-[#1A2A00] hover:bg-[#E6A800]"
                    >
                        Terapkan Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection
