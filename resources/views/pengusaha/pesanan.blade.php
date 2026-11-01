
@extends('layouts.pengusaha')

@section('title', 'Pesanan - TaniLoka')

@section('content')

@php
    $pesanan = [
        [
            'id' => 'TL-0008',
            'tanggal' => '1 Nov 2026',
            'status' => 'menunggu_konfirmasi',
            'nama_petani' => 'Ibu Wayan Sari',
            'produk' => 'Cabai Rawit Merah',
            'jumlah' => '5 kg',
            'total' => 237000,
        ],
        [
            'id' => 'TL-0009',
            'tanggal' => '2 Nov 2026',
            'status' => 'diproses',
            'nama_petani' => 'Pak Made Suarta',
            'produk' => 'Tomat Organik',
            'jumlah' => '10 kg',
            'total' => 120000,
        ],
        [
            'id' => 'TL-0010',
            'tanggal' => '2 Nov 2026',
            'status' => 'selesai',
            'nama_petani' => 'Ibu Ketut Rina',
            'produk' => 'Bayam Organik',
            'jumlah' => '3 kg',
            'total' => 25500,
        ],
    ];
@endphp

<div class="space-y-6" x-data="{ filter: 'semua' }">
    {{-- Judul Halaman --}}
    <div>
        <h1 class="text-2xl font-extrabold text-gray-900">
            Pesanan
        </h1>
        <p class="mt-1 text-sm text-gray-500">
            Pantau dan kelola pesanan produk pertanianmu.
        </p>
    </div>


    {{-- Filter Status --}}
    <div class="flex gap-3 overflow-x-auto pb-3">

        <button
            @click="filter = 'semua'"
            :class="filter === 'semua'
                ? 'bg-green-700 text-white border-green-700'
                : 'bg-white text-green-800 border-green-200'"
            class="shrink-0 rounded-full border-2 px-5 py-2 font-semibold">
            Semua
        </button>

        <button
            @click="filter = 'menunggu'"
            :class="filter === 'menunggu'
                ? 'bg-green-700 text-white border-green-700'
                : 'bg-white text-green-800 border-green-200'"
            class="shrink-0 rounded-full border-2 px-5 py-2 font-semibold">
            Menunggu
        </button>

        <button
            @click="filter = 'diproses'"
            :class="filter === 'diproses'
                ? 'bg-green-700 text-white border-green-700'
                : 'bg-white text-green-800 border-green-200'"
            class="shrink-0 rounded-full border-2 px-5 py-2 font-semibold">
            Diproses
        </button>

        <button
            @click="filter = 'dikirim'"
            :class="filter === 'dikirim'
                ? 'bg-green-700 text-white border-green-700'
                : 'bg-white text-green-800 border-green-200'"
            class="shrink-0 rounded-full border-2 px-5 py-2 font-semibold">
            Dikirim
        </button>

        <button
            @click="filter = 'selesai'"
            :class="filter === 'selesai'
                ? 'bg-green-700 text-white border-green-700'
                : 'bg-white text-green-800 border-green-200'"
            class="shrink-0 rounded-full border-2 px-5 py-2 font-semibold">
            Selesai
        </button>

        <button
            @click="filter = 'dibatalkan'"
            :class="filter === 'dibatalkan'
                ? 'bg-green-700 text-white border-green-700'
                : 'bg-white text-green-800 border-green-200'"
            class="shrink-0 rounded-full border-2 px-5 py-2 font-semibold">
            Dibatalkan
        </button>

    </div>


    {{-- Daftar Pesanan --}}
    <div class="space-y-5">

        @foreach ($pesanan as $item)

            <div
                x-show="
                    filter === 'semua' ||
                    (filter === 'menunggu' && {{ in_array($item['status'], ['menunggu_pembayaran', 'menunggu_konfirmasi']) ? 'true' : 'false' }}) ||
                    filter === '{{ $item['status'] }}'
                "
                x-transition
                class="overflow-hidden rounded-2xl border border-green-200 bg-white shadow-sm">

                {{-- Header Pesanan --}}
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-green-200 bg-green-50 p-5">

                    <div>
                        <x-status-badge :status="$item['status']" />

                        <h3 class="mt-2 font-bold text-gray-900">
                            {{ $item['nama_petani'] }}
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            #{{ $item['id'] }} · {{ $item['tanggal'] }}
                        </p>
                    </div>

                </div>

                {{-- Informasi Produk --}}
                <div class="space-y-4 p-5">

                    <div class="flex items-center justify-between gap-3">

                        <div>
                            <h4 class="font-bold text-gray-900">
                                {{ $item['produk'] }}
                            </h4>

                            <p class="mt-1 text-sm text-gray-500">
                                {{ $item['jumlah'] }}
                            </p>
                        </div>

                        <div class="text-right">
                            <p class="text-sm text-gray-500">
                                Total Pesanan
                            </p>

                            <p class="text-lg font-extrabold text-green-700">
                                Rp{{ number_format($item['total'], 0, ',', '.') }}
                            </p>
                        </div>

                    </div>

                    {{-- Tombol Detail --}}
                    <div class="flex justify-end border-t border-gray-100 pt-4">

                        <button
                            type="button"
                            class="rounded-xl bg-green-700 px-5 py-2.5
                                   font-bold text-white transition
                                   hover:bg-green-800">
                            Lihat Detail
                        </button>

                    </div>

                </div>

            </div>

        @endforeach

    </div>

</div>

@endsection
