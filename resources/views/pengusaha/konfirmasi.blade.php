@extends('layouts.pengusaha')

@section('title', 'Konfirmasi Pesanan - TaniLoka')

@php
    $namaPembayaran = [
        'transfer' => 'Transfer Bank',
        'gopay' => 'GoPay',
        'ovo' => 'OVO',
        'cod' => 'COD (Bayar di Tempat)',
    ];

    $labelPengiriman = [
        'kendaraan' => 'Kendaraan Petani',
    ];

    $formatRupiah = fn ($nilai) => 'Rp' . number_format($nilai, 0, ',', '.');
@endphp

@section('content')

<x-page-header
    judul="Konfirmasi Pesanan"
    :kembali="route('pengusaha.pembayaran', [
        'alamat_id' => $alamatAktif->id,
        'pengiriman' => json_encode($pengiriman),
        'pembayaran' => $metodeBayar,
    ])"
    lebar="max-w-4xl"
/>

<div class="mx-auto w-full max-w-4xl">

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

            {{-- Detail Pesanan per Petani --}}
            @foreach ($perPetani as $petaniId => $produkPetani)
                @php
                    $petani = $produkPetani->first()->produk->petani;
                    $subtotalPetani = $produkPetani->sum(fn ($item) => $item->subtotal);

                    $pilihan = $pengiriman[$petaniId] ?? [
                        'metode' => 'diantar',
                        'kurir' => 'kendaraan',
                    ];

                    $ambilSendiri = ($pilihan['metode'] ?? 'diantar') === 'ambil';

                    $ongkirPetani = $ambilSendiri ? 0 : 15000;
                @endphp

                <div class="overflow-hidden rounded-2xl border border-green-200 bg-white">

                    {{-- Informasi Penjual --}}
                    <div class="border-b border-green-200 bg-green-50 px-5 py-4">
                        <h2 class="font-bold text-gray-900">
                            {{ $petani->profilPetani->nama ?? $petani->name ?? 'Petani' }}
                        </h2>

                        <p class="mt-1 text-sm text-gray-600">
                            {{ $produkPetani->count() }} produk ·
                            {{ $formatRupiah($subtotalPetani) }}
                        </p>
                    </div>

                    {{-- Informasi Produk --}}
                    @foreach ($produkPetani as $item)
                        @php
                            $produk = $item->produk;
                            $foto = $produk->fotoUtama?->url;
                        @endphp

                        <div class="flex items-center justify-between gap-3 border-b border-gray-100 p-5 last:border-b-0">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="h-14 w-14 shrink-0 overflow-hidden rounded-xl bg-green-100">
                                    @if ($foto)
                                        <img
                                            src="{{ $foto }}"
                                            alt="{{ $produk->nama }}"
                                            class="h-full w-full object-cover"
                                        >
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-xs text-green-700">
                                            Produk
                                        </div>
                                    @endif
                                </div>

                                <div class="min-w-0">
                                    <h3 class="font-bold text-gray-900">
                                        {{ $produk->nama }}
                                    </h3>

                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ number_format($item->jumlah_kg, 0, ',', '.') }} kg
                                        ×
                                        {{ $formatRupiah($item->harga_per_kg ?? $produk->hargaAktif()) }}
                                    </p>
                                </div>
                            </div>

                            <p class="shrink-0 font-bold text-gray-900">
                                {{ $formatRupiah($item->subtotal) }}
                            </p>
                        </div>
                    @endforeach

                    {{-- Subtotal dan Ongkir --}}
                    <div class="space-y-2 border-t border-gray-200 px-5 py-4 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="text-gray-700">
                                {{ $formatRupiah($subtotalPetani) }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">Ongkos kirim</span>
                            <span class="text-gray-700">
                                {{ $ongkirPetani === 0 ? 'Gratis' : $formatRupiah($ongkirPetani) }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">Pengiriman</span>
                            <span class="font-medium text-gray-900">
                                {{ $ambilSendiri ? 'Ambil Sendiri' : 'Kendaraan Petani' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Alamat dan Informasi Pembayaran --}}
            <div class="rounded-2xl border border-green-100 bg-white p-4">

                <h2 class="mb-3 font-bold text-gray-900">
                    Informasi Pengiriman
                </h2>

                <p class="text-sm text-gray-600">
                    Penerima:
                    <span class="font-medium text-gray-900">
                        {{ $alamatAktif->nama_penerima }}
                    </span>
                </p>

                <p class="mt-2 text-sm text-gray-600">
                    No. HP:
                    <span class="font-medium text-gray-900">
                        {{ $alamatAktif->no_hp }}
                    </span>
                </p>

                <p class="mt-2 text-sm text-gray-600">
                    Alamat:
                    <span class="font-medium text-gray-900">
                        {{ $alamatAktif->alamat_lengkap }},
                        {{ $alamatAktif->kecamatan }},
                        {{ $alamatAktif->kabupaten }}
                    </span>
                </p>

                <p class="mt-3 text-sm text-gray-600">
                    Metode bayar:
                    <span class="font-medium text-gray-900">
                        {{ $namaPembayaran[$metodeBayar] ?? 'Transfer Bank' }}
                    </span>
                </p>

                <p class="mt-2 text-sm leading-relaxed text-gray-600">
                    Bayar setelah petani mengonfirmasi pesanan (maks. 24 jam).
                    Pesanan yang melewati batas waktu akan dibatalkan otomatis.
                </p>
            </div>

            {{-- Tombol Kirim Pesanan --}}
            <form action="{{ route('pengusaha.konfirmasi.store') }}" method="POST">
                @csrf

                <input type="hidden" name="alamat_id" value="{{ $alamatAktif->id }}">
                <input type="hidden" name="pengiriman" value="{{ json_encode($pengiriman) }}">
                <input type="hidden" name="pembayaran" value="{{ $metodeBayar }}">

                <button
                    type="submit"
                    class="w-full rounded-xl bg-amber-500 px-4 py-4 text-center font-bold text-gray-900 transition hover:bg-amber-600"
                >
                    Kirim Pesanan · {{ $formatRupiah($totalPembayaran) }}
                </button>
            </form>

        </div>

        {{-- Kolom Kanan: Ringkasan --}}
        <div class="self-start lg:sticky lg:top-24">
            <div class="rounded-2xl border border-green-100 bg-white p-4">

                <h2 class="mb-4 text-lg font-bold text-gray-800">
                    Ringkasan
                </h2>

                <div class="space-y-2 text-sm">
                    @foreach ($items as $item)
                        <div class="flex items-center justify-between gap-2">
                            <p class="min-w-0 text-sm text-gray-600">
                                {{ $item->produk->nama }}
                                ({{ rtrim(rtrim(number_format($item->jumlah_kg, 2, ',', '.'), '0'), ',') }} kg)
                            </p>

                            <p class="shrink-0 font-bold text-gray-800">
                                {{ $formatRupiah($item->subtotal) }}
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 space-y-2 border-t border-gray-200 pt-3 text-sm">
                    {{-- Subtotal --}}
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="text-gray-700">
                            {{ $formatRupiah($subtotal) }}
                        </span>
                    </div>

                    {{-- Ongkos kirim --}}
                    <div class="flex justify-between">
                        <span class="text-gray-600">Ongkos kirim</span>
                        <span class="text-gray-700">
                            {{ $totalOngkir === 0 ? 'Gratis' : $formatRupiah($totalOngkir) }}
                        </span>
                    </div>

                    {{-- PPN 10% --}}
                    <div class="flex justify-between">
                        <span class="text-gray-600">PPN (10%)</span>
                        <span class="text-gray-700">
                            {{ $formatRupiah($totalPPN) }}
                        </span>
                    </div>
                </div>

                <div class="mt-3 flex justify-between border-t border-gray-200 pt-3">
                    <span class="font-bold text-primary">Total</span>
                    <span class="font-extrabold text-primary">
                        {{ $formatRupiah($totalPembayaran) }}
                    </span>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection
