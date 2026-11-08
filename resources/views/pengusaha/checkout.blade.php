@extends('layouts.pengusaha')

@section('title', 'Checkout - TaniLoka')

@section('content')

@php
    // Data ongkos kirim sementara (dummy)
    $ongkosKirim = [
        'kendaraan' => 0,
        'jnt' => 12000,
        'ambil' => 0,
    ];
@endphp

    {{-- Judul halaman --}}
    <div class="relative left-1/2 -mt-4 w-screen -translate-x-1/2 border-b border-green-200 bg-white">
        <div class="mx-auto max-w-4xl px-4 py-4">
            <div class="flex items-center gap-5">
                <a href="{{ route('pengusaha.keranjang') }}"
                    class="flex items-center gap-2 text-sm font-semibold text-primary hover:text-green-800">
                    <span class="text-2xl leading-none">‹</span>
                    Kembali
                </a>

                <h1 class="text-xl font-extrabold text-gray-800">
                    Checkout
                </h1>
            </div>
        </div>
    </div>

    {{-- Tahapan checkout --}}
    <div class="relative left-1/2 w-screen -translate-x-1/2 border-b border-green-200 bg-white">
        <div class="mx-auto flex max-w-4xl items-center justify-center gap-3 px-4 py-3">

            {{-- Tahap 1 --}}
            <div class="flex items-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-500 text-sm font-bold text-black">
                    1
                </span>
                <span class="text-sm font-semibold text-gray-800">
                    Pengiriman
                </span>
            </div>

            <div class="h-0.5 w-20 bg-gray-200"></div>

            {{-- Tahap 2 --}}
            <div class="flex items-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-200 text-sm font-bold text-gray-500">
                    2
                </span>
                <span class="text-sm font-medium text-gray-600">
                    Pembayaran
                </span>
            </div>

            <div class="h-0.5 w-20 bg-gray-200"></div>

            {{-- Tahap 3 --}}
            <div class="flex items-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-200 text-sm font-bold text-gray-500">
                    3
                </span>
                <span class="text-sm font-medium text-gray-600">
                    Konfirmasi
                </span>
            </div>

        </div>
    </div>

