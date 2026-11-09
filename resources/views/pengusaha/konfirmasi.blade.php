
@extends('layouts.pengusaha')

@section('title', 'Konfirmasi Pesanan - TaniLoka')

@php
    $metodePengiriman = request('metode', 'diantar');
    $kurir = request('kurir', 'kendaraan');
    $metodeBayar = request('pembayaran', 'transfer');

    $subtotal = 90000;
    $ongkir = $metodePengiriman === 'ambil' ? 0 : ($kurir === 'jnt' ? 12000 : 0);
    $total = $subtotal + $ongkir;

    $namaPembayaran = [
        'transfer' => 'Transfer Bank',
        'gopay' => 'GoPay',
        'ovo' => 'OVO',
        'cod' => 'COD (Bayar di Tempat)',
    ];

    $namaPengiriman = [
        'kendaraan' => 'Kendaraan Sendiri',
        'jnt' => 'J&T Express',
    ];
@endphp

@section('content')

<div class="mx-auto w-full max-w-4xl">

    {{-- Header Halaman --}}
    <div class="relative left-1/2 -mt-4 w-screen -translate-x-1/2 border-b border-green-200 bg-white">
        <div class="mx-auto max-w-4xl px-4 py-5">
            <div class="flex items-center gap-5">
                <a href="{{ route('pengusaha.pembayaran') }}"
                class="font-semibold text-primary hover:text-green-700">
                    ‹ Kembali
                </a>

                <h1 class="text-xl font-extrabold text-gray-900">
                    Konfirmasi Pesanan
                </h1>
            </div>
        </div>
    </div>

    {{-- Progress Tahapan --}}
    <div class="relative left-1/2 w-screen -translate-x-1/2 border-b border-green-200 bg-white">
        <div class="mx-auto flex max-w-4xl items-center justify-center gap-3 px-4 py-3">

            <div class="flex items-center gap-2 text-sm font-semibold text-green-700">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-green-600 text-white">✓</span>
                Pengiriman
            </div>

            <div class="h-0.5 w-12 bg-green-600"></div>

            <div class="flex items-center gap-2 text-sm font-semibold text-green-700">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-green-600 text-white">✓</span>
                Pembayaran
            </div>

            <div class="h-0.5 w-12 bg-green-600"></div>

            <div class="flex items-center gap-2 text-sm font-semibold text-gray-900">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-500 text-white">3</span>
                Konfirmasi
            </div>

        </div>
    </div>

    {{-- Konten Konfirmasi --}}
    <div class="mx-auto grid w-full max-w-4xl grid-cols-1 items-start gap-6 pt-6 lg:grid-cols-[minmax(0,1fr)_266px]">

        {{-- Kolom Kiri --}}
        <div class="min-w-0 space-y-4">


    {{-- Detail Pesanan --}}
    <div class="overflow-hidden rounded-2xl border border-green-200 bg-white">

        {{-- Informasi Penjual --}}
        <div class="border-b border-green-200 bg-green-50 px-5 py-4">
            <h2 class="font-bold text-gray-900">
                Ibu Wayan Sari
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                1 produk · Rp90.000
            </p>
        </div>

        {{-- Informasi Produk --}}
        <div class="flex items-center justify-between gap-3 p-5">
            <div class="flex min-w-0 items-center gap-3">
                <div class="h-14 w-14 shrink-0 overflow-hidden rounded-xl bg-green-100">
                    <img
                        src="{{ asset('images/products/cabai rawit.jpg') }}"
                        alt="Cabai Rawit Merah"
                        class="h-full w-full object-cover"
                    >
                </div>

                <div class="min-w-0">
                    <h3 class="font-bold text-gray-900">
                        Cabai Rawit Merah
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        2 kg × Rp45.000
                    </p>
                </div>
            </div>

            <p class="shrink-0 font-bold text-gray-900">
                Rp90.000
            </p>
        </div>

        {{-- Subtotal dan Ongkir --}}
        <div class="space-y-2 border-t border-gray-200 px-5 py-4 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600">Subtotal</span>
                <span class="text-gray-700">Rp90.000</span>
            </div>

            <div class="flex justify-between">
                <span class="text-gray-600">Ongkos kirim</span>
                <span class="text-gray-700">Menyesuaikan pilihan pengiriman</span>
            </div>
        </div>

    </div>


    {{-- Alamat dan Informasi Pembayaran --}}
    <div class="rounded-2xl border border-green-100 bg-white p-4">

        <p class="text-sm text-gray-600">
            Alamat:
            <span class="font-medium text-gray-900">
                Restoran · Jl. Sunset Road No. 88, Denpasar
            </span>
        </p>

        <p class="mt-2 text-sm text-gray-600">
            Pengiriman:
            <span class="font-medium text-gray-900">
                {{ $metodePengiriman === 'ambil' ? 'Ambil Sendiri' : ($namaPengiriman[$kurir] ?? 'Kendaraan Sendiri') }}
            </span>
        </p>

        <p class="mt-2 text-sm text-gray-600">
            Metode bayar:
            <span class="font-medium text-gray-900">
                {{ $namaPembayaran[$metodeBayar] ?? 'Transfer Bank' }}
            </span>
        </p>

        <p class="mt-2 text-sm leading-relaxed text-gray-600">
            Bayar setelah petani mengonfirmasi pesanan (maks. 24 jam).
            Petani yang tidak merespons membuat pesanan batal otomatis.
        </p>

    </div>

    {{-- Tombol Kirim Pesanan --}}
    <form action="{{ route('pengusaha.konfirmasi.store') }}" method="POST">
        @csrf

        <input type="hidden" name="metode" value="{{ $metodePengiriman }}">
        <input type="hidden" name="kurir" value="{{ $kurir }}">
        <input type="hidden" name="pembayaran" value="{{ $metodeBayar }}">

        <button
            type="submit"
            class="w-full rounded-xl bg-amber-500 px-4 py-4 text-center font-bold text-gray-900 transition hover:bg-amber-600"
        >
            Kirim Pesanan · Rp {{ number_format($total, 0, ',', '.') }}
        </button>
    </form>

</div>


{{-- Kolom Kanan: Ringkasan --}}
<div class="self-start lg:sticky lg:top-24">
    <div class="rounded-2xl border border-green-100 bg-white p-4">

        <h2 class="mb-4 text-lg font-bold text-gray-800">
            Ringkasan
        </h2>

        {{-- Produk --}}
        <div class="flex items-center justify-between gap-2">
            <p class="truncate text-sm text-gray-600">
                Cabai Rawit Merah
            </p>
            <p class="shrink-0 text-sm font-bold text-gray-800">
                Rp {{ number_format($subtotal, 0, ',', '.') }}
            </p>
        </div>

        <div class="mt-3 space-y-2 border-t border-gray-200 pt-3 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600">Subtotal</span>
                <span class="text-gray-700">
                    Rp {{ number_format($subtotal, 0, ',', '.') }}
                </span>
            </div>

            <div class="flex justify-between">
                <span class="text-gray-600">Ongkos kirim</span>
                <span class="text-gray-700">
                    {{ $ongkir === 0 ? 'Gratis' : 'Rp ' . number_format($ongkir, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <div class="mt-3 flex justify-between border-t border-gray-200 pt-3">
            <span class="font-bold text-primary">Total</span>
            <span class="font-extrabold text-primary">
                Rp {{ number_format($total, 0, ',', '.') }}
            </span>
        </div>

    </div>

</div>

@endsection
