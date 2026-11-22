@extends('layouts.petani')

@section('title', 'Akun - TaniLoka')

@section('content')

@php
    $user    = auth()->user();
    $profil  = $user->profilPetani;
    $nama    = $profil?->nama_usaha_tani ?: $user->name;
    $inisial = strtoupper(mb_substr($nama, 0, 1));
    $kota = $user->alamatUtama?->kabupaten ?? 'Bali';

    $jumlahProduk  = $user->produk()->count();
    $jumlahPesanan = $user->pesananMasuk()->count();

    $menuAkun = [
        ['url' => route('petani.profil'), 'ikon' => '👤', 'judul' => 'Profil Petani', 'ket' => 'Informasi usaha tani dan rekening'],
        ['url' => route('petani.alamat'), 'ikon' => '📍', 'judul' => 'Alamat Kebun', 'ket' => 'Lokasi kebun dan pengambilan barang'],
    ];
    $menuAktivitas = [
        ['url' => route('petani.produk.index'),  'ikon' => '🌱', 'judul' => 'Kelola Produk', 'ket' => 'Tambah dan ubah produk jualan'],
        ['url' => route('petani.pesanan'), 'ikon' => '📦', 'judul' => 'Pesanan Masuk', 'ket' => 'Pesanan dari pengusaha kuliner'],
        ['url' => route('petani.pesan'),   'ikon' => '💬', 'judul' => 'Pesan', 'ket' => 'Negosiasi harga dan percakapan'],
    ];
    $menuBantuan = [
        ['url' => route('petani.bantuan'), 'ikon' => '❓', 'judul' => 'Bantuan & Informasi', 'ket' => 'WhatsApp, pertanyaan umum, kontak'],
    ];
    $grup = ['Akun Saya' => $menuAkun, 'Aktivitas' => $menuAktivitas, 'Bantuan & Lainnya' => $menuBantuan];

    $badgeVerifikasi = [
        'terverifikasi'   => ['Terverifikasi',   'bg-green-50 text-[#2E7D32]'],
        'menunggu'        => ['Menunggu',        'bg-gray-100 text-gray-600'],
        'perlu_perbaikan' => ['Perlu Perbaikan', 'bg-orange-50 text-orange-700'],
        'ditolak'         => ['Ditolak',         'bg-red-50 text-red-600'],
        'belum_diajukan'  => ['Ajukan',          'bg-[#F5C400]/20 text-yellow-700'],
    ];
    
    [$teksBadge, $warnaBadge] = $badgeVerifikasi[$user->status_verifikasi] ?? $badgeVerifikasi['belum_diajukan'];
    
    $keteranganVerifikasi = match ($user->status_verifikasi) {
        'terverifikasi'   => 'Akun kamu sudah terverifikasi dan bisa menjual produk',
        'menunggu'        => 'Dokumen sedang ditinjau admin',
        'perlu_perbaikan' => 'Ada data yang perlu diperbaiki, cek catatan admin',
        'ditolak'         => 'Pengajuan ditolak, silakan ajukan ulang',
        default           => 'Lengkapi data dan dokumen agar bisa menjual produk',
    };
@endphp

{{-- HEADER HALAMAN AKUN --}}
<x-page-header judul="Akun" :kembali="false" lebar="max-w-4xl"/>

<div class="mx-auto max-w-4xl py-5 md:py-6">

    @if (session('pesan_akun'))
        <div class="mb-5 rounded-xl bg-green-50 px-4 py-3 text-sm text-[#2E7D32]">{{ session('pesan_akun') }}</div>
    @endif

    {{-- PROFIL PETANI --}}
    <div class="mb-5 flex items-center gap-4 rounded-2xl bg-[#2E7D32] p-4 text-white shadow-sm">
        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-[#F5C400] text-2xl font-extrabold text-[#2E7D32]">
            {{ $inisial }}
        </div>
        <div class="min-w-0 flex-1">
            <h2 class="truncate text-lg font-bold">{{ $nama }}</h2>
            <p class="text-sm text-green-100">Petani</p>
            <p class="mt-1 text-xs text-green-100">{{ $user->name }} · {{ $kota }}</p>
        </div>
        <span class="shrink-0 rounded-full bg-white/20 px-3 py-1 text-xs font-semibold">Akun Petani</span>
    </div>

    {{-- VERIFIKASI AKUN --}}
    <a href="{{ route('petani.verifikasi') }}"
    class="mb-5 flex items-center justify-between gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
        <div>
            <p class="font-semibold text-gray-800">Verifikasi Akun</p>
            <p class="text-xs text-gray-500">{{ $keteranganVerifikasi }}</p>
        </div>
        <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ $warnaBadge }}">
            {{ $teksBadge }}
        </span>
    </a>

    {{-- STATISTIK --}}
    <div class="mb-6 grid grid-cols-2 gap-3">
        <a href="{{ route('petani.produk.index') }}"
           class="rounded-2xl border border-[#DCE8D8] bg-white p-5 text-center transition hover:border-[#2E7D32]">
            <div class="mb-2 text-2xl">🌱</div>
            <h3 class="text-2xl font-extrabold text-[#1A2A1A]">{{ $jumlahProduk }}</h3>
            <p class="mt-1 text-sm text-[#6B806B]">Produk</p>
        </a>
        <a href="{{ route('petani.pesanan') }}"
           class="rounded-2xl border border-[#DCE8D8] bg-white p-5 text-center transition hover:border-[#2E7D32]">
            <div class="mb-2 text-2xl">📦</div>
            <h3 class="text-2xl font-extrabold text-[#1A2A1A]">{{ $jumlahPesanan }}</h3>
            <p class="mt-1 text-sm text-[#6B806B]">Pesanan Masuk</p>
        </a>
    </div>

    {{-- GRUP MENU --}}
    @foreach ($grup as $judulGrup => $items)
        <section class="mb-6">
            <h3 class="mb-3 text-xs font-bold uppercase tracking-wide text-[#6B806B]">{{ $judulGrup }}</h3>
            <div class="overflow-hidden rounded-2xl border border-[#DCE8D8] bg-white">
                @foreach ($items as $m)
                    <a href="{{ $m['url'] }}"
                       class="flex items-center gap-4 p-4 transition hover:bg-[#F5F9F2] {{ $loop->last ? '' : 'border-b border-[#E5EEE2]' }}">
                        <span class="text-2xl">{{ $m['ikon'] }}</span>
                        <div class="min-w-0 flex-1">
                            <h4 class="font-bold text-[#1A2A1A]">{{ $m['judul'] }}</h4>
                            <p class="text-sm text-[#829182]">{{ $m['ket'] }}</p>
                        </div>
                        <span class="text-lg text-[#829182]">›</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach

    {{-- KELUAR --}}
    <form action="{{ route('keluar') }}" method="POST">
        @csrf
        <button type="submit"
                class="w-full rounded-xl border border-red-300 bg-transparent px-5 py-3 font-semibold text-red-600 transition hover:bg-red-50">
            Keluar
        </button>
    </form>

    <p class="mt-5 text-center text-xs text-[#829182]">TaniLoka v1.0.0 MVP · Dibuat di Bali</p>
</div>

@endsection