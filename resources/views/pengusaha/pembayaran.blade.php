@extends('layouts.pengusaha')

@section('title', 'Pembayaran - TaniLoka')

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

        {{-- Kolom Kiri: Info Pembayaran (Midtrans) --}}
        <div class="min-w-0">
            <div class="rounded-2xl border border-green-100 bg-white p-5">
                <h2 class="text-lg font-bold text-gray-800">
                    Metode Pembayaran
                </h2>

                <div class="mt-4 flex items-start gap-3 rounded-xl border-2 border-green-700 bg-green-50 p-4">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-700 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2zm2 9h4" />
                        </svg>
                    </span>
                    <span class="flex-1">
                        <span class="block font-bold text-gray-800">Pembayaran Online (Midtrans)</span>
                        <span class="mt-1 block text-sm text-gray-500">
                            Virtual Account (BCA, Mandiri, BRI, BNI), QRIS, GoPay, ShopeePay, kartu kredit/debit, dan lainnya.
                            Metode dipilih saat membayar.
                        </span>
                    </span>
                </div>
            </div>

            {{-- Informasi Pembayaran --}}
            <div class="mt-4 rounded-2xl border border-green-200 bg-green-50 p-4">
                <p class="text-sm leading-relaxed text-green-800">
                    <span class="font-bold">Kapan saya membayar?</span>
                    Pembayaran dilakukan setelah petani mengonfirmasi pesanan (maks. 24 jam).
                    Tombol <span class="font-semibold">Bayar Sekarang</span> akan muncul di Detail Pesanan.
                </p>
            </div>

            <a
                href="{{ route('pengusaha.konfirmasi', [
                    'alamat_id' => request('alamat_id'),
                    'pengiriman' => request('pengiriman'),
                ]) }}"
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
