@extends('layouts.petani')

@section('title', 'Profil Petani - TaniLoka')

@section('content')
@php
    $namaUsaha = $profil?->nama_usaha_tani ?: $user->name;
    $input = 'w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-green-600 focus:ring-2 focus:ring-green-100';
    $dariVerifikasi = request('dari') === 'verifikasi';
@endphp

<x-page-header judul="Alamat Saya"
    :kembali="$dariVerifikasi ? route('petani.verifikasi') : route('petani.akun')"
    lebar="max-w-5xl"/>

<div class="mx-auto max-w-5xl space-y-6 px-4 py-6">

    @if (session('sukses'))
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">{{ session('sukses') }}</div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-600">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-2xl border border-green-100 bg-white p-5 shadow-sm md:p-7">

        {{-- IDENTITAS --}}
        <div class="flex items-center gap-4 border-b border-gray-100 pb-5">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-green-100 text-2xl font-bold text-green-700">
                {{ strtoupper(mb_substr($namaUsaha, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900">{{ $namaUsaha }}</h2>
                <p class="text-sm text-gray-500">
                    Petani TaniLoka
                    @if ($user->terverifikasi())
                        <span class="ml-1 font-semibold text-green-700">· ✓ Terverifikasi</span>
                    @else
                        <span class="ml-1 font-semibold text-amber-600">· Belum terverifikasi</span>
                    @endif
                </p>
            </div>
        </div>

        <form action="{{ route('petani.profil.simpan') }}" method="POST" class="mt-6 space-y-8">
            @csrf

            @if (request('dari') === 'verifikasi')
                <input type="hidden" name="dari" value="verifikasi">
            @endif

            {{-- INFORMASI USAHA TANI --}}
            <div>
                <h3 class="mb-4 font-bold text-gray-900">Informasi Usaha Tani</h3>
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">Nama Petani *</label>
                        <input type="text" name="nama" value="{{ old('nama', $user->name) }}" required class="{{ $input }}" placeholder="Masukkan nama lengkap">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">Nama Usaha Tani *</label>
                        <input type="text" name="nama_usaha_tani" value="{{ old('nama_usaha_tani', $profil?->nama_usaha_tani) }}" required class="{{ $input }}" placeholder="Contoh: Kebun Sayur Pak Made">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">Email *</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="{{ $input }}" placeholder="contoh@email.com">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">Nomor WhatsApp *</label>
                        <input type="tel" name="no_wa" value="{{ old('no_wa', $user->no_wa) }}" required class="{{ $input }}" placeholder="Contoh: 081234567890">
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-gray-700">Deskripsi Usaha Tani</label>
                        <textarea name="deskripsi" rows="4" maxlength="500" class="{{ $input }}"
                                  placeholder="Ceritakan komoditas yang Anda tanam, luas lahan, atau cara bertani...">{{ old('deskripsi', $profil?->deskripsi) }}</textarea>
                        <p class="mt-1 text-xs text-gray-400">Maksimal 500 karakter.</p>
                    </div>
                </div>
            </div>

            {{-- REKENING --}}
            <div class="border-t border-gray-100 pt-6">
                <h3 class="font-bold text-gray-900">Rekening Pembayaran</h3>
                <p class="mb-4 mt-1 text-sm text-gray-500">Digunakan untuk menerima hasil penjualan produk.</p>
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">Nama Bank</label>
                        <input type="text" name="nama_bank" value="{{ old('nama_bank', $profil?->nama_bank) }}" class="{{ $input }}" placeholder="Contoh: BRI, BPD Bali">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">Nomor Rekening</label>
                        <input type="text" name="no_rekening" value="{{ old('no_rekening', $profil?->no_rekening) }}" inputmode="numeric" class="{{ $input }}" placeholder="Contoh: 012301000123456">
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-gray-700">Atas Nama Rekening</label>
                        <input type="text" name="atas_nama_rekening" value="{{ old('atas_nama_rekening', $profil?->atas_nama_rekening) }}" class="{{ $input }}" placeholder="Nama sesuai buku tabungan">
                    </div>
                </div>
            </div>

            {{-- TOMBOL --}}
            <div class="flex flex-col-reverse justify-end gap-3 border-t border-gray-100 pt-5 sm:flex-row">
                <a href="{{ request('dari') === 'verifikasi' ? route('petani.verifikasi') : route('petani.akun') }}"
                class="rounded-xl border border-gray-200 px-6 py-3 text-center text-sm font-semibold text-gray-600 transition hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit"
                        class="rounded-xl bg-green-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-green-800">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection