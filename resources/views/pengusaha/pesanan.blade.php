
@extends('layouts.pengusaha')

@section('title', 'Pesanan - TaniLoka')

@section('content')

@php

    $pesanan = [
        [
            'id' => 'TL-0008',
            'tanggal' => '26 Okt 2026',
            'status' => 'menunggu_konfirmasi',
            'nama_petani' => 'Ibu Wayan Sari',
            'produk' => 'Cabai Rawit Merah',
            'gambar' => 'images/products/cabai rawit.jpg',
            'jumlah' => '5 kg',
            'total' => 237000,
            'foto' => 'https://placehold.co/100x100?text=Cabai',
            'notifikasi' => 'Batas konfirmasi: 21 jam 58 menit',
            'jenis_notifikasi' => 'waktu',
        ],
        [
            'id' => 'TL-0006',
            'tanggal' => '26 Okt 2026',
            'status' => 'menunggu_pembayaran',
            'nama_petani' => 'Ibu Nyoman Dewi',
            'produk' => 'Kangkung Segar',
            'gambar' => 'images/products/kangkung.png',
            'jumlah' => '20 kg',
            'total' => 110000,
            'foto' => 'https://placehold.co/100x100?text=Kangkung',
            'notifikasi' => 'Batas pembayaran: 1 menit 44 detik',
            'jenis_notifikasi' => 'waktu',
            'tindakan' => 'Perlu tindakan: Unggah bukti pembayaran',
        ],
        [
            'id' => 'TL-0004',
            'tanggal' => '23 Okt 2026',
            'status' => 'dikirim',
            'nama_petani' => 'Pak Ketut Wirasa',
            'produk' => 'Tomat Segar Grade A',
            'gambar' => 'images/products/tomat.jpg',
            'jumlah' => '20 kg',
            'total' => 260000,
            'foto' => 'https://placehold.co/100x100?text=Tomat',
        ],
        [
            'id' => 'TL-0001',
            'tanggal' => '14 Okt 2026',
            'status' => 'selesai',
            'nama_petani' => 'Pak Made Sukartha',
            'produk' => 'Bayam Hijau Organik',
            'gambar' => 'images/products/bayam.png',
            'jumlah' => '10 kg',
            'total' => 100000,
            'foto' => 'https://placehold.co/100x100?text=Bayam',
            'tindakan' => 'Perlu tindakan: Beri penilaian',
        ],
    ];
@endphp

<div class="mx-auto max-w-[760px] space-y-4" x-data="{ filter: 'semua', detailPesanan: null }">    <div>
        <h1 class="text-2xl font-extrabold text-gray-900">
            Pesanan
        </h1>
        <p class="mt-1 text-sm text-gray-500">
            Pantau dan kelola pesanan produk pertanianmu.
        </p>
    </div>



    {{-- Filter Status --}}
