@extends('layouts.guest')
@section('title', 'Daftar - TaniLoka')

@php
    $input = 'min-h-13 w-full rounded-xl border-2 bg-white px-4 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20';
@endphp

@section('content')
<div class="flex min-h-screen" x-data="{ peran: '{{ old('peran', $peran) }}' }">
    <x-auth.panel judul="Bergabung dengan TaniLoka" teks="Daftar gratis sebagai petani atau pengusaha kuliner, lalu mulai jual atau beli hasil tani Bali." />

    <div class="flex w-full flex-col justify-center p-5 md:p-12 lg:w-7/12">
        <x-logo :size="36" class="mb-6 lg:hidden" />

        <div class="mx-auto w-full max-w-md">
            <h1 class="mb-1 text-3xl font-extrabold">Buat Akun Baru</h1>
            <p class="mb-6 text-text-muted">Sudah punya akun?
                <a href="{{ route('masuk') }}" class="font-bold text-primary hover:underline">Masuk di sini</a>
            </p>

            <form method="POST" action="{{ route('daftar.proses') }}" class="space-y-4" novalidate>
                @csrf
                <input type="hidden" name="peran" :value="peran">

                <div>
                    <p class="mb-1.5 font-semibold">Saya bergabung sebagai:</p>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach (['pengusaha' => ['Pengusaha Kuliner', 'Restoran, kafe, katering'], 'petani' => ['Petani', 'Penjual hasil tani']] as $key => [$judul, $desc])
                            <button type="button" @click="peran = '{{ $key }}'"
                                :class="peran === '{{ $key }}' ? 'border-primary bg-green-muted' : 'border-border bg-card hover:border-primary-light'"
                                class="min-h-24 rounded-2xl border-2 p-3 text-left transition">
                                <p class="text-sm font-bold">{{ $judul }}</p>
                                <p class="text-sm text-text-muted">{{ $desc }}</p>
                            </button>
                        @endforeach
                    </div>
                    <p x-show="peran === 'petani'" x-cloak class="mt-3 rounded-xl border border-accent/40 bg-accent/10 p-3 text-sm">
                        <span class="font-semibold">Catatan:</span> akun petani perlu diverifikasi 1–2 hari kerja sebelum bisa mulai berjualan.
                    </p>
                </div>

                <div>
                    <label for="nama" class="mb-1.5 block font-semibold">Nama Lengkap</label>
                    <input id="nama" name="nama" value="{{ old('nama') }}" placeholder="Contoh: Wayan Suartha"
                        class="{{ $input }} {{ $errors->has('nama') ? 'border-red-400' : 'border-border' }}">
                    @error('nama') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="mb-1.5 block font-semibold">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="email@contoh.com"
                        class="{{ $input }} {{ $errors->has('email') ? 'border-red-400' : 'border-border' }}">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="telepon" class="mb-1.5 block font-semibold">Nomor WhatsApp</label>
                    <input id="telepon" name="telepon" type="tel" value="{{ old('telepon') }}" placeholder="08xx-xxxx-xxxx" class="{{ $input }} border-border">
                </div>

                <div>
                    <label for="nama_usaha" class="mb-1.5 block font-semibold" x-text="peran === 'petani' ? 'Nama Toko/Kebun' : 'Nama Usaha'"></label>
                    <input id="nama_usaha" name="nama_usaha" value="{{ old('nama_usaha') }}"
                        :placeholder="peran === 'petani' ? 'Contoh: Kebun Sukartha' : 'Contoh: Warung Mak Bali'" class="{{ $input }} border-border">
                </div>

                <div>
                    <label for="password" class="mb-1.5 block font-semibold">Password</label>
                    <input id="password" name="password" type="password" placeholder="Minimal 8 karakter"
                        class="{{ $input }} {{ $errors->has('password') ? 'border-red-400' : 'border-border' }}">
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button class="mt-2 min-h-14 w-full rounded-xl bg-accent text-lg font-bold transition hover:bg-accent-dark">Buat Akun</button>
            </form>
        </div>
    </div>
</div>
@endsection