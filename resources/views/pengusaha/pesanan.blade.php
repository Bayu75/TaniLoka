@extends('layouts.pengusaha')

@section('title', 'Pesanan Saya - TaniLoka')

@section('content')

<x-page-header
    judul="Pesanan"
    :kembali="false"
    lebar="max-w-[600px]"
/>

<div class="w-full space-y-5" x-data="{ filter: 'semua' }">

    {{-- Deskripsi --}}
    <div class="mx-auto max-w-[600px] px-4 pt-2">
        <p class="text-sm text-gray-500">
            Pantau status dan riwayat pesanan produk Anda.
        </p>
    </div>

    {{-- Filter --}}
    <div class="mx-auto max-w-[600px] px-4">
        <div class="flex items-center gap-3 overflow-x-auto pb-3">
            @php
                $filterList = [
                    'semua' => 'Semua',
                    'menunggu' => 'Menunggu',
                    'diproses' => 'Diproses',
                    'dikirim' => 'Dikirim',
                    'selesai' => 'Selesai',
                    'dibatalkan' => 'Dibatalkan',
                ];
            @endphp

            @foreach ($filterList as $key => $label)
                <button
                    type="button"
                    @click="filter = '{{ $key }}'"
                    :class="filter === '{{ $key }}'
                        ? 'bg-green-700 text-white border-green-700'
                        : 'bg-white text-gray-800 border-green-200 hover:bg-green-50'"
                    class="shrink-0 rounded-full border px-5 py-2.5 text-sm font-bold transition">
                    {{ $label }} ({{ $jumlahPesanan[$key] ?? 0 }})
                </button>
            @endforeach
        </div>
    </div>

    {{-- Daftar Pesanan --}}
    <div class="mx-auto flex max-w-[640px] flex-col gap-5">

        @forelse ($pesanan as $item)

            @php
                $status = $item->status;

                $namaPetani = $item->petani?->profilPetani?->nama_usaha_tani
                    ?: ($item->petani?->name ?? '-');

                $produkPertama = $item->item->first();
                $produk = $produkPertama?->produk;

                $jumlahKg = (float) $item->item->sum('jumlah_kg');

                $jumlahLabel = rtrim(
                    rtrim(number_format($jumlahKg, 2, ',', '.'), '0'),
                    ','
                );

                $namaProduk = $produkPertama?->nama_produk ?? 'Produk';

                $gambar = $produk?->fotoUtama?->url;

                $statusInfo = match ($status) {
                    'menunggu_konfirmasi' => [
                        'label' => 'Menunggu Konfirmasi',
                        'icon' => '⏳',
                        'header' => 'bg-yellow-100',
                        'text' => 'text-yellow-800',
                        'badge' => 'bg-yellow-50 text-yellow-800 border-yellow-200',
                    ],
                    'menunggu_pembayaran' => [
                        'label' => 'Menunggu Pembayaran',
                        'icon' => '💳',
                        'header' => 'bg-orange-100',
                        'text' => 'text-orange-800',
                        'badge' => 'bg-orange-50 text-orange-800 border-orange-200',
                    ],
                    'diproses' => [
                        'label' => 'Diproses',
                        'icon' => '📦',
                        'header' => 'bg-blue-100',
                        'text' => 'text-blue-800',
                        'badge' => 'bg-blue-50 text-blue-800 border-blue-200',
                    ],
                    'dikirim' => [
                        'label' => 'Dikirim',
                        'icon' => '🚚',
                        'header' => 'bg-green-100',
                        'text' => 'text-green-800',
                        'badge' => 'bg-green-50 text-green-800 border-green-200',
                    ],
                    'siap_diambil' => [
                        'label' => 'Siap Diambil',
                        'icon' => '📦',
                        'header' => 'bg-green-100',
                        'text' => 'text-green-800',
                        'badge' => 'bg-green-50 text-green-800 border-green-200',
                    ],
                    'terkirim' => [
                        'label' => 'Terkirim',
                        'icon' => '🚚',
                        'header' => 'bg-green-100',
                        'text' => 'text-green-800',
                        'badge' => 'bg-green-50 text-green-800 border-green-200',
                    ],
                    'selesai' => [
                        'label' => 'Selesai',
                        'icon' => '🎉',
                        'header' => 'bg-emerald-100',
                        'text' => 'text-emerald-800',
                        'badge' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                    ],
                    'dibatalkan', 'ditolak' => [
                        'label' => $status === 'ditolak' ? 'Ditolak' : 'Dibatalkan',
                        'icon' => '❌',
                        'header' => 'bg-red-100',
                        'text' => 'text-red-800',
                        'badge' => 'bg-red-50 text-red-800 border-red-200',
                    ],
                    default => [
                        'label' => ucfirst(str_replace('_', ' ', $status)),
                        'icon' => '📋',
                        'header' => 'bg-gray-100',
                        'text' => 'text-gray-800',
                        'badge' => 'bg-gray-50 text-gray-800 border-gray-200',
                    ],
                };

                $kategoriFilter = match ($status) {
                    'menunggu_konfirmasi', 'menunggu_pembayaran' => 'menunggu',
                    'dikirim', 'siap_diambil', 'terkirim' => 'dikirim',
                    'dibatalkan', 'ditolak' => 'dibatalkan',
                    default => $status,
                };

                $tanggal = $item->created_at?->format('d M Y') ?? '-';

                $deadline = $item->created_at?->copy()->addHours(24);
                $sisaDetik = $deadline
                    ? max(0, now()->diffInSeconds($deadline, false))
                    : 0;

                $notifikasi = match ($status) {
                    'menunggu_konfirmasi' => 'Batas konfirmasi: '
                        . floor($sisaDetik / 3600) . ' jam '
                        . floor(($sisaDetik % 3600) / 60) . ' menit',
                    'menunggu_pembayaran' => 'Batas pembayaran: '
                        . floor($sisaDetik / 3600) . ' jam '
                        . floor(($sisaDetik % 3600) / 60) . ' menit',
                    'diproses' => 'Pesanan sedang diproses',
                    'dikirim' => 'Pesanan sedang dikirim',
                    'siap_diambil' => 'Pesanan siap diambil',
                    'terkirim' => 'Pesanan telah diterima kurir',
                    default => null,
                };
            @endphp

            <div
                x-show="filter === 'semua' || filter === '{{ $kategoriFilter }}'"
                x-transition
                class="overflow-hidden rounded-2xl border border-green-200 bg-white shadow-sm">

                {{-- Header Kartu --}}
                <div class="flex items-center gap-4 border-b border-green-200 p-4 {{ $statusInfo['header'] }}">

                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white text-3xl">
                        {{ $statusInfo['icon'] }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-extrabold {{ $statusInfo['text'] }}">
                            {{ $statusInfo['label'] }}
                        </p>

                        <h3 class="mt-1 font-bold text-gray-900">
                            {{ $namaPetani }}
                        </h3>

                        <p class="mt-1 text-sm text-gray-600">
                            #{{ $item->kode }} · {{ $tanggal }}
                        </p>
                    </div>
                </div>

                {{-- Informasi Produk --}}
                <div class="space-y-4 p-4">

                    <div class="flex items-center gap-3">
                        <img
                            src="{{ $gambar ?? '' }}"
                            alt="{{ $namaProduk }}"
                            class="h-14 w-14 shrink-0 rounded-xl object-cover"
                        >

                        <div class="min-w-0">
                            <h4 class="text-sm font-bold text-gray-900">
                                {{ $namaProduk }}
                            </h4>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ $jumlahLabel }} kg
                            </p>
                        </div>
                    </div>

                    {{-- Total --}}
                    <p class="text-base font-extrabold text-green-700">
                        Total: Rp{{ number_format($item->total, 0, ',', '.') }}
                    </p>

                    {{-- Notifikasi --}}
                    @if ($notifikasi && !in_array($status, ['dibatalkan', 'ditolak', 'selesai']))
                        <div class="inline-flex items-center gap-2 rounded-xl bg-amber-50 px-3 py-2 text-sm font-bold text-amber-800">
                            <span>⏱️</span>
                            {{ $notifikasi }}
                        </div>
                    @endif

                </div>

                {{-- Tombol Detail --}}
                <div class="px-4 pb-4">
                    <a
                        href="{{ route('pengusaha.pesanan.detail', $item->id) }}"
                        class="block w-full rounded-2xl bg-green-700 px-5 py-3 text-center text-sm font-extrabold text-white transition hover:bg-green-800">
                        Lihat Detail
                    </a>
                </div>

            </div>

        @empty

            <div class="rounded-2xl border border-green-200 bg-white px-5 py-12 text-center">
                <div class="mb-3 text-4xl">🛒</div>
                <h3 class="font-bold text-gray-800">
                    Belum Ada Pesanan
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Pesanan Anda akan muncul di halaman ini.
                </p>
            </div>

        @endforelse

    </div>

</div>

@endsection
