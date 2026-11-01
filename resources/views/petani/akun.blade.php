@extends('layouts.petani')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6 space-y-5 pb-24">
    <x-page-header judul="Akun" />

    <div class="bg-primary text-white rounded-2xl p-4 flex items-center gap-4">
        <div class="w-14 h-14 rounded-xl bg-yellow-400 text-primary flex items-center justify-center text-2xl font-bold">
            {{ strtoupper(substr(session('nama', 'P'), 0, 1)) }}
        </div>
        <div class="flex-1">
            <p class="font-semibold text-lg">{{ session('nama', 'Pak Made Sukartha') }}</p>
            <p class="text-sm text-white/90">{{ ucfirst(session('peran', 'petani')) }} · Tabanan, Bali</p>
        </div>
        <span class="text-xs bg-white/20 px-2.5 py-1 rounded-full flex items-center gap-1">✓ Terverifikasi</span>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <a href="{{ url('/petani/pesanan') }}" class="bg-white border border-border rounded-2xl p-4 text-center">
            <p class="text-2xl">📋</p>
            <p class="text-2xl font-semibold text-gray-900">5</p>
            <p class="text-sm text-text-muted">Pesanan Masuk</p>
        </a>
        <a href="{{ url('/petani/pesanan') }}#selesai" class="bg-white border border-border rounded-2xl p-4 text-center">
            <p class="text-2xl">☑️</p>
            <p class="text-2xl font-semibold text-gray-900">2</p>
            <p class="text-sm text-text-muted">Selesai</p>
        </a>
    </div>

    <div>
        <p class="text-xs font-semibold text-text-muted tracking-wide mb-2">AKUN SAYA</p>
        <a href="{{ url('/petani/alamat') }}" class="bg-white border border-border rounded-2xl p-4 flex items-center gap-3">
            <span class="text-xl">📍</span>
            <div class="flex-1">
                <p class="font-semibold text-gray-900">Alamat Saya</p>
                <p class="text-sm text-text-muted">1 alamat tersimpan</p>
            </div>
            <span class="text-text-muted">›</span>
        </a>
    </div>

    <div>
        <p class="text-xs font-semibold text-text-muted tracking-wide mb-2">AKTIVITAS</p>
        <div class="bg-white border border-border rounded-2xl divide-y divide-border overflow-hidden">
            <a href="{{ url('/petani/pesanan') }}" class="flex items-center gap-3 p-4">
                <span class="text-xl">📋</span>
                <div class="flex-1">
                    <p class="font-semibold text-gray-900">Pesanan</p>
                    <p class="text-sm text-text-muted">5 pesanan</p>
                </div>
                <span class="text-text-muted">›</span>
            </a>
            <a href="{{ url('/petani/pesan') }}" class="flex items-center gap-3 p-4">
                <span class="text-xl">💬</span>
                <div class="flex-1">
                    <p class="font-semibold text-gray-900">Pesan</p>
                    <p class="text-sm text-text-muted">Negosiasi harga & percakapan</p>
                </div>
                <span class="text-text-muted">›</span>
            </a>
            <a href="{{ url('/petani/produk') }}" class="flex items-center gap-3 p-4">
                <span class="text-xl">🛒</span>
                <div class="flex-1">
                    <p class="font-semibold text-gray-900">Kelola Produk</p>
                    <p class="text-sm text-text-muted">Produk, stok, dan harga</p>
                </div>
                <span class="text-text-muted">›</span>
            </a>
        </div>
    </div>

    <div>
        <p class="text-xs font-semibold text-text-muted tracking-wide mb-2">BANTUAN & LAINNYA</p>
        <a href="{{ url('/petani/bantuan') }}" class="bg-white border border-border rounded-2xl p-4 flex items-center gap-3">
            <span class="text-xl text-red-500">❓</span>
            <div class="flex-1">
                <p class="font-semibold text-gray-900">Bantuan & Informasi</p>
                <p class="text-sm text-text-muted">WhatsApp, pertanyaan umum, kontak</p>
            </div>
            <span class="text-text-muted">›</span>
        </a>
    </div>

    <a href="{{ url('/masuk') }}" class="block w-full text-center border border-red-300 text-red-600 rounded-xl py-3 font-medium">Keluar</a>
    <p class="text-center text-xs text-text-muted">TaniLoka v1.0.0 MVP · Dibuat di Bali</p>
</div>
@endsection