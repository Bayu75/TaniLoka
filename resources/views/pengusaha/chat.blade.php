
@extends('layouts.pengusaha')

@section('title', 'Chat Negosiasi - TaniLoka')

<style>
    /* Menghilangkan tombol panah pada input angka */
    input[type="number"] {
        appearance: textfield;
        -moz-appearance: textfield;
    }

    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        appearance: none;
        margin: 0;
    }
</style>

@section('content')

<style>
    [x-cloak] {
        display: none !important;
    }
</style>

<div
    x-data="{
        tampilkanPenawaran: false,
        hargaPenawaran: 31500,
        jumlah: 5,
        hargaNormal: 35000,
        pesanBaru: '',
        daftarPesan: [],

        get totalPenawaran() {
            return Math.max(0, Number(this.hargaPenawaran) || 0)
                * Math.max(0, Number(this.jumlah) || 0);
        },

        formatRupiah(nilai) {
            return 'Rp ' + Number(nilai).toLocaleString('id-ID');
        },

        kirimPesan() {
            if (this.tampilkanPenawaran) {
                if (
                    Number(this.hargaPenawaran) <= 0 ||
                    Number(this.jumlah) <= 0
                ) {
                    return;
                }

                this.daftarPesan.push({
                    tipe: 'penawaran',
                    status: 'menunggu_jawaban',
                    harga: Number(this.hargaPenawaran),
                    jumlah: Number(this.jumlah),
                    total: this.totalPenawaran,
                    isi: this.pesanBaru.trim(),
                    waktu: new Date().toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    })
                });

                this.tampilkanPenawaran = false;
                this.pesanBaru = '';

            } else {
                if (!this.pesanBaru.trim()) {
                    return;
                }

                this.daftarPesan.push({
                    tipe: 'teks',
                    isi: this.pesanBaru.trim(),
                    waktu: new Date().toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    })
                });

                this.pesanBaru = '';
            }

            this.$nextTick(() => {
                const area = this.$refs.areaPesan;
                area.scrollTop = area.scrollHeight;
            });
        }

    }"
