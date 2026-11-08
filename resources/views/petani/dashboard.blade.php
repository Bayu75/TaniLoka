@extends('layouts.petani')

@section('main-class', 'w-full pb-24 pt-4 md:pb-10 md:pt-20')

@section('content')
@php
    $pesananTerbaru = [
        ['nomor' => 'TL-0012', 'pembeli' => 'Warung Bu Komang', 'produk' => 'Bayam Hijau Organik 10 kg', 'total' => 85000, 'tab' => 'menunggu', 'status' => 'Menunggu Konfirmasi', 'kelas' => 'bg-yellow-100 text-yellow-800'],
        ['nomor' => 'TL-0011', 'pembeli' => 'Resto Segara', 'produk' => 'Tomat Segar Grade A 20 kg, Kangkung Segar 5 kg', 'total' => 270000, 'tab' => 'diproses', 'status' => 'Diproses', 'kelas' => 'bg-blue-100 text-blue-800'],
        ['nomor' => 'TL-0010', 'pembeli' => 'Katering Sari Rasa', 'produk' => 'Tomat Segar Grade A 15 kg', 'total' => 180000, 'tab' => 'dikirim', 'status' => 'Dikirim', 'kelas' => 'bg-indigo-100 text-indigo-800'],
    ];
@endphp

<div class="pb-16">
    <div class="w-full bg-gradient-to-r from-green-800 to-green-600 text-white">
        <div class="max-w-4xl mx-auto px-4 pt-6 pb-20">
            <p class="text-sm opacity-90">Selamat datang</p>
            <h1 class="text-2xl font-bold">{{ session('nama', 'Pak Made Sukartha') }}</h1>
            <p class="text-sm opacity-90 mt-1">Dashboard Toko Anda</p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 -mt-14 space-y-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <a href="{{ url('/petani/produk') }}" class="bg-white border border-gray-200 rounded-2xl p-3 shadow-sm hover:border-green-600">
                <div class="text-lg">📦</div>
                <p class="text-lg font-bold mt-1">2</p>
                <p class="text-sm text-gray-500">Produk Aktif</p>
            </a>
            <a href="{{ url('/petani/pesanan') }}#menunggu" class="bg-white border border-gray-200 rounded-2xl p-3 shadow-sm hover:border-green-600">
                <div class="text-lg">🔔</div>
                <p class="text-lg font-bold mt-1">1</p>
                <p class="text-sm text-gray-500">Pesanan Baru</p>
            </a>
            <a href="{{ url('/petani/pesan') }}" class="bg-white border border-gray-200 rounded-2xl p-3 shadow-sm hover:border-green-600">
                <div class="text-lg">💬</div>
                <p class="text-lg font-bold mt-1">1</p>
                <p class="text-sm text-gray-500">Pesan Belum Dibaca</p>
            </a>
            <a href="{{ url('/petani/pesanan') }}#selesai" class="bg-white border border-gray-200 rounded-2xl p-3 shadow-sm hover:border-green-600">
                <div class="text-lg">💰</div>
                <p class="text-lg font-bold mt-1">Rp 68.000</p>
                <p class="text-sm text-gray-500">Pendapatan Bulan Ini</p>
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
                @foreach ($pesananTerbaru as $p)
                    <a href="{{ url('/petani/pesanan') }}#{{ $p['tab'] }}" class="flex items-center justify-between gap-3 bg-white border border-gray-200 rounded-2xl p-4 hover:border-green-600">
                        <div>
                            <p class="font-semibold text-sm">{{ $p['pembeli'] }}</p>
                            <p class="text-sm text-gray-500">{{ $p['produk'] }}</p>
                            <p class="text-sm font-bold text-green-700 mt-1">Rp {{ number_format($p['total'], 0, ',', '.') }}</p>
                        </div>
                        <span class="text-xs font-semibold px-3 py-1 rounded-full whitespace-nowrap {{ $p['kelas'] }}">{{ $p['status'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection