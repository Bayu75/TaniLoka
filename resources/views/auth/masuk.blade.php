@extends('layouts.guest')
@section('title', 'Masuk - TaniLoka')

@section('content')
<div class="flex min-h-screen">
    <x-auth.panel teks="Masuk sesuai peranmu. Petani mengelola produk dan pesanan, pengusaha kuliner belanja langsung dari kebun." />

    <div class="flex w-full flex-col justify-center p-5 md:p-12 lg:w-7/12">
        <x-logo :size="36" class="mb-6 lg:hidden" />

        <div class="mx-auto w-full max-w-xl">
            <h1 class="mb-1 text-3xl font-extrabold">Masuk ke TaniLoka</h1>
            <p class="mb-6 text-text-muted">Masuk dengan email atau nomor WA.</p>

            {{-- Info akun demo + masuk cepat --}}
            @if (app()->environment('local'))
                @php
                    $demo = [
                        'petani' => ['Demo Petani', 'Made Sutama', 'made@taniloka.test'],
                        'pengusaha' => ['Demo Pengusaha Kuliner', 'Putu Restu', 'resto@taniloka.test'],
                    ];
                @endphp

                <div class="mb-6 grid gap-4 sm:grid-cols-2">
                    @foreach ($demo as $key => [$judul, $nama, $email])
                        <div class="flex flex-col gap-2">
                            <div class="min-h-28 rounded-2xl border-2 border-border bg-card p-4">
                                <p class="text-lg font-bold leading-tight">{{ $judul }}</p>
                                <p class="mt-1 text-sm text-text-muted">{{ $nama }}</p>
                                <p class="text-sm text-text-muted">{{ $email }}</p>
                                <p class="text-sm text-text-muted">Password: <span class="font-semibold">password</span></p>
                            </div>

                            <form method="POST" action="{{ route('masuk.demo') }}">
                                @csrf
                                <input type="hidden" name="peran" value="{{ $key }}">
                                <button class="w-full cursor-pointer rounded-xl border-2 border-accent bg-accent/10 py-2.5 font-bold transition hover:bg-accent">Masuk Cepat (Demo)</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('masuk.proses') }}" class="space-y-4 pt-4 md:pt-0" novalidate>
                @csrf
                <div>
                    <label for="login" class="mb-1.5 block font-semibold">Email atau Nomor WA</label>
                    <input id="login" name="login" type="text" value="{{ old('login') }}" placeholder="email@contoh.com atau 08xxxxxxxxxx"
                        autocomplete="username"
                        class="min-h-13 w-full rounded-xl border-2 {{ $errors->has('login') ? 'border-red-400' : 'border-border' }} bg-white px-4 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                </div>
                <div>
                    <label for="password" class="mb-1.5 block font-semibold">Password</label>
                    <input id="password" name="password" type="password" placeholder="Password" autocomplete="current-password"
                        class="min-h-13 w-full rounded-xl border-2 {{ $errors->has('password') ? 'border-red-400' : 'border-border' }} bg-white px-4 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                </div>

                <label class="flex items-center gap-2 text-text-muted">
                    <input type="checkbox" name="ingat" value="1" class="size-5 rounded border-2 border-border accent-primary">
                    Ingat saya
                </label>

                @if ($errors->any())
                    <div class="rounded-xl border-2 border-red-200 bg-red-50 p-3 font-medium text-red-700">{{ $errors->first() }}</div>
                @endif

                <button class="min-h-14 w-full rounded-xl bg-accent text-lg font-bold transition hover:bg-accent-dark cursor-pointer">Masuk</button>
            </form>

            <p class="mt-5 text-text-muted">Belum punya akun?
                <a href="{{ route('daftar') }}" class="font-semibold text-primary underline">Daftar</a>
            </p>
        </div>
    </div>
</div>
@endsection