<div class="mx-auto w-full max-w-4xl">

    {{-- Layout Checkout --}}
    <div
        x-data="{
            metode: 'diantar',
            kurir: 'kendaraan',

            modalAlamat: false,
            alamatAktif: 0,
            tampilAlamat: false,

            daftarAlamat: [
                {
                    nama: 'Restoran',
                    label: 'Utama',
                    kontak: 'Sari Kitchen · 0812-3456-7890',
                    alamat: 'Jl. Sunset Road No. 88, Denpasar'
                },
                {
                    nama: 'Dapur Pusat',
                    label: '',
                    kontak: 'Sari Kitchen (Gudang) · 0812-3456-7891',
                    alamat: 'Jl. Imam Bonjol No. 21, Badung'
                }
            ],
            ongkosKirim: @js($ongkosKirim),

            get biayaKirim() {
                if (this.metode === 'ambil') return this.ongkosKirim.ambil;
                return this.ongkosKirim[this.kurir];
            },

            get totalPembayaran() {
                return 90000 + this.biayaKirim;
            }
        }"
            class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_266px]"
    >
        {{-- Kolom Kiri: Konten yang Bisa Di-scroll --}}
        <div class="min-w-0 lg:h-[calc(100vh-280px)] lg:overflow-y-auto lg:pr-2">

            {{-- Alamat Pengiriman --}}
            <div class="rounded-2xl border border-green-100 bg-white p-5">

                {{-- Header --}}
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-800">
                        Alamat Pengiriman
                    </h2>

                    <button
                        type="button"
                        @click="tampilAlamat = !tampilAlamat"
                        class="rounded-xl border-2 border-primary px-4 py-2 text-sm font-semibold text-primary hover:bg-green-50"
                        x-text="tampilAlamat ? 'Tutup' : 'Ganti Alamat'"
                    ></button>
                </div>

                {{-- Daftar Alamat --}}
                <div x-show="tampilAlamat" x-transition class="space-y-2">

                    <template x-for="(alamat, index) in daftarAlamat" :key="index">
                        <button
                            type="button"
                            @click="alamatAktif = index"
                            class="flex w-full items-start gap-3 rounded-xl border-2 p-3 text-left transition"
                            :class="alamatAktif === index
                                ? 'border-green-700 bg-green-50'
                                : 'border-green-100 bg-white'"
                        >
                            {{-- Radio --}}
                            <span
                                class="mt-1 flex h-6 w-6 shrink-0 items-center justify-center rounded-full border"
                                :class="alamatAktif === index
                                    ? 'border-green-700'
                                    : 'border-gray-400'"
                            >
                                <span
                                    x-show="alamatAktif === index"
                                    class="h-3 w-3 rounded-full bg-green-700"
                                ></span>
                            </span>

                            {{-- Informasi Alamat --}}
                            <span class="flex-1">
                                <span class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="font-bold text-gray-800"
                                        x-text="alamat.nama"
                                    ></span>

                                    <span
                                        x-show="alamat.label"
                                        class="rounded-full bg-green-700 px-2 py-0.5 text-xs font-bold text-white"
                                        x-text="alamat.label"
                                    ></span>
                                </span>

                                <span
                                    class="block text-sm text-gray-600"
                                    x-text="alamat.kontak"
                                ></span>

                                <span
                                    class="block text-sm text-gray-600"
                                    x-text="alamat.alamat"
                                ></span>
                            </span>
                        </button>
                    </template>

                    <button
                        type="button"
                        class="pt-3 text-sm font-bold text-primary underline"
                    >
                        Kelola Alamat
                    </button>

                </div>

                {{-- Alamat Terpilih --}}
                <div x-show="!tampilAlamat">
                    <p class="font-bold text-gray-800"
                        x-text="daftarAlamat[alamatAktif].nama"></p>

                    <p class="text-sm text-gray-600"
                        x-text="daftarAlamat[alamatAktif].kontak"></p>

                    <p class="text-sm text-gray-600"
                        x-text="daftarAlamat[alamatAktif].alamat"></p>
                </div>

            </div>

    {{-- Pesanan dari Petani --}}
    <div
        class="mt-4 overflow-hidden rounded-2xl border border-green-100 bg-white"

    >

        {{-- Header Pesanan --}}
        <div class="border-b border-green-100 bg-green-50 p-4">
            <h3 class="font-bold text-gray-800">
                Pesanan dari Ibu Wayan Sari
            </h3>

            <p class="text-sm text-gray-600">
                1 produk · Rp 90.000
            </p>
        </div>

        <div class="space-y-3 p-4">

            {{-- Pilihan Metode --}}
            <div class="grid grid-cols-2 gap-2">

                <button
                    type="button"
                    @click="metode = 'diantar'"
                    :class="metode === 'diantar'
                        ? 'border-2 border-green-700 bg-green-50 text-green-800'
                        : 'border-2 border-green-100 bg-white text-gray-600'"
                    class="rounded-xl px-3 py-4 text-sm font-bold"
                >
                    Diantar
                </button>

                <button
                    type="button"
                    @click="metode = 'ambil'"
                    :class="metode === 'ambil'
                        ? 'border-2 border-green-700 bg-green-50 text-green-800'
                        : 'border-2 border-green-100 bg-white text-gray-600'"
                    class="rounded-xl px-3 py-4 text-sm font-bold"
                >
                    Ambil Sendiri (Siap Diambil)
                </button>

            </div>

            {{-- Jika memilih Diantar --}}
            <div x-show="metode === 'diantar'" x-transition class="space-y-3">

                {{-- Kendaraan Sendiri --}}
                <button
                    type="button"
                    @click="kurir = 'kendaraan'"
                    :class="kurir === 'kendaraan'
                        ? 'border-2 border-green-700 bg-green-50'
                        : 'border-2 border-green-100 bg-white'"
                    class="flex w-full items-center gap-3 rounded-xl p-3 text-left"
                >
                    <span
                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-gray-400"
                        :class="kurir === 'kendaraan' ? 'border-green-700' : ''"
                    >
                        <span
                            x-show="kurir === 'kendaraan'"
                            class="h-3 w-3 rounded-full bg-green-700"
                        ></span>
                    </span>

                    <span class="text-xl">🚚</span>

                    <span class="flex-1">
                        <span class="block text-sm font-bold text-gray-800">
                            Kendaraan Sendiri
                        </span>
                        <span class="block text-xs text-gray-500">
                            Pengiriman mandiri, hanya Bali
                        </span>
                    </span>

                    <span class="text-sm font-bold text-green-700">
                        Gratis
                    </span>
                </button>

                {{-- J&T Express --}}
                <button
                    type="button"
                    @click="kurir = 'jnt'"
                    :class="kurir === 'jnt'
                        ? 'border-2 border-green-700 bg-green-50'
                        : 'border-2 border-green-100 bg-white'"
                    class="flex w-full items-center gap-3 rounded-xl p-3 text-left"
                >
                    <span
                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-gray-400"
                        :class="kurir === 'jnt' ? 'border-green-700' : ''"
                    >
                        <span
                            x-show="kurir === 'jnt'"
                            class="h-3 w-3 rounded-full bg-green-700"
                        ></span>
                    </span>

                    <span class="text-xl">📦</span>

                    <span class="flex-1">
                        <span class="block text-sm font-bold text-gray-800">
                            J&T Express
                        </span>
                        <span class="block text-xs text-gray-500">
                            Estimasi 1–2 hari kerja
                        </span>
                    </span>

                    <span class="text-sm font-bold text-green-700">
                        Rp 12.000
                    </span>
                </button>

            </div>

            {{-- Jika memilih Ambil Sendiri --}}
            <div
                x-show="metode === 'ambil'"
                x-transition
                class="rounded-xl bg-green-50 p-3 text-sm leading-relaxed text-gray-600"
            >
                Anda mengambil sendiri pesanan di kebun petani.
                Tanpa ongkos kirim. Petani akan menandai pesanan
                "Siap Diambil".
            </div>

        </div>

    </div>

    {{-- Tombol Lanjut ke Pembayaran --}}
    <a
        href="{{ route('pengusaha.pembayaran') }}"
        class="mt-4 block w-full rounded-xl bg-amber-500 px-4 py-4 text-center text-sm font-bold text-gray-900 transition hover:bg-amber-600"
    >
        Lanjut ke Pembayaran
    </a>

        </div>


        {{-- Kolom Kanan: Ringkasan Tetap --}}
        <div class="self-start lg:sticky lg:top-24">

            <div class="rounded-2xl border border-green-100 bg-white p-4">
                <h2 class="mb-4 text-lg font-bold text-gray-800">
                    Ringkasan
                </h2>

                {{-- Produk --}}
                <div class="flex items-center justify-between gap-2 border-b border-gray-200 pb-3">
                    <p class="truncate text-sm text-gray-600">
                        Cabai Rawit Merah
                    </p>
                    <p class="shrink-0 text-sm font-bold text-gray-800">
                        Rp 90.000
                    </p>
                </div>

                {{-- Subtotal dan Ongkos Kirim --}}
                <div class="mt-3 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="text-gray-700">Rp 90.000</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-600">Ongkos kirim</span>
                        <span
                            class="text-gray-600"
                            x-text="biayaKirim === 0
                                ? 'Gratis'
                                : 'Rp ' + biayaKirim.toLocaleString('id-ID')"
                        ></span>
                    </div>
                </div>

                {{-- Total --}}
                <div class="mt-3 flex justify-between border-t border-gray-200 pt-3">
                    <span class="font-bold text-primary">Total</span>
                    <span
                        class="font-extrabold text-primary"
                        x-text="'Rp ' + totalPembayaran.toLocaleString('id-ID')"
                    ></span>
                </div>
            </div>

            </div>

        </div>

    </div>

</div>

@endsection
