@extends('layouts.guest')
@section('title', 'TaniLoka')

@php
    $produk = [
        ['nama' => 'Bayam Organik', 'harga' => 'Rp8.500/kg', 'label' => 'Organik', 'foto' => 'https://images.unsplash.com/photo-1768734837803-54eb8c83e080?w=300&h=200&fit=crop'],
        ['nama' => 'Cabai Rawit', 'harga' => 'Rp45.000/kg', 'label' => 'Bisa Nego', 'foto' => 'https://images.unsplash.com/photo-1768734837679-9b3180433384?w=300&h=200&fit=crop'],
        ['nama' => 'Tomat', 'harga' => 'Rp12.000/kg', 'label' => 'Grade A', 'foto' => 'https://images.unsplash.com/photo-1768734837464-c5045c6a98c4?w=300&h=200&fit=crop'],
        ['nama' => 'Wortel', 'harga' => 'Rp14.000/kg', 'label' => 'Organik', 'foto' => 'https://images.unsplash.com/photo-1768119901638-75c6edd64c0b?w=300&h=200&fit=crop'],
    ];

    $keunggulan = [
        ['judul' => 'Petani Terverifikasi', 'isi' => 'Setiap akun petani dicek dulu sebelum bisa berjualan, jadi asal produknya jelas.', 'ikon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>'],
        ['judul' => 'Harga Jelas, Bisa Nego', 'isi' => 'Harga tampil langsung dari petani. Untuk produk tertentu, pembeli bisa mengajukan penawaran.', 'ikon' => '<path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>'],
        ['judul' => 'Pengiriman Fleksibel', 'isi' => 'Antar sendiri untuk wilayah Bali atau pakai ekspedisi. Ongkir terlihat sebelum pesan.', 'ikon' => '<rect x="1" y="3" width="15" height="13"/><path d="M16 8h4l3 3v5h-7z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>'],
        ['judul' => 'Mendukung Petani Lokal', 'isi' => 'Tanpa perantara, petani mendapat harga lebih baik dan dapur mendapat bahan lebih segar.', 'ikon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>'],
    ];

    $langkah = [
        ['judul' => 'Daftar & pilih peran', 'isi' => 'Daftar sebagai petani atau pengusaha kuliner.'],
        ['judul' => 'Unggah atau cari produk', 'isi' => 'Petani mengunggah hasil panen, pembeli menjelajahi katalog.'],
        ['judul' => 'Negosiasi harga', 'isi' => 'Opsional, untuk produk berlabel "Bisa Nego".'],
        ['judul' => 'Pesan & pilih pengiriman', 'isi' => 'Masukkan ke keranjang lalu pilih cara pengiriman.'],
        ['judul' => 'Konfirmasi & bayar', 'isi' => 'Petani mengonfirmasi, pembeli menyelesaikan pembayaran.'],
        ['judul' => 'Terima & beri ulasan', 'isi' => 'Produk dikirim, lalu beri penilaian untuk petaninya.'],
    ];
@endphp

