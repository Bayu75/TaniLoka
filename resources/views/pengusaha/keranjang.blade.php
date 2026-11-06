
@extends('layouts.pengusaha')

@section('title', 'Keranjang - TaniLoka')

@section('content')
@php
  $produkList = [
    'bayam-hijau-organik' => [
        'nama' => 'Bayam Hijau Organik',
        'gambar' => 'https://images.unsplash.com/photo-1768734837803-54eb8c83e080?w=300&h=300&fit=crop&auto=format',
        'harga' => 8500,
        'minimal' => 5,
        'stok' => 120,
        'petani' => 'Pak Made Sukartha',
    ],

    'cabai-rawit-merah' => [
        'nama' => 'Cabai Rawit Merah',
        'gambar' => 'https://images.unsplash.com/photo-1768734837679-9b3180433384?w=300&h=300&fit=crop&auto=format',
        'harga' => 45000,
        'minimal' => 2,
        'stok' => 85,
        'petani' => 'Ibu Wayan Sari',
    ],

    'tomat-segar-grade-a' => [
        'nama' => 'Tomat Segar Grade A',
        'gambar' => 'https://images.unsplash.com/photo-1768734837464-c5045c6a98c4?w=300&h=300&fit=crop&auto=format',
        'harga' => 12000,
        'minimal' => 10,
        'stok' => 300,
        'petani' => 'Pak Ketut Wirasa',
    ],

    'kangkung-segar' => [
        'nama' => 'Kangkung Segar',
        'gambar' => 'https://images.unsplash.com/photo-1768734836079-040ec2543a65?w=300&h=300&fit=crop&auto=format',
        'harga' => 5500,
        'minimal' => 10,
        'stok' => 200,
        'petani' => 'Ibu Nyoman Dewi',
    ],

    'bawang-putih-lokal' => [
        'nama' => 'Bawang Putih Lokal',
        'gambar' => 'https://images.unsplash.com/photo-1765862857099-cb03e1c3de07?w=300&h=300&fit=crop&auto=format',
        'harga' => 35000,
        'minimal' => 5,
        'stok' => 150,
        'petani' => 'Pak Gede Setiawan',
    ],

    'kembang-kol-putih' => [
        'nama' => 'Kembang Kol Putih',
        'gambar' => 'https://images.unsplash.com/photo-1770782912836-82b4d67444d5?w=300&h=300&fit=crop&auto=format',
        'harga' => 15000,
        'minimal' => 5,
        'stok' => 60,
        'petani' => 'Pak Ketut Wirasa',
    ],

    'wortel-organik' => [
        'nama' => 'Wortel Organik',
        'gambar' => 'https://images.unsplash.com/photo-1768119901638-75c6edd64c0b?w=300&h=300&fit=crop&auto=format',
        'harga' => 14000,
        'minimal' => 5,
        'stok' => 90,
        'petani' => 'Pak Made Sukartha',
    ],

    'paprika-merah' => [
        'nama' => 'Paprika Merah',
        'gambar' => 'https://images.unsplash.com/photo-1777791374501-ff28ce93d2cb?w=300&h=300&fit=crop&auto=format',
        'harga' => 55000,
        'minimal' => 3,
        'stok' => 0,
        'petani' => 'Ibu Wayan Sari',
    ],
];

    $keranjang = session()->get('keranjang', []);
    $total = 0;
    $jumlahProduk = 0;

    foreach ($keranjang as $slug => $jumlah) {
        if (isset($produkList[$slug])) {
            $total += $produkList[$slug]['harga'] * $jumlah;
            $jumlahProduk++;
        }
    }
@endphp

<div class="mb-6">
    <p class="mb-1 text-sm font-medium text-primary">Belanja</p>
    <h1 class="text-2xl font-extrabold text-text md:text-3xl">
        Keranjang Saya
    </h1>
    <p class="mt-2 text-sm text-text-muted">
        Periksa kembali produk sebelum melanjutkan pesanan.
    </p>
</div>

