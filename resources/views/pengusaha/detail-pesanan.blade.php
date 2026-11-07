@extends('layouts.pengusaha')

@section('title', 'Detail Pesanan - TaniLoka')

@section('content')

<div class="mx-auto max-w-[760px] space-y-5">

    {{-- Judul Detail Pesanan --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('pengusaha.pesanan') }}"
            class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 text-gray-600 hover:bg-gray-100">
            ←
        </a>

        <div>
            <h1 class="text-2xl font-bold text-gray-800">Detail Pesanan</h1>
            <p class="text-sm text-gray-500">Informasi lengkap pesanan Anda</p>
        </div>
    </div>

    {{-- Status Pesanan --}}
    <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-5">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-yellow-100 text-2xl">
                ⏳
            </div>

            <div>
                <h2 class="font-bold text-yellow-800">Menunggu Konfirmasi</h2>
                <p class="mt-1 text-sm text-yellow-700">
                    Pesanan sedang menunggu konfirmasi dari penjual.
                </p>
            </div>
        </div>
    </div>

    {{-- Batas Konfirmasi --}}
    <div class="rounded-2xl border border-yellow-200 bg-white p-4">
        <p class="mb-3 text-sm font-semibold text-yellow-800">
            Menunggu penjual mengonfirmasi pesanan Anda.
        </p>

        <div class="inline-flex items-center gap-2 rounded-lg bg-yellow-50 px-3 py-2 text-sm font-semibold text-yellow-800">
            <span>⏱️</span>
            <span>Batas konfirmasi: 21 jam 59 menit</span>
        </div>

            <p class="mt-1 text-sm text-grey-800">
                Jika pesanan tidak dikonfirmasi sebelum tenggat oleh penjual, maka pesanan otomatis dibatalkan.
            </p>

    </div>

    {{-- Tombol Batalkan Pesanan --}}
    <div>
        <button
            type="button"
            onclick="alert('Fitur pembatalan pesanan akan segera tersedia.')"
            class="w-full rounded-xl border border-red-300 bg-white px-4 py-3 text-sm font-semibold text-red-600 transition hover:bg-red-50">
            Batalkan Pesanan
        </button>
    </div>

    {{-- Riwayat Pesanan --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5">
        <h2 class="mb-5 text-lg font-bold text-gray-800">
            Riwayat Pesanan
        </h2>

        <x-order-timeline status="menunggu_konfirmasi" />
    </div>

    {{-- Produk yang Dipesan --}}
    <div class="rounded-2xl border border-green-200 bg-white p-5">
        <h2 class="mb-4 text-lg font-bold text-gray-800">
            Produk Dipesan
        </h2>

        <div class="flex items-center gap-3">
            <img
                src="{{ asset('images/products/cabai rawit.jpg') }}"
                alt="Cabai Rawit Merah"
                class="h-16 w-16 rounded-xl object-cover"
            >

            <div class="flex-1">
                <h3 class="text-sm font-bold text-gray-800">
                    Cabai Rawit Merah
                </h3>

                <p class="mt-1 text-sm text-green-800">
                    5 kg × Rp45.000
                </p>

                <p class="mt-1 font-bold text-green-700">
                    Rp225.000
                </p>
            </div>
        </div>
    </div>

    {{-- Pengiriman dan Metode Pembayaran --}}
    <div class="rounded-2xl border border-green-200 bg-white p-5">

        {{-- Informasi Pengiriman --}}
        <div>
            <h2 class="mb-1 font-bold text-gray-800">
                Pengiriman
            </h2>

            <p class="text-sm text-gray-800">
                J&T Express
            </p>

            <p class="mt-1 text-sm text-green-800">
                Sari Kitchen, Jl. Sunset Road No. 88, Denpasar
            </p>
        </div>

        {{-- Metode Pembayaran --}}
        <div class="mt-4">
            <h2 class="mb-1 font-bold text-gray-800">
                Metode Pembayaran
            </h2>

            <p class="text-sm text-gray-800">
                Transfer Bank
            </p>
        </div>

    </div>

    {{-- Rincian Biaya --}}
    <div class="rounded-2xl border border-green-200 bg-white p-5">
        <h2 class="mb-3 font-bold text-gray-800">
            Rincian Biaya
        </h2>

        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-green-800">Subtotal produk</span>
                <span class="text-gray-800">Rp225.000</span>
            </div>

            <div class="flex justify-between">
                <span class="text-green-800">Ongkos kirim</span>
                <span class="text-gray-800">Rp12.000</span>
            </div>

            <div class="mt-3 flex justify-between border-t border-green-100 pt-3">
                <span class="font-bold text-gray-800">Total</span>
                <span class="font-extrabold text-green-700">Rp237.000</span>
            </div>
        </div>
    </div>

    {{-- Informasi Penjual --}}
    <div class="rounded-2xl border border-green-200 bg-white p-5">
        <h2 class="mb-3 font-bold text-gray-800">
            Penjual
        </h2>

        <p class="mb-3 text-sm font-semibold text-gray-800">
            Ibu Wayan Sari
        </p>

        <div class="flex gap-3">
            <button
                type="button"
                onclick="alert('Halaman toko akan segera tersedia.')"
                class="flex-1 rounded-xl border border-green-700 bg-white px-4 py-3 text-sm font-bold text-green-700 transition hover:bg-green-50">
                Lihat Toko
            </button>

            <button
                type="button"
                onclick="alert('Fitur chat penjual akan segera tersedia.')"
                class="flex-1 rounded-xl border border-green-700 bg-white px-4 py-3 text-sm font-bold text-green-700 transition hover:bg-green-50">
                Hubungi Penjual
            </button>
        </div>
    </div>

    {{-- Tombol Kembali ke Pesanan --}}
    <div>
        <a
            href="{{ route('pengusaha.pesanan') }}"
            class="block w-full rounded-xl border border-green-200 bg-white px-4 py-3 text-center text-sm font-bold text-green-800 transition hover:bg-green-50">
            Kembali ke Pesanan
        </a>
    </div>

</div>

@endsection
