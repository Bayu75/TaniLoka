@extends('layouts.pengusaha')

@section('title', 'Toko ' . $petani->name . ' - TaniLoka')

@section('content')

@php
    $profil = $petani->profilPetani;
    $kabupaten = $profil?->kabupaten;
    $inisial = strtoupper(mb_substr($petani->name, 0, 1));
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
                        {{ $petani->name }}
                    </h1>

                    @if ($kabupaten)
                        <p class="mt-1 text-sm text-[#5A7A5A]">📍 {{ $kabupaten }}, Bali</p>
                    @endif
                </div>

                @if ($petani->terverifikasi())
                    <span class="rounded-full bg-[#EBF5EB] px-4 py-2 text-sm font-bold text-[#2E7D32]">
                        ✓ Petani Terverifikasi
                    </span>
                @endif

            </div>

            @if ($profil?->deskripsi)
                <div class="mt-6 border-t border-[#E5EEE5] pt-5">
                    <h2 class="font-bold text-[#1A2A1A]">Tentang Toko</h2>
                    <p class="mt-2 max-w-3xl text-sm leading-relaxed text-[#5A7A5A]">
                        {{ $profil->deskripsi }}
                    </p>
                </div>
            @endif

            <div class="mt-5 flex flex-wrap gap-3">

                <div class="rounded-xl bg-[#F1F7F1] px-4 py-3">
                    <p class="text-xs text-[#789078]">Jumlah Produk</p>
                    <p class="mt-1 font-bold text-[#2E7D32]">{{ $produkToko->count() }} Produk</p>
                </div>

                @if ($kabupaten)
                    <div class="rounded-xl bg-[#F1F7F1] px-4 py-3">
                        <p class="text-xs text-[#789078]">Lokasi Toko</p>
                        <p class="mt-1 font-bold text-[#2E7D32]">{{ $kabupaten }}, Bali</p>
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- DAFTAR PRODUK --}}
    <div class="mt-8">

        <div class="mb-5">
            <h2 class="text-xl font-extrabold text-[#1A2A1A]">
                Produk dari {{ $petani->name }}
            </h2>
            <p class="mt-1 text-sm text-[#789078]">
                Temukan hasil pertanian yang tersedia di toko ini.
            </p>
        </div>

        @if ($produkToko->isNotEmpty())

            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($produkToko as $produk)
                    <x-pengusaha.product-card :produk="$produk" />
                @endforeach
            </div>

        @else

            <div class="rounded-2xl border border-dashed border-[#D4E8D0] bg-white p-10 text-center">
                <p class="font-bold text-[#5A7A5A]">Belum ada produk tersedia.</p>
            </div>

        @endif

    </div>

</div>

@endsection