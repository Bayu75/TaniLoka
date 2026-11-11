
@extends('layouts.pengusaha')

@section('title', 'Profil Usaha - TaniLoka')

@section('content')
@php
    $profil = session('profil_usaha', []);

    $nama = session('nama', 'Sari Kitchen');
    $namaUsaha = $profil['nama_usaha'] ?? $nama;
    $email = $profil['email'] ?? 'pengusaha@taniloka.id';
    $telepon = $profil['telepon'] ?? '';
    $jenisUsaha = $profil['jenis_usaha'] ?? 'Restoran';
    $deskripsi = $profil['deskripsi'] ?? '';
@endphp


{{-- HEADER HALAMAN PROFIL USAHA --}}
<div class="relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen border-b border-[#D4E8D0] bg-white">

    <div class="relative mx-auto max-w-5xl px-4 py-3">

        {{-- TOMBOL KEMBALI --}}
        <a href="{{ route('pengusaha.akun') }}"
           class="mb-2 inline-flex items-center gap-1 text-sm font-extrabold text-[#087C3E] hover:text-green-800
                  md:absolute md:right-full md:top-1/2 md:mb-0 md:mr-4 md:-translate-y-1/2 md:whitespace-nowrap">

            <span class="text-2xl font-black leading-none">&lt;</span>
            <span>Kembali</span>
        </a>

        {{-- JUDUL DAN DESKRIPSI --}}
        <div>
            <p class="text-sm font-semibold text-[#087C3E]">
                Akun Saya
            </p>

            <h1 class="mt-1 text-2xl font-extrabold text-[#1A2A1A]">
                Profil Usaha
            </h1>

            <p class="mt-1 text-sm text-[#6B806B]">
                Kelola informasi dan identitas usaha kuliner Anda.
            </p>
        </div>

    </div>

</div>

{{-- KONTEN HALAMAN --}}
<div class="mx-auto max-w-5xl space-y-6 px-4 py-6">


    {{-- NOTIFIKASI --}}
    @if(session('sukses'))
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('sukses') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-600">
            <ul class="list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- IDENTITAS USAHA --}}
    <div class="rounded-2xl border border-green-100 bg-white p-5 shadow-sm md:p-7">
        <div class="flex items-center gap-4 border-b border-gray-100 pb-5">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-green-100 text-2xl font-bold text-green-700">
                {{ strtoupper(substr($namaUsaha, 0, 1)) }}
            </div>

            <div>
                <h2 class="text-lg font-bold text-gray-900">
                    {{ $namaUsaha }}
                </h2>
                <p class="text-sm text-gray-500">
                    Pengusaha Kuliner TaniLoka
                </p>
            </div>
        </div>

        {{-- FORM PROFIL --}}
        <form action="{{ route('pengusaha.profil.simpan') }}"
              method="POST"
              class="mt-6 space-y-5">
            @csrf

            <div>
                <h3 class="mb-4 font-bold text-gray-900">
                    Informasi Usaha
                </h3>
            </div>

            <div class="grid gap-5 md:grid-cols-2">

                {{-- NAMA PEMILIK --}}
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Nama Pemilik *
                    </label>
                    <input
                        type="text"
                        name="nama"
                        value="{{ old('nama', $nama) }}"
                        required
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-green-600 focus:ring-2 focus:ring-green-100"
                        placeholder="Masukkan nama pemilik"
                    >
                </div>

                {{-- NAMA USAHA --}}
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Nama Usaha *
                    </label>
                    <input
                        type="text"
                        name="nama_usaha"
                        value="{{ old('nama_usaha', $namaUsaha) }}"
                        required
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-green-600 focus:ring-2 focus:ring-green-100"
                        placeholder="Masukkan nama usaha"
                    >
                </div>

                {{-- EMAIL --}}
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Email *
                    </label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email', $email) }}"
                        required
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-green-600 focus:ring-2 focus:ring-green-100"
                        placeholder="contoh@email.com"
                    >
                </div>

                {{-- TELEPON --}}
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Nomor WhatsApp
                    </label>
                    <input
                        type="tel"
                        name="telepon"
                        value="{{ old('telepon', $telepon) }}"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-green-600 focus:ring-2 focus:ring-green-100"
                        placeholder="Contoh: 081234567890"
                    >
                </div>

                {{-- JENIS USAHA --}}
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Jenis Usaha
                    </label>
                    <select
                        name="jenis_usaha"
                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100"
                    >
                        @foreach(['Restoran', 'Katering', 'Warung Makan', 'Cafe', 'Hotel', 'Usaha Kuliner Lainnya'] as $jenis)
                            <option value="{{ $jenis }}"
                                {{ old('jenis_usaha', $jenisUsaha) === $jenis ? 'selected' : '' }}>
                                {{ $jenis }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- DESKRIPSI --}}
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Deskripsi Usaha
                    </label>
                    <textarea
                        name="deskripsi"
                        rows="4"
                        maxlength="500"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100"
                        placeholder="Ceritakan sedikit tentang usaha kuliner Anda..."
                    >{{ old('deskripsi', $deskripsi) }}</textarea>
                    <p class="mt-1 text-xs text-gray-400">
                        Maksimal 500 karakter.
                    </p>
                </div>

            </div>

            {{-- TOMBOL --}}
            <div class="flex flex-col-reverse justify-end gap-3 border-t border-gray-100 pt-5 sm:flex-row">
                <a
                    href="{{ route('pengusaha.beranda') }}"
                    class="rounded-xl border border-gray-200 px-6 py-3 text-center text-sm font-semibold text-gray-600 transition hover:bg-gray-50"
                >
                    Batal
                </a>

                <button
                    type="submit"
                    class="rounded-xl bg-green-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-green-800"
                >
                    Simpan Perubahan
                </button>
            </div>

        </form>
    </div>
</div>
@endsection