>

    {{-- HEADER CHAT --}}
    <div class="sticky top-0 z-50 relative left-1/2 -mt-4 w-screen -translate-x-1/2 border-b border-green-100 bg-white">
        <div class="mx-auto flex max-w-3xl items-center gap-5 px-4 py-3">

            <a
                href="{{ route('pengusaha.pesan') }}"
                class="flex shrink-0 items-center gap-2 font-semibold text-green-700 transition hover:text-green-800"
            >
                <span class="text-2xl leading-none">‹</span>
                <span>Kembali</span>
            </a>

            <div class="min-w-0">
                <h1 class="font-bold text-gray-900">
                    Pak Made Sukartha
                </h1>
                <p class="text-sm text-green-700">
                    Bayam Hijau Organik
                </p>
            </div>

        </div>
    </div>


    {{-- AREA PERCAKAPAN --}}
    <div
        :style="tampilkanPenawaran
            ? 'height: calc(100dvh - 424px)'
            : 'height: calc(100dvh - 198px)'"
        class="fixed left-1/2 top-[124px] z-10 flex w-full max-w-3xl -translate-x-1/2 flex-col overflow-hidden border-x border-green-100 bg-white"
    >

    {{-- AREA SCROLL PESAN --}}
    <div
        x-ref="areaPesan"
        class="h-0 min-h-0 flex-1 overflow-x-hidden overflow-y-scroll overscroll-y-contain p-4"
        style="scroll-behavior: smooth;"
    >

        {{-- INFORMASI PRODUK --}}
        <div class="mb-4 rounded-2xl border border-green-100 bg-white p-4">

            <div class="flex items-center gap-3">

                <img
                    src="{{ asset('images/products/bayam.png') }}"
                    alt="Bayam Hijau Organik"
                    class="h-16 w-16 shrink-0 rounded-xl object-cover"
                >

                <div class="min-w-0 flex-1">

                    <h2 class="font-bold text-gray-900">
                        Bayam Hijau Organik
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Harga petani:
                        {{ 'Rp ' . number_format(35000, 0, ',', '.') }}/kg
                    </p>

                    <p class="text-sm font-semibold text-amber-600">
                        Negosiasikan harga dengan petani
                    </p>

                </div>
            </div>
        </div>


        {{-- DAFTAR PESAN --}}
        <div class="space-y-4">

            <template x-for="(pesan, index) in daftarPesan" :key="index">

                <div class="flex justify-end">

                    {{-- PESAN TEKS --}}
                    <template x-if="pesan.tipe === 'teks'">

                        <div class="max-w-[85%] rounded-2xl rounded-tr-none bg-green-100 p-3">

                            <p
                                class="break-words text-sm text-gray-800"
                                x-text="pesan.isi"
                            ></p>

                            <p
                                class="mt-1 text-right text-xs text-gray-400"
                                x-text="'Anda · ' + pesan.waktu"
                            ></p>

                        </div>

                    </template>

                    {{-- PESAN PENAWARAN --}}
                    <template x-if="pesan.tipe === 'penawaran'">
                        <div class="flex justify-end">
                            <div class="w-fit max-w-[85%] overflow-hidden rounded-2xl rounded-tr-none border-2 border-green-700 bg-white">

                                {{-- Header hijau --}}
                                <div class="flex items-center justify-between rounded-t-xl bg-green-700 px-4 py-3">
                                    <h3 class="font-bold text-white">
                                        Penawaran Harga
                                    </h3>

                                    <span
                                        class="rounded-full bg-white/95 px-3 py-1 text-xs font-semibold text-green-800"
                                        x-text="
                                            pesan.status === 'menunggu_jawaban'
                                                ? 'Menunggu jawaban'
                                                : pesan.status === 'ditawar_balik'
                                                    ? 'Ditawar balik'
                                                    : 'Disetujui'
                                        "
                                    ></span>
                                </div>

                                {{-- Detail penawaran --}}
                                <div class="p-4">

                                    {{-- Harga per kg --}}
                                    <div class="flex flex-wrap items-baseline gap-2">
                                        <span
                                            class="text-2xl font-extrabold text-green-800"
                                            x-text="formatRupiah(pesan.harga)">
                                        </span>

                                        <span class="text-sm text-gray-600">
                                            per kg
                                        </span>
                                    </div>

                                    {{-- Jumlah dan total --}}
                                    <p class="mt-1 text-sm text-gray-800">
                                        Jumlah:
                                        <strong x-text="pesan.jumlah + ' kg'"></strong>

                                        <span class="mx-1">·</span>

                                        Total
                                        <strong x-text="formatRupiah(pesan.total)"></strong>
                                    </p>

                                    {{-- Harga normal dan persentase --}}
                                    <p class="mt-1 text-sm leading-relaxed text-gray-500">
                                        Harga normal
                                        <span x-text="formatRupiah(hargaNormal) + '/kg'"></span>
                                        ·
                                        <strong
                                            class="font-medium"
                                            x-text="Math.max(0, Math.round((hargaNormal - pesan.harga) / hargaNormal * 100)) + '% di bawah harga normal'">
                                        </strong>
                                    </p>

                                    {{-- Pesan tambahan dari pembeli --}}
                                    <template x-if="pesan.isi">
                                        <div class="mt-2 border-t border-green-100 pt-2">
                                            <p
                                                class="break-words text-sm text-gray-800"
                                                x-text="pesan.isi">
                                            </p>
                                        </div>
                                    </template>

                                </div>

                                {{-- Waktu --}}
                                <p
                                    class="px-4 pb-2 text-right text-xs text-gray-400"
                                    x-text="'Anda · ' + pesan.waktu">
                                </p>

                            </div>
                        </div>
                    </template>

                </div>

            </template>


            {{-- TAMPILAN CHAT KOSONG --}}
            <div
                x-show="daftarPesan.length === 0"
                class="flex min-h-[180px] items-center justify-center"
            >
                <div class="text-center">

                    <p class="font-semibold text-gray-500">
                        Belum ada percakapan
                    </p>

                    <p class="mt-1 text-sm text-gray-400">
                        Mulai percakapan atau ajukan penawaran kepada petani
                    </p>

                </div>
            </div>

        </div>

    </div>

    </div>


    {{-- PANEL INPUT DI BAGIAN BAWAH --}}
    <div class="fixed bottom-0 left-1/2 z-50 w-full max-w-3xl -translate-x-1/2 border-t border-green-100 bg-white/95 p-4 backdrop-blur">


        {{-- FORMULIR PENAWARAN --}}
        <div
            x-show="tampilkanPenawaran"
            x-cloak
            class="mb-3 rounded-xl bg-white p-3"
            style="border: 2px solid #C8E8D0 !important;"
        >
            {{-- Judul dan tombol batal --}}
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900">
                    Ajukan Penawaran
                </h3>

                <button
                    type="button"`
                    @click="tampilkanPenawaran = false"
                    class="flex h-9 w-9 items-center justify-center rounded-full border-[3px] border-red-500 bg-white text-2xl font-bold leading-none text-red-500 transition hover:bg-red-50"
                    title="Tutup penawaran"
                >
                    ×
                </button>
            </div>

            {{-- Input harga dan jumlah --}}
            <div class="mt-3 grid grid-cols-2 gap-3">

                {{-- Harga per kg --}}
                <div class="min-w-0">
                    <label class="text-xs font-medium text-gray-700">
                        Harga per kg (Rp)
                    </label>

                    <div class="mt-1 flex items-center justify-between rounded-xl border border-green-100 bg-white p-1">

                        <button
                            type="button"
                            @click="hargaPenawaran = Math.max(1, hargaPenawaran - 500)"
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 font-bold text-gray-800 hover:bg-gray-200"
                        >
                            −
                        </button>

                        <input
                            type="number"
                            min="1"
                            x-model.number="hargaPenawaran"
                            class="w-full min-w-0 [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none border-0 bg-transparent px-1 text-center text-sm font-bold text-gray-900 outline-none focus:ring-0"
                        >

                        <button
                            type="button"
                            @click="hargaPenawaran += 500"
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 font-bold text-gray-800 hover:bg-gray-200"
                        >
                            +
                        </button>

                    </div>
                </div>

                {{-- Jumlah kg --}}
                <div class="min-w-0">
                    <label class="text-xs font-medium text-gray-700">
                        Jumlah (kg)
                    </label>

                    <div class="mt-1 flex items-center justify-between rounded-xl border border-green-100 bg-white p-1">

                        <button
                            type="button"
                            @click="jumlah = Math.max(1, jumlah - 1)"
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 font-bold text-gray-800 hover:bg-gray-200"
                        >
                            −
                        </button>

                        <input
                            type="number"
                            min="1"
                            x-model.number="jumlah"
                            class="w-full min-w-0 [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none border-0 bg-transparent px-1 text-center text-sm font-bold text-gray-900 outline-none focus:ring-0"
                        >

                        <button
                            type="button"
                            @click="jumlah += 1"
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 font-bold text-gray-800 hover:bg-gray-200"
                        >
                            +
                        </button>

                    </div>
                </div>

            </div>

            {{-- Total dan harga normal --}}
            <div class="mt-3 border-t border-amber-200 pt-2 text-xs text-gray-700">
                <p>
                    Total:
                    <span
                        class="font-bold text-green-700"
                        x-text="formatRupiah(totalPenawaran)"
                    ></span>
                </p>

                <p class="mt-1">
                    Harga normal:
                    <span
                        class="font-bold text-gray-900"
                        x-text="formatRupiah(hargaNormal) + '/kg'"
                    ></span>
                </p>
            </div>
        </div>

        {{-- CHAT BAR --}}
        <div class="flex items-center gap-2 rounded-full border border-gray-200 bg-gray-100 p-1.5 shadow-inner">

            {{-- INPUT PESAN --}}
            <input
                type="text"
                x-model="pesanBaru"
                @keydown.enter.prevent="kirimPesan()"
                placeholder="Tulis pesan..."
                class="min-w-0 flex-1 border-0 bg-transparent px-4 py-2 text-sm text-gray-800 outline-none placeholder:text-gray-400 focus:ring-0"
            >

            {{-- TOMBOL PENAWARAN --}}
            <button
                type="button"
                @click="tampilkanPenawaran = !tampilkanPenawaran"
                :class="tampilkanPenawaran
                    ? 'bg-amber-100 text-amber-700'
                    : 'bg-white text-gray-600'"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full shadow-sm transition hover:bg-amber-100"
                title="Ajukan penawaran"
            >
                <span class="text-lg">🏷️</span>
            </button>

            {{-- TOMBOL KIRIM --}}
            <button
                type="button"
                @click="kirimPesan()"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-700 text-white shadow-sm transition hover:bg-green-800"
                title="Kirim pesan"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="h-5 w-5"
                >
                    <path d="m22 2-7 20-4-9-9-4Z"/>
                    <path d="M22 2 11 13"/>
                </svg>
            </button>

        </div>

    </div>


</div>

@endsection