@section('content')
    <x-landing.navbar />

    {{-- Hero --}}
    <section class="relative flex min-h-screen items-center overflow-hidden pt-16">
        <img src="https://images.unsplash.com/photo-1786881915324-1b3242a40b04?w=1400&h=900&fit=crop" alt="Sawah terasering di Bali" class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-r from-primary-dark/90 via-primary/75 to-primary-dark/40"></div>

        <div class="relative mx-auto grid max-w-6xl items-center gap-12 px-4 py-20 md:grid-cols-2 md:px-8">
            <div>
                <span class="mb-6 inline-block rounded-full border border-accent/40 bg-accent/20 px-3 py-1.5 text-sm font-semibold text-accent">
                    Marketplace B2B hasil tani Bali
                </span>
                <h1 class="mb-4 text-4xl font-extrabold leading-tight text-white md:text-5xl">
                    Dari ladang Bali, <span class="text-accent">langsung ke dapur</span> usahamu
                </h1>
                <p class="mb-8 max-w-lg text-lg leading-relaxed text-white/80">
                    TaniLoka mempertemukan petani lokal dengan restoran, kafe, dan katering. Bahan segar, harga jelas, tanpa perantara.
                </p>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('daftar', ['peran' => 'petani']) }}" class="rounded-2xl bg-accent px-6 py-3.5 text-center font-bold text-text shadow-lg transition hover:-translate-y-0.5 hover:bg-accent-dark">
                        Saya Petani
                    </a>
                    <a href="{{ route('daftar', ['peran' => 'pengusaha']) }}" class="rounded-2xl border border-white/40 bg-white/15 px-6 py-3.5 text-center font-bold text-white transition hover:bg-white/25">
                        Saya Pengusaha Kuliner
                    </a>
                </div>
            </div>

            <div class="hidden rounded-3xl border border-white/20 bg-white/10 p-4 backdrop-blur-md md:block">
                <div class="grid grid-cols-2 gap-3">
                    @foreach ($produk as $p)
                        <div class="overflow-hidden rounded-2xl bg-white/15">
                            <img src="{{ $p['foto'] }}" alt="{{ $p['nama'] }}" class="h-24 w-full object-cover">
                            <div class="p-2">
                                <p class="text-xs font-semibold text-white">{{ $p['nama'] }}</p>
                                <p class="mt-0.5 text-xs font-bold text-accent">{{ $p['harga'] }}</p>
                                <span class="mt-1 inline-block rounded-full bg-accent/20 px-1.5 py-0.5 text-[10px] text-accent">{{ $p['label'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('masuk') }}" class="mt-3 block rounded-xl bg-accent py-2.5 text-center text-sm font-bold text-text transition hover:bg-accent-dark">
                    Lihat semua produk
                </a>
            </div>
        </div>
    </section>

    {{-- Keunggulan --}}
    <section id="tentang" class="mx-auto max-w-6xl px-4 py-20">
        <x-landing.section-title label="Kenapa TaniLoka?" title="Dibuat untuk kebutuhan<br>pertanian Bali"
            subtitle="Kami memotong rantai perantara supaya petani mendapat penghasilan lebih baik dan pembeli mendapat harga lebih wajar." />

        <div class="grid gap-6 md:grid-cols-2">
            @foreach ($keunggulan as $k)
                <div class="group flex gap-4 rounded-2xl border border-border bg-card p-6 transition hover:border-primary-light">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-green-muted text-primary transition group-hover:bg-primary group-hover:text-white">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $k['ikon'] !!}</svg>
                    </div>
                    <div>
                        <h3 class="mb-2 font-bold">{{ $k['judul'] }}</h3>
                        <p class="text-sm leading-relaxed text-text-muted">{{ $k['isi'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Cara kerja --}}
    <section id="cara-kerja" class="bg-cream-dark py-20">
        <div class="mx-auto max-w-6xl px-4">
            <x-landing.section-title label="Cara Kerja" title="Enam langkah sederhana" />

            <div class="grid gap-6 md:grid-cols-3">
                @foreach ($langkah as $i => $l)
                    <div class="rounded-2xl border border-border bg-card p-6 transition hover:border-primary-light">
                        <span class="mb-3 flex h-9 w-9 items-center justify-center rounded-full bg-primary text-sm font-bold text-white">{{ $i + 1 }}</span>
                        <h3 class="mb-2 text-lg font-bold">{{ $l['judul'] }}</h3>
                        <p class="text-sm leading-relaxed text-text-muted">{{ $l['isi'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Untuk petani & kuliner --}}
    <section class="mx-auto grid max-w-6xl gap-8 px-4 py-20 md:grid-cols-2">
        @foreach ([
            ['id' => 'petani', 'label' => 'Untuk Petani', 'judul' => 'Jual langsung,<br>tanpa tengkulak', 'isi' => 'Unggah produk, terima pesanan, dan atur harga sendiri.', 'tombol' => 'Daftar sebagai Petani', 'peran' => 'petani', 'foto' => 'https://images.unsplash.com/photo-1561969310-fa2e856250ba?w=700&h=450&fit=crop'],
            ['id' => 'kuliner', 'label' => 'Untuk Kuliner', 'judul' => 'Bahan segar,<br>harga wajar', 'isi' => 'Belanja dari petani terverifikasi dalam jumlah besar.', 'tombol' => 'Daftar sebagai Pengusaha', 'peran' => 'pengusaha', 'foto' => 'https://images.unsplash.com/photo-1768734837679-9b3180433384?w=700&h=450&fit=crop'],
        ] as $c)
            <div id="{{ $c['id'] }}" class="relative flex min-h-72 scroll-mt-20 flex-col justify-end overflow-hidden rounded-3xl p-8">
                <img src="{{ $c['foto'] }}" alt="" class="absolute inset-0 h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-primary-dark/95 via-primary-dark/60 to-transparent"></div>
                <div class="relative">
                    <span class="mb-3 inline-block rounded-full border border-accent/40 bg-accent/20 px-3 py-1 text-xs font-semibold text-accent">{{ $c['label'] }}</span>
                    <h3 class="mb-2 text-2xl font-extrabold text-white">{!! $c['judul'] !!}</h3>
                    <p class="mb-4 text-sm text-white/80">{{ $c['isi'] }}</p>
                    <a href="{{ route('daftar', ['peran' => $c['peran']]) }}" class="inline-block rounded-xl bg-accent px-5 py-2.5 text-sm font-bold text-text transition hover:bg-accent-dark">{{ $c['tombol'] }}</a>
                </div>
            </div>
        @endforeach
    </section>

    {{-- CTA --}}
    <section class="bg-gradient-to-br from-primary-dark to-primary py-20">
        <div class="mx-auto max-w-3xl px-4 text-center">
            <h2 class="mb-4 text-3xl font-extrabold text-white md:text-4xl">Siap bergabung dengan TaniLoka?</h2>
            <p class="mb-8 text-white/80">Daftar gratis, lalu mulai jual atau beli hari ini.</p>
            <div class="flex flex-col justify-center gap-4 sm:flex-row">
                <a href="{{ route('daftar') }}" class="rounded-2xl bg-accent px-8 py-4 text-lg font-bold text-text shadow-lg transition hover:bg-accent-dark">Daftar Sekarang</a>
                <a href="{{ route('masuk') }}" class="rounded-2xl border border-white/40 bg-white/15 px-8 py-4 text-lg font-bold text-white transition hover:bg-white/25">Masuk</a>
            </div>
        </div>
    </section>

    <x-landing.footer />
@endsection
