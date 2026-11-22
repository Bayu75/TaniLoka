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
                    @error('peran') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="name" class="mb-1.5 block font-semibold">Nama Lengkap</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Nama sesuai KTP" autocomplete="name"
                        class="{{ $input }} {{ $errors->has('name') ? 'border-red-400' : 'border-border' }}">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="mb-1.5 block font-semibold">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="email@contoh.com" autocomplete="email"
                        class="{{ $input }} {{ $errors->has('email') ? 'border-red-400' : 'border-border' }}">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="no_wa" class="mb-1.5 block font-semibold">Nomor WhatsApp</label>
                    <input id="no_wa" name="no_wa" type="tel" value="{{ old('no_wa') }}" placeholder="08xxxxxxxxxx" autocomplete="tel"
                        class="{{ $input }} {{ $errors->has('no_wa') ? 'border-red-400' : 'border-border' }}">
                    @error('no_wa') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-sm text-text-muted">Isi minimal salah satu: email atau nomor WA.</p>
                </div>

                <div>
                    <label for="password" class="mb-1.5 block font-semibold">Password</label>
                    <input id="password" name="password" type="password" placeholder="Minimal 8 karakter" autocomplete="new-password"
                        class="{{ $input }} {{ $errors->has('password') ? 'border-red-400' : 'border-border' }}">
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1.5 block font-semibold">Konfirmasi Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Ulangi password" autocomplete="new-password"
                        class="{{ $input }} border-border">
                </div>

                <div>
                    <label class="flex items-start gap-2 text-text-muted">
                        <input type="checkbox" name="setuju" value="1" @checked(old('setuju'))
                            class="mt-0.5 size-5 shrink-0 rounded border-2 border-border accent-primary">
                        <span>Saya menyetujui <a href="#" class="font-semibold text-primary underline">Syarat &amp; Ketentuan</a> serta <a href="#" class="font-semibold text-primary underline">Kebijakan Privasi</a> TaniLoka.</span>
                    </label>
                    @error('setuju') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button class="mt-2 min-h-14 w-full rounded-xl bg-accent text-lg font-bold transition hover:bg-accent-dark cursor-pointer">Buat Akun</button>
            </form>
        </div>
    </div>
</div>
@endsection