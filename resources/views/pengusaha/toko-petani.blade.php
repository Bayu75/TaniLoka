
@extends('layouts.pengusaha')

@section('title', 'Toko Petani - TaniLoka')

@section('content')


@php
    // DATA TOKO DARI DATABASE
    $profil = $toko->profilPetani;

    $namaToko = $profil?->nama_usaha_tani ?: $toko->name;
    $kabupaten = $profil?->kabupaten ?: 'Lokasi belum diisi';
    $deskripsi = $profil?->deskripsi ?: 'Belum ada deskripsi toko.';

    $inisial = strtoupper(substr($namaToko, 0, 1));
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
                {{ $inisial }}
            </div>

            <div class="flex-1 pb-1">
                <h1 class="text-2xl font-extrabold text-[#1A2A1A]">
                    {{ $namaToko }}
                </h1>

                <p class="mt-1 text-sm text-[#5A7A5A]">
                    📍 {{ $kabupaten }}, Bali
                </p>
            </div>

        </div>

        <div class="mt-6 border-t border-[#E5EEE5] pt-5">
            <h2 class="font-bold text-[#1A2A1A]">
                Tentang Toko
            </h2>

            <p class="mt-2 max-w-3xl text-sm leading-relaxed text-[#5A7A5A]">
                {{ $deskripsi }}
            </p>
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
                        {{ $kabupaten }}, Bali
                    </p>
                </div>

            </div>
        </div>
    </div>


    {{-- DAFTAR PRODUK --}}
    <div class="mt-8">

        <div class="mb-5">
            <h2 class="text-xl font-extrabold text-[#1A2A1A]">
                Produk dari {{ $namaToko }}
            </h2>

            <p class="mt-1 text-sm text-[#789078]">
                Temukan hasil pertanian yang tersedia di toko ini.
            </p>
        </div>

        @if ($produkToko->isNotEmpty())

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">

                @foreach ($produkToko as $produk)

                    <div class="overflow-hidden rounded-2xl border border-[#D4E8D0] bg-white transition hover:-translate-y-1 hover:shadow-lg">

                        <div class="relative aspect-[4/3] overflow-hidden bg-[#EBF5EB]">

                            <img
                                src="{{ $produk->fotoUtama?->url ?? asset('images/no-image.png') }}"
                                alt="{{ $produk->nama }}"
                                class="h-full w-full object-cover"
                            >

                            <span class="absolute left-3 top-3 rounded-full bg-white/95 px-3 py-1 text-xs font-bold text-[#2E7D32]">
                                {{ $produk->kategori?->nama ?? 'Lainnya' }}
                            </span>

                            @if ($produk->stok_kg <= 0)
                                <span class="absolute right-3 top-3 rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-600">
                                    Stok Habis
                                </span>
                            @endif

                        </div>

                        <div class="p-4">

                            <h3 class="font-bold text-[#1A2A1A]">
                                {{ $produk->nama }}
                            </h3>

                            <p class="mt-2 text-lg font-extrabold text-[#2E7D32]">
                                Rp{{ number_format($produk->harga_per_kg, 0, ',', '.') }}
                                <span class="text-xs font-normal text-[#789078]">/kg</span>
                            </p>

                            <div class="mt-3 flex justify-between text-xs text-[#789078]">
                                <span>Minimal {{ $produk->min_order_kg }} kg</span>
                                <span>Stok: {{ $produk->stok_kg }} kg</span>
                            </div>

                            <a href="{{ route('pengusaha.produk.detail', ['slug' => $produk->slug]) }}"
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
