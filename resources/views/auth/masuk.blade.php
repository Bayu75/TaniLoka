@extends('layouts.guest')
@section('title', 'Masuk - TaniLoka')

@section('content')
<div class="flex min-h-screen" x-data="{ peran: '{{ old('peran', $peran) }}' }">
    <x-auth.panel teks="Masuk sesuai peranmu. Petani mengelola produk dan pesanan, pengusaha kuliner belanja langsung dari kebun." />

    <div class="flex w-full flex-col justify-center p-5 md:p-12 lg:w-7/12">
        <x-logo :size="36" class="mb-6 lg:hidden" />

        <div class="mx-auto w-full max-w-xl">
            <h1 class="mb-1 text-3xl font-extrabold">Masuk ke TaniLoka</h1>
            <p class="mb-6 text-text-muted">Pilih peranmu terlebih dahulu.</p>

            {{-- Pilih peran + masuk cepat --}}
            <div class="mb-6 grid gap-4 sm:grid-cols-2">
                @foreach (['petani' => ['Saya Petani', 'Jual hasil panen, terima pesanan'], 'pengusaha' => ['Saya Pengusaha Kuliner', 'Restoran, kafe, katering']] as $key => [$judul, $desc])
                    <div class="flex flex-col gap-2">
                        <button type="button" @click="peran = '{{ $key }}'"
                            :class="peran === '{{ $key }}' ? 'border-primary bg-green-muted' : 'border-border bg-card hover:border-primary-light'"
                            class="relative min-h-28 rounded-2xl border-2 p-4 text-left transition">
                            <span x-show="peran === '{{ $key }}'" class="absolute right-3 top-3 flex h-6 w-6 items-center justify-center rounded-full bg-primary text-xs text-white">&#10003;</span>
                            <p class="text-lg font-bold leading-tight">{{ $judul }}</p>
                            <p class="text-sm text-text-muted">{{ $desc }}</p>
                        </button>
                        <form method="POST" action="{{ route('masuk.proses') }}">
                            @csrf
                            <input type="hidden" name="peran" value="{{ $key }}">
                            <input type="hidden" name="demo" value="1">
                            <button class="w-full rounded-xl border-2 border-accent bg-accent/10 py-2.5 font-bold transition hover:bg-accent">Masuk Cepat (Demo)</button>
                        </form>
                    </div>
                @endforeach
            </div>

            <div class="relative mb-4 border-t border-border">
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-cream px-3 text-sm text-text-muted">atau masuk dengan akun</span>
            </div>

            <form method="POST" action="{{ route('masuk.proses') }}" class="space-y-4" novalidate>
                @csrf
                <input type="hidden" name="peran" :value="peran">
                <p class="text-text-muted">Masuk sebagai:
                    <span class="font-bold text-primary" x-text="peran === 'petani' ? 'Petani' : 'Pengusaha Kuliner'"></span>
                </p>

                <div>
                    <label for="email" class="mb-1.5 block font-semibold">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="email@contoh.com"
                        class="min-h-13 w-full rounded-xl border-2 border-border bg-white px-4 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                </div>
                <div>
                    <label for="password" class="mb-1.5 block font-semibold">Password</label>
                    <input id="password" name="password" type="password" placeholder="Password"
                        class="min-h-13 w-full rounded-xl border-2 border-border bg-white px-4 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                </div>

                @if ($errors->any())
                    <div class="rounded-xl border-2 border-red-200 bg-red-50 p-3 font-medium text-red-700">{{ $errors->first() }}</div>
                @endif

                <button class="min-h-14 w-full rounded-xl bg-accent text-lg font-bold transition hover:bg-accent-dark">Masuk</button>
            </form>

            <p class="mt-5 text-text-muted">Belum punya akun?
                <a href="{{ route('daftar') }}" class="font-semibold text-primary underline">Daftar</a>
            </p>
        </div>
    </div>
</div>
@endsection