
@extends('layouts.pengusaha')

@section('title', 'Pembayaran - TaniLoka')

@section('content')

<div class="relative left-1/2 -mt-4 w-screen -translate-x-1/2 border-b border-green-200 bg-white">
    <div class="mx-auto max-w-4xl px-4 py-5">
        <div class="flex items-center gap-5">
            <a href="{{ route('pengusaha.checkout') }}"
                class="flex items-center gap-2 text-sm font-semibold text-primary hover:text-green-800">
                <span class="text-2xl leading-none">‹</span>
                Kembali
            </a>

            <h1 class="text-xl font-extrabold text-gray-800">
                Pembayaran
            </h1>
        </div>
    </div>
</div>

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
        <div class="min-w-0">
            <div
                x-data="{ metodeBayar: 'transfer' }"
                class="rounded-2xl border border-green-100 bg-white p-5"
            >
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
                        <span class="text-xl">🏦</span>
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
                        <span class="text-xl">💚</span>
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
                        <span class="text-xl">💜</span>
                        <span class="flex-1">
                            <span class="block font-bold text-gray-800">OVO</span>
                            <span class="block text-sm text-gray-500">Bayar dengan saldo OVO</span>
                        </span>
                    </button>

                    {{-- COD --}}
                    <button
                        type="button"
                        @click="metodeBayar = 'cod'"
                        :class="metodeBayar === 'cod' ? 'border-green-700 bg-green-50' : 'border-green-100 bg-white'"
                        class="flex w-full items-center gap-3 rounded-xl border-2 p-3 text-left"
                    >
                        <span class="flex h-6 w-6 items-center justify-center rounded-full border border-gray-400">
                            <span x-show="metodeBayar === 'cod'" class="h-3 w-3 rounded-full bg-green-700"></span>
                        </span>
                        <span class="text-xl">💵</span>
                        <span class="flex-1">
                            <span class="block font-bold text-gray-800">COD (Bayar di Tempat)</span>
                            <span class="block text-sm text-gray-500">Khusus pengiriman mandiri</span>
                        </span>
                    </button>

                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Ringkasan --}}
        <div class="self-start lg:sticky lg:top-24">
            <div class="rounded-2xl border border-green-100 bg-white p-4">

                <h2 class="mb-4 text-lg font-bold text-gray-800">
                    Ringkasan
                </h2>

                <div class="flex items-center justify-between gap-2 border-b border-gray-200 pb-3">
                    <p class="truncate text-sm text-gray-600">
                        Cabai Rawit Merah
                    </p>

                    <p class="shrink-0 text-sm font-bold text-gray-800">
                        Rp 90.000
                    </p>
                </div>

                <div class="mt-3 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="text-gray-700">Rp 90.000</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-600">Ongkos kirim</span>
                        <span class="text-gray-700">Gratis</span>
                    </div>
                </div>

                <div class="mt-3 flex justify-between border-t border-gray-200 pt-3">
                    <span class="font-bold text-primary">Total</span>
                    <span class="font-extrabold text-primary">Rp 90.000</span>
                </div>

            </div>
        </div>

    </div>

</div>

@endsection
