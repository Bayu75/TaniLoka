@extends('layouts.pengusaha')

@section('title', 'Pembayaran - TaniLoka')

@php
    $metodeAwal = request('pembayaran', 'transfer');
    if (! in_array($metodeAwal, ['transfer', 'gopay', 'ovo'], true)) {
        $metodeAwal = 'transfer';
    }
@endphp

@section('content')

<x-page-header
    judul="Pembayaran"
    :kembali="route('pengusaha.checkout')"
    lebar="max-w-4xl"
/>

<div class="mx-auto w-full max-w-4xl space-y-6">

    <div class="relative left-1/2 w-screen -translate-x-1/2 border-b border-green-200 bg-white">
        <div class="mx-auto flex max-w-4xl items-center justify-center gap-3 px-4 py-3">
            <div class="flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-green-700 text-lg font-bold text-white">
                        ✓
                    </span>
                    <span class="text-sm font-semibold text-gray-600">Pengiriman</span>
                </div>

                <div class="h-0.5 w-20 bg-green-700"></div>

                <div class="flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-500 text-sm font-bold text-black">2</span>
                    <span class="text-sm font-semibold text-gray-800">Pembayaran</span>
                </div>

                <div class="h-0.5 w-20 bg-gray-200"></div>

                <div class="flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-200 text-sm font-bold text-gray-500">3</span>
                    <span class="text-sm font-semibold text-gray-600">Konfirmasi</span>
                </div>
            </div>
        </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_266px]">

        {{-- Kolom Kiri: Metode Pembayaran --}}
        <div class="min-w-0" x-data="{ metodeBayar: @js($metodeAwal) }">
            <div class="rounded-2xl border border-green-100 bg-white p-5">
                <h2 class="text-lg font-bold text-gray-800">
                    Metode Pembayaran
                </h2>

                <div class="mt-5 space-y-2">

                    {{-- Transfer Bank --}}
                    <button
                        type="button"
                        @click="metodeBayar = 'transfer'"
                        :class="metodeBayar === 'transfer' ? 'border-green-700 bg-green-50' : 'border-green-100 bg-white'"
                        class="flex w-full items-center gap-3 rounded-xl border-2 p-3 text-left"
                    >
                        <span class="flex h-6 w-6 items-center justify-center rounded-full border border-green-700">
                            <span x-show="metodeBayar === 'transfer'" class="h-3 w-3 rounded-full bg-green-700"></span>
                        </span>
                                                                       <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#005BAA] text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10l9-6 9 6M5 10v8m4-8v8m6-8v8m4-8v8M3 20h18" />
                            </svg>
                        </span>
                        <span class="flex-1">
                            <span class="block font-bold text-gray-800">Transfer Bank</span>
                            <span class="block text-sm text-gray-500">BCA, Mandiri, BRI, BNI</span>
                        </span>
                    </button>

                    {{-- GoPay --}}
                    <button
                        type="button"
                        @click="metodeBayar = 'gopay'"
                        :class="metodeBayar === 'gopay' ? 'border-green-700 bg-green-50' : 'border-green-100 bg-white'"
                        class="flex w-full items-center gap-3 rounded-xl border-2 p-3 text-left"
                    >
                        <span class="flex h-6 w-6 items-center justify-center rounded-full border border-gray-400">
                            <span x-show="metodeBayar === 'gopay'" class="h-3 w-3 rounded-full bg-green-700"></span>
                        </span>
                                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#00AED6] text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h12a2 2 0 012 2v1h1a1 1 0 011 1v6a1 1 0 01-1 1h-1v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                <circle cx="16" cy="12" r="1.2" fill="currentColor" />
                            </svg>
                        </span>
                        <span class="flex-1">
                            <span class="block font-bold text-gray-800">GoPay</span>
                            <span class="block text-sm text-gray-500">Bayar dengan saldo GoPay</span>
                        </span>
                    </button>

                    {{-- OVO --}}
                    <button
                        type="button"
                        @click="metodeBayar = 'ovo'"
                        :class="metodeBayar === 'ovo' ? 'border-green-700 bg-green-50' : 'border-green-100 bg-white'"
                        class="flex w-full items-center gap-3 rounded-xl border-2 p-3 text-left"
                    >
                        <span class="flex h-6 w-6 items-center justify-center rounded-full border border-gray-400">
                            <span x-show="metodeBayar === 'ovo'" class="h-3 w-3 rounded-full bg-green-700"></span>
                        </span>
                                               <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#4C3494] text-xs font-extrabold tracking-wide text-white">
                            OVO
                        </span>
                        <span class="flex-1">
                            <span class="block font-bold text-gray-800">OVO</span>
                            <span class="block text-sm text-gray-500">Bayar dengan saldo OVO</span>
                        </span>
                    </button>
                </div>
            </div>

            {{-- Informasi Pembayaran --}}
            <div class="mt-4 rounded-2xl border border-green-200 bg-green-50 p-4">
                <p class="text-sm leading-relaxed text-green-800">
                    <span class="font-bold">Kapan saya membayar?</span>
                    Pembayaran dilakukan setelah petani mengonfirmasi pesanan.
                    Instruksi dan unggah bukti ada di Detail Pesanan.
                </p>
            </div>

            <a
                :href="'{{ route('pengusaha.konfirmasi', [
                    'alamat_id' => request('alamat_id'),
                    'pengiriman' => request('pengiriman'),
                ]) }}&pembayaran=' + encodeURIComponent(metodeBayar)"
                class="mt-4 block w-full rounded-xl bg-amber-500 px-4 py-4 text-center font-bold text-gray-900 transition hover:bg-amber-600"
            >
                Tinjau Pesanan
            </a>

        </div>



        {{-- Kolom Kanan: Ringkasan --}}
        <div class="self-start lg:sticky lg:top-24">
            <div class="rounded-2xl border border-green-100 bg-white p-4">

                <h2 class="mb-4 text-lg font-bold text-gray-800">
                    Ringkasan
                </h2>

                {{-- Daftar Produk --}}
                <div class="space-y-3 border-b border-gray-200 pb-3">
                    @foreach ($items as $item)
                        <div class="flex items-center justify-between gap-2">
                            <p class="min-w-0 text-sm text-gray-600">
                                {{ $item->produk->nama }}
                                ({{ rtrim(rtrim(number_format($item->jumlah_kg, 2, ',', '.'), '0'), ',') }} kg)
                            </p>

                            <p class="shrink-0 text-sm font-bold text-gray-800">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </p>
                        </div>
                    @endforeach
                </div>

                {{-- Subtotal, Ongkir, dan PPN --}}
                <div class="mt-3 space-y-2 text-sm">

                    {{-- Subtotal --}}
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="text-gray-700">
                            Rp {{ number_format($subtotal, 0, ',', '.') }}
                        </span>
                    </div>

                    {{-- Ongkos Kirim --}}
                    <div class="flex justify-between">
                        <span class="text-gray-600">Ongkos kirim</span>
                        <span class="text-gray-700">
                            {{ $totalOngkir === 0 ? 'Gratis' : 'Rp ' . number_format($totalOngkir, 0, ',', '.') }}
                        </span>
                    </div>

                    {{-- PPN 10% --}}
                    <div class="flex justify-between">
                        <span class="text-gray-600">PPN (10%)</span>
                        <span class="text-gray-700">
                            Rp {{ number_format($totalPPN, 0, ',', '.') }}
                        </span>
                    </div>

                </div>

                {{-- Total --}}
                <div class="mt-3 flex justify-between border-t border-gray-200 pt-3">
                    <span class="font-bold text-primary">Total</span>
                    <span class="font-extrabold text-primary">
                        Rp {{ number_format($totalPembayaran, 0, ',', '.') }}
                    </span>
                </div>

            </div>
        </div>



    </div>

</div>

@endsection