<div class="flex w-full items-center gap-3 overflow-x-auto pb-3">        <button
            @click="filter = 'semua'"
            :class="filter === 'semua'
                ? 'bg-green-800 text-white border-green-800'
                : 'bg-white text-gray-800 border-green-200'"
            class="shrink-0 rounded-full border px-4 py-2 text-xs font-bold">
            Semua (4)
        </button>

        <button
            @click="filter = 'menunggu'"
            :class="filter === 'menunggu'
                ? 'bg-green-800 text-white border-green-800'
                : 'bg-white text-gray-800 border-green-200'"
            class="shrink-0 rounded-full border px-4 py-2 text-xs font-bold">
            Menunggu (2)
        </button>

        <button
            @click="filter = 'diproses'"
            :class="filter === 'diproses'
                ? 'bg-green-800 text-white border-green-800'
                : 'bg-white text-gray-800 border-green-200'"
            class="shrink-0 rounded-full border px-4 py-2 text-xs font-bold">
            Diproses (0)
        </button>

        <button
            @click="filter = 'dikirim'"
            :class="filter === 'dikirim'
                ? 'bg-green-800 text-white border-green-800'
                : 'bg-white text-gray-800 border-green-200'"
            class="shrink-0 rounded-full border px-4 py-2 text-xs font-bold">
            Dikirim (1)
        </button>

        <button
            @click="filter = 'selesai'"
            :class="filter === 'selesai'
                ? 'bg-green-800 text-white border-green-800'
                : 'bg-white text-gray-800 border-green-200'"
            class="shrink-0 rounded-full border px-4 py-2 text-xs font-bold">
            Selesai (1)
        </button>

        <button
            @click="filter = 'dibatalkan'"
            :class="filter === 'dibatalkan'
                ? 'bg-green-800 text-white border-green-800'
                : 'bg-white text-gray-800 border-green-200'"
            class="shrink-0 rounded-full border px-4 py-2 text-xs font-bold">
            Dibatalkan (0)
        </button>

    </div>



    {{-- Daftar Pesanan --}}
    <div class="flex flex-col gap-5">

        @foreach ($pesanan as $item)

            <div
                x-show="
                    filter === 'semua' ||
                    (filter === 'menunggu' && ['menunggu_pembayaran', 'menunggu_konfirmasi'].includes('{{ $item['status'] }}')) ||
                    filter === '{{ $item['status'] }}'
                "
                x-transition
                class="overflow-hidden rounded-2xl border border-green-200 bg-white shadow-sm">

                {{-- Header Pesanan --}}

                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-green-200 p-4
                    {{ match($item['status']) {
                        'menunggu_konfirmasi' => 'bg-yellow-100',
                        'menunggu_pembayaran' => 'bg-orange-100',
                        'dikirim' => 'bg-green-100',
                        'selesai' => 'bg-emerald-100',
                        default => 'bg-white',
                    } }}">

                    <div>
                        <div class="flex items-center gap-3">

                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-xl">
                                @switch($item['status'])
                                    @case('menunggu_konfirmasi')
                                        ⏳
                                        @break
                                    @case('menunggu_pembayaran')
                                        💳
                                        @break
                                    @case('dikirim')
                                        🚚
                                        @break
                                    @case('selesai')
                                        🎉
                                        @break
                                @endswitch
                            </div>

                            <div>
                                <p class="text-sm font-extrabold
                                    {{ $item['status'] === 'menunggu_konfirmasi' ? 'text-yellow-800' : '' }}
                                    {{ $item['status'] === 'menunggu_pembayaran' ? 'text-orange-800' : '' }}
                                    {{ $item['status'] === 'dikirim' ? 'text-green-800' : '' }}
                                    {{ $item['status'] === 'selesai' ? 'text-emerald-800' : '' }}">
                                    {{ str_replace('_', ' ', ucfirst($item['status'])) }}
                                </p>

                                <h3 class="font-bold text-gray-900">
                                    {{ $item['nama_petani'] }}
                                </h3>

                                <p class="text-sm text-gray-500">
                                    #{{ $item['id'] }} · {{ $item['tanggal'] }}
                                </p>
                            </div>

                        </div>

                    </div>

                </div>

                {{-- Informasi Produk --}}
                <div class="space-y-4 p-5">


                    <div class="space-y-3">

                        {{-- Foto dan Informasi Produk --}}
                        <div class="flex items-center gap-3">

                            <img
                                src="{{ asset($item['gambar']) }}"
                                alt="{{ $item['produk'] }}"
                                class="h-12 w-12 rounded-xl object-cover"
                            >

                            <div>
                                <h4 class="text-sm font-bold text-gray-900">
                                    {{ $item['produk'] }}
                                </h4>

                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $item['jumlah'] }}
                                </p>
                            </div>

                        </div>

                        {{-- Total Pesanan --}}
                        <p class="font-extrabold text-green-700">
                            Total: Rp{{ number_format($item['total'], 0, ',', '.') }}
                        </p>

                        {{-- Notifikasi Pesanan --}}
                        @if (!empty($item['notifikasi']))
                            <div class="inline-flex rounded-lg px-3 py-2 text-xs font-bold
                                {{ $item['jenis_notifikasi'] === 'waktu'
                                    ? 'bg-amber-50 text-amber-800'
                                    : 'bg-red-50 text-red-600' }}">
                                ⏱ {{ $item['notifikasi'] }}
                            </div>
                        @endif

                        @if (!empty($item['tindakan']))
                            <div class="rounded-lg bg-amber-50 px-3 py-2 text-xs font-bold text-amber-800">
                                {{ $item['tindakan'] }}
                            </div>
                        @endif

                    </div>

                    </div>
                        {{-- Tombol Detail --}}
                        <div class="px-3 pt-1 pb-3">
                            <a
                                href="{{ route('pengusaha.pesanan.detail', $item['id']) }}"
                                class="block w-full rounded-xl bg-green-700 px-5 py-2.5 text-center text-sm font-bold text-white transition hover:bg-green-800">
                                Lihat Detail
                            </a>
                        

                        </div>

                        {{-- Detail Pesanan dan Timeline --}}
                        <div
                            x-show="detailPesanan === '{{ $item['id'] }}'"
                            x-transition
                            class="border-t border-green-200 bg-green-50 p-5">

                            <h4 class="mb-5 text-lg font-extrabold text-gray-900">
                                Perkembangan Pesanan
                            </h4>

                            <x-order-timeline :status="$item['status']" />

                 </div>

            </div>

        @endforeach

    </div>

</div>

@endsection