<div class="grid gap-6 lg:grid-cols-3">

    {{-- DAFTAR PRODUK --}}
    <div class="space-y-4 lg:col-span-2">

        @if ($jumlahProduk === 0)
            <div class="rounded-2xl border border-border bg-white px-6 py-12 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-muted text-3xl">
                    🛒
                </div>

                <h2 class="text-lg font-bold text-text">
                    Keranjang masih kosong
                </h2>

                <p class="mt-2 text-sm text-text-muted">
                    Yuk, temukan bahan pangan segar dari petani lokal.
                </p>

                <a href="{{ route('pengusaha.katalog') }}"
                   class="mt-5 inline-block rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-dark">
                    Mulai Belanja
                </a>
            </div>
        @else

            @foreach ($keranjang as $slug => $jumlah)
                @if (isset($produkList[$slug]))
                    @php
                        $produk = $produkList[$slug];
                        $subtotal = $produk['harga'] * $jumlah;
                    @endphp

                    <div class="rounded-2xl border border-border bg-white p-4">

                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">

                            <img
                                src="{{ $produk['gambar'] }}"
                                alt="{{ $produk['nama'] }}"
                                class="h-24 w-full rounded-xl object-cover sm:w-28"
                            >

                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-medium text-primary">
                                    {{ $produk['petani'] }}
                                </p>

                                <h2 class="mt-1 font-bold text-text">
                                    {{ $produk['nama'] }}
                                </h2>

                                <p class="mt-1 text-sm text-text-muted">
                                    Rp{{ number_format($produk['harga'], 0, ',', '.') }} / kg
                                </p>

                                <p class="mt-2 font-bold text-primary">
                                    Rp{{ number_format($subtotal, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>

                        {{-- FORM UBAH JUMLAH --}}
                        <div class="mt-4 flex flex-wrap items-end justify-between gap-3 border-t border-border pt-4">

                            <form action="{{ route('pengusaha.keranjang.update') }}"
                                  method="POST"
                                  class="flex flex-wrap items-end gap-2">

                                @csrf

                                <input type="hidden" name="slug" value="{{ $slug }}">

                                <div>
                                    <label class="mb-1 block text-xs font-medium text-text-muted">
                                        Jumlah (kg)
                                    </label>

                                    <input
                                        type="number"
                                        name="jumlah"
                                        value="{{ $jumlah }}"
                                        min="{{ $produk['minimal'] }}"
                                        max="{{ $produk['stok'] }}"
                                        required
                                        class="w-24 rounded-lg border border-border px-3 py-2 text-sm outline-none focus:border-primary"
                                    >
                                </div>

                                <button
                                    type="submit"
                                    class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark"
                                >
                                    Ubah Jumlah
                                </button>
                            </form>

                            {{-- FORM HAPUS --}}
                            <form action="{{ route('pengusaha.keranjang.hapus') }}"
                                  method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus produk ini?')">

                                @csrf

                                <input type="hidden" name="slug" value="{{ $slug }}">

                                <button
                                    type="submit"
                                    class="rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-500 hover:bg-red-50"
                                >
                                    Hapus
                                </button>
                            </form>

                        </div>
                    </div>
                @endif
            @endforeach

        @endif
    </div>

    {{-- RINGKASAN BELANJA --}}
    <div class="h-fit rounded-2xl border border-border bg-white p-5">
        <h2 class="text-lg font-bold text-text">
            Ringkasan Belanja
        </h2>

        <div class="mt-5 flex justify-between text-sm text-text-muted">
            <span>Total produk</span>
            <span>{{ $jumlahProduk }} produk</span>
        </div>

        <div class="mt-4 flex justify-between gap-3">
            <span class="font-semibold text-text">
                Total Belanja
            </span>

            <span class="font-extrabold text-primary">
                Rp{{ number_format($total, 0, ',', '.') }}
            </span>
        </div>

        <div class="my-5 border-t border-border"></div>

        <button
            type="button"
            disabled
            class="w-full cursor-not-allowed rounded-xl bg-primary px-5 py-3 font-semibold text-white opacity-50"
        >
            Lanjutkan Pesanan
        </button>

        <a href="{{ route('pengusaha.katalog') }}"
           class="mt-3 block text-center text-sm font-medium text-primary hover:underline">
            + Tambah Produk Lain
        </a>
    </div>

</div>
@endsection
