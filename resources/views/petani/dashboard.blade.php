@extends('layouts.petani')

@section('main-class', 'w-full pb-24 pt-0 md:pb-10 md:pt-15')

@section('content')
@php
    // Data dari Petani\DashboardController@index (database), bukan array dummy lagi.
    $rupiah = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');
    $kg = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, ',', '.'), '0'), ',');
@endphp

<div class="pb-16">
    <div class="w-full bg-gradient-to-r from-green-800 to-green-600 text-white">
        <div class="max-w-4xl mx-auto px-4 pt-6 pb-20">
            <p class="text-sm opacity-90">Selamat datang</p>
            <h1 class="text-2xl font-bold">{{ $namaPetani }}</h1>
            <p class="text-sm opacity-90 mt-1">Dashboard Toko Anda</p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 -mt-14 space-y-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <a href="{{ url('/petani/produk') }}" class="bg-white border border-gray-200 rounded-2xl p-3 shadow-sm hover:border-green-600">
                <div class="text-lg">📦</div>
                <p class="text-lg font-bold mt-1">{{ $statistik['produkAktif'] }}</p>
                <p class="text-sm text-gray-500">Produk Aktif</p>
            </a>
            <a href="{{ url('/petani/pesanan') }}#menunggu" class="bg-white border border-gray-200 rounded-2xl p-3 shadow-sm hover:border-green-600">
                <div class="text-lg">🔔</div>
                <p class="text-lg font-bold mt-1">{{ $statistik['pesananBaru'] }}</p>
                <p class="text-sm text-gray-500">Pesanan Baru</p>
            </a>
            <a href="{{ url('/petani/pesan') }}" class="bg-white border border-gray-200 rounded-2xl p-3 shadow-sm hover:border-green-600">
                <div class="text-lg">💬</div>
                <p class="text-lg font-bold mt-1">{{ $statistik['pesanBelumDibaca'] }}</p>
                <p class="text-sm text-gray-500">Pesan Belum Dibaca</p>
            </a>
            <a href="{{ url('/petani/pesanan') }}#selesai" class="bg-white border border-gray-200 rounded-2xl p-3 shadow-sm hover:border-green-600">
                <div class="text-lg">💰</div>
                <p class="text-lg font-bold mt-1">{{ $rupiah($statistik['pendapatanBulanIni']) }}</p>
                <p class="text-sm text-gray-500">Pendapatan Bulan Ini</p>
            </a>
        </div>

        <div class="grid grid-cols-3 gap-3">
            <a href="{{ url('/petani/produk') }}" class="bg-white border border-gray-200 rounded-2xl p-3 shadow-sm hover:border-green-600">
                <p class="text-lg font-bold">{{ $kg($statistik['totalStok']) }} kg</p>
                <p class="text-sm text-gray-500">Total Stok</p>
            </a>
            <a href="{{ url('/petani/pesanan') }}#diproses" class="bg-white border border-gray-200 rounded-2xl p-3 shadow-sm hover:border-green-600">
                <p class="text-lg font-bold">{{ $statistik['pesananDiproses'] }}</p>
                <p class="text-sm text-gray-500">Sedang Diproses</p>
            </a>
            <a href="{{ url('/petani/pesanan') }}#selesai" class="bg-white border border-gray-200 rounded-2xl p-3 shadow-sm hover:border-green-600">
                <p class="text-lg font-bold">{{ $rupiah($statistik['pendapatanTotal']) }}</p>
                <p class="text-sm text-gray-500">Total Pendapatan</p>
            </a>
        </div>

        <div>
            <h2 class="font-semibold mb-3">Aksi Cepat</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <a href="{{ url('/petani/produk') }}" class="bg-yellow-400 rounded-2xl p-4 flex flex-col items-center justify-center gap-2 font-semibold text-sm text-gray-900 hover:bg-yellow-500">
                    <span class="text-2xl">➕</span>Tambah Produk
                </a>
                <a href="{{ url('/petani/pesanan') }}" class="bg-white border border-gray-200 rounded-2xl p-4 flex flex-col items-center justify-center gap-2 font-semibold text-sm hover:border-green-600">
                    <span class="text-2xl">📋</span>Pesanan Masuk
                </a>
                <a href="{{ url('/petani/pesan') }}" class="bg-white border border-gray-200 rounded-2xl p-4 flex flex-col items-center justify-center gap-2 font-semibold text-sm hover:border-green-600">
                    <span class="text-2xl">💬</span>Pesan &amp; Negosiasi
                </a>
                <a href="{{ url('/petani/katalog') }}" class="bg-white border border-gray-200 rounded-2xl p-4 flex flex-col items-center justify-center gap-2 font-semibold text-sm hover:border-green-600">
                    <span class="text-2xl">🛒</span>Lihat Katalog Pasar
                </a>
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold">Pesanan Terbaru Masuk</h2>
                <a href="{{ url('/petani/pesanan') }}" class="text-sm font-medium text-green-700 hover:underline">Lihat Semua</a>
            </div>
            <div class="space-y-3">
                @forelse ($pesananTerbaru as $p)
                    <a href="{{ route('petani.pesanan.show', $p['id']) }}" class="flex items-center justify-between gap-3 bg-white border border-gray-200 rounded-2xl p-4 hover:border-green-600">
                        <div>
                            <p class="font-semibold text-sm">{{ $p['pembeli'] }}</p>
                            <p class="text-sm text-gray-500">#{{ $p['nomor'] }} · {{ $p['produk'] }}</p>
                            <p class="text-sm font-bold text-green-700 mt-1">Rp {{ number_format($p['total'], 0, ',', '.') }}</p>
                        </div>
                        <span class="text-xs font-semibold px-3 py-1 rounded-full whitespace-nowrap {{ $p['kelas'] }}">{{ $p['status'] }}</span>
                    </a>
                @empty
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 text-center text-sm text-gray-500">Belum ada pesanan masuk.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection