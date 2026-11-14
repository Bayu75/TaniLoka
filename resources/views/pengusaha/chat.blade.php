
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
            tampilkanPenawaran: {{ request('tawar') == '1' && ($penawaranAktif || !$percakapan->pesan->where('tipe', 'penawaran')->last() || !in_array($percakapan->pesan->where('tipe', 'penawaran')->last()->status_penawaran, ['disepakati', 'ditolak'])) ? 'true' : 'false' }},
            hargaPenawaran: @js((float) ($produk?->harga_per_kg ?? 0)),
            jumlah: @js((float) ($produk?->min_order_kg ?? 1)),
            minimalPembelian: @js((float) ($produk?->min_order_kg ?? 1)),
            hargaNormal: @js((float) ($produk?->harga_per_kg ?? 0)),
            pesanBaru: '',

            // Status negosiasi
            penawaranDisepakati: false,
            hargaDisepakati: null,
            jumlahDisepakati: null,
            waktuDisepakati: null,
            produkDiKeranjang: false,

            daftarPesan: @js($daftarPesan),

            get totalPenawaran() {
                return Math.max(0, Number(this.hargaPenawaran) || 0) *
                    Math.max(0, Number(this.jumlah) || 0);
            },

            formatRupiah(nilai) {
                return 'Rp ' + Number(nilai).toLocaleString('id-ID');
            },

            async kirimPesan() {
                if (this.tampilkanPenawaran) {
                    if (
                        Number(this.hargaPenawaran) <= 0 ||
                        Number(this.jumlah) < this.minimalPembelian
                    ) {
                        return;
                    }

                    this.$refs.formPenawaran.submit();
                    return;
                }

                const isi = this.pesanBaru.trim();

                if (!isi) return;

                try {
                    const form = this.$refs.formKirim;
                    const formData = new FormData(form);

                    formData.set('isi', isi);

                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    const hasil = await response.json();

                    if (!response.ok) {
                        throw new Error(hasil.message || 'Gagal mengirim pesan.');
                    }

                    // Tambahkan pesan baru tanpa reload halaman
                    this.daftarPesan.push(hasil.pesan);

                    // Kosongkan input
                    this.pesanBaru = '';

                    // Pertahankan posisi di paling bawah
                    this.scrollKeBawah();

                } catch (error) {
                    console.error('Gagal mengirim pesan:', error);
                    alert('Pesan gagal dikirim. Silakan coba lagi.');
                }
            },

            scrollKeBawah() {
                this.$nextTick(() => {
                    requestAnimationFrame(() => {
                        const area = this.$refs.areaPesan;

                        if (area) {
                            area.scrollTop = area.scrollHeight;
                        }
                    });
                });
            },

            terimaPenawaran(pesan) {
                if (this.penawaranDisepakati) return;

                pesan.status = 'disepakati';
                this.penawaranDisepakati = true;
                this.hargaDisepakati = pesan.harga;
                this.jumlahDisepakati = pesan.jumlah;
                this.waktuDisepakati = new Date().toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                this.$nextTick(() => {
                    const area = this.$refs.areaPesan;
                    area.scrollTop = area.scrollHeight;
                });
            },

            tambahKeKeranjang() {
                if (!this.penawaranDisepakati || this.produkDiKeranjang) return;

                this.produkDiKeranjang = true;
            }
    }"
>

    {{-- FORM TERSEMBUNYI UNTUK MENYIMPAN PESAN --}}
    <form
        x-ref="formKirim"
        method="POST"
        action="{{ route('pengusaha.pesan.kirim', $percakapan->id) }}"
        class="hidden"
    >
        @csrf
        <input type="hidden" name="produk" value="{{ $produk?->slug }}">
        <input type="hidden" name="isi" :value="pesanBaru">
    </form>

    {{-- HEADER CHAT --}}
    <div class="fixed top-[65px] left-0 right-0 z-40 w-full border-b border-green-100 bg-white">
        <div class="mx-auto flex w-full max-w-3xl items-center gap-5 px-4 py-3">

            <a
                href="{{ route('pengusaha.pesan') }}"
                class="flex shrink-0 items-center gap-2 font-semibold text-green-700 transition hover:text-green-800"
            >
                <span class="text-2xl leading-none">‹</span>
                <span>Kembali</span>
            </a>

            <div class="min-w-0">
                <h1 class="text-lg font-bold text-gray-900">
                    {{ $namaPetani }}
                </h1>
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
        x-cloak
        x-init="scrollKeBawah()"
        class="h-0 min-h-0 flex-1 overflow-x-hidden overflow-y-scroll overscroll-y-contain p-4"
        style="scroll-behavior: auto;"
    >

        {{-- INFORMASI PRODUK --}}
        <div class="mb-4 rounded-2xl border border-green-100 bg-white p-4">

            <div class="flex items-center gap-3">

                <img
                    src="{{ $produk?->fotoUtama?->path ? asset('storage/' . $produk->fotoUtama->path) : asset('images/products/bayam.png') }}"
                    alt="{{ $produk?->nama ?? 'Produk' }}"
                    class="h-16 w-16 shrink-0 rounded-xl object-cover"
                >

                <div class="min-w-0 flex-1">

                    <h2 class="font-bold text-gray-900">
                        {{ $produk?->nama ?? 'Belum ada produk dipilih' }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Harga petani:
                        {{ $produk ? 'Rp ' . number_format($produk->harga_per_kg, 0, ',', '.') . '/kg' : '-' }}
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

                <div :class="pesan.arah === 'masuk' ? 'flex justify-start' : 'flex justify-end'">

                    {{-- PESAN TEKS --}}
                    <template x-if="pesan.tipe === 'teks'">

                        <div
                            class="max-w-[85%] rounded-2xl p-3"
                            :class="pesan.arah === 'masuk'
                                ? 'rounded-tl-none bg-sky-100'
                                : 'rounded-tr-none bg-green-100'"
                        >

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
                        <div class="flex w-full"
                            :class="pesan.arah === 'masuk' ? 'justify-start' : 'justify-end'">

                            <div
                                class="w-full max-w-[420px] overflow-hidden rounded-2xl border-2 bg-white"
                                :class="pesan.arah === 'masuk'
                                    ? 'rounded-tl-none border-sky-200'
                                    : 'rounded-tr-none border-green-200'"
                            >
                                {{-- Header hijau --}}
                                <div
                                    class="flex items-center justify-between rounded-t-xl px-4 py-3"
                                    :class="pesan.arah === 'masuk' ? 'bg-sky-100' : 'bg-green-700'"
                                >
                                <h3
                                    class="font-bold"
                                    :class="pesan.arah === 'masuk' ? 'text-sky-900' : 'text-white'"
                                >
                                    Penawaran Harga
                                </h3>

                                    <span
                                        class="rounded-full bg-white px-3 py-1 text-xs font-semibold"
                                        :class="pesan.arah === 'masuk' ? 'text-sky-800' : 'text-green-800'"
                                        x-text="
                                            pesan.status === 'menunggu'
                                                ? 'Menunggu jawaban'
                                                : pesan.status === 'ditawar_balik'
                                                    ? 'Ditawar balik'
                                                    : 'Disepakati'
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
                                        <span x-text="formatRupiah(pesan.harga_normal) + '/kg'"></span>
                                        ·
                                        <strong
                                            class="font-medium"
                                            x-text="pesan.harga_normal > 0
                                                ? Math.round((pesan.harga_normal - pesan.harga) / pesan.harga_normal * 100) + '% di bawah harga normal'
                                                : 'Harga normal tidak tersedia'">
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
                                    x-text="(pesan.arah === 'keluar' ? 'Anda' : pesan.pengirim) + ' · ' + pesan.waktu">
                                </p>

                                <template x-if="pesan.arah === 'masuk' && pesan.status === 'menunggu' && !penawaranDisepakati">
                                    <div class="border-t border-green-100 p-3">
                                        <button
                                            type="button"
                                            @click="terimaPenawaran(pesan)"
                                            class="w-full rounded-xl bg-green-700 px-4 py-3 font-bold text-white transition hover:bg-green-800"
                                        >
                                            Terima
                                        </button>
                                    </div>
                                </template>

                            </div>
                        </div>
                    </template>

                </div>

            </template>

            {{-- NOTIFIKASI KESEPAKATAN --}}
            <div
                x-show="penawaranDisepakati"
                x-cloak
                class="my-5 flex justify-center"
            >
                <div class="w-full max-w-[365px] rounded-2xl border-2 border-green-700 bg-green-50 px-4 py-3 text-center">
                    <p class="font-bold text-green-800">
                        Disepakati oleh Anda
                    </p>

                    <p class="mt-1 text-sm text-green-800">
                        Penawaran
                        <strong x-text="formatRupiah(hargaDisepakati) + '/kg'"></strong>
                        untuk
                        <strong x-text="jumlahDisepakati + ' kg'"></strong>
                        diterima.
                    </p>

                    <p
                        class="mt-1 text-xs text-green-700"
                        x-text="waktuDisepakati"
                    ></p>


                    <form
                        method="POST"
                        action="{{ route('pengusaha.keranjang.tambah') }}"
                        class="mt-3"
                    >
                        @csrf

                        <input
                            type="hidden"
                            name="slug"
                            value="{{ $produk?->slug }}"
                        ><input
                            type="hidden"
                            name="slug"
                            value="bayam-hijau-organik"
                        >

                        <input
                            type="hidden"
                            name="jumlah"
                            :value="jumlahDisepakati"
                        >

                        <input
                            type="hidden"
                            name="harga_nego"
                            :value="hargaDisepakati"
                        >

                        <button
                            type="submit"
                            :disabled="produkDiKeranjang"
                            class="w-full rounded-xl bg-amber-500 px-4 py-3 font-bold text-gray-900 transition hover:bg-amber-600 disabled:cursor-not-allowed disabled:opacity-60"
                            x-text="produkDiKeranjang
                                ? 'Sudah Ditambahkan'
                                : 'Tambah ke Keranjang (harga nego)'"
                        >
                        </button>
                    </form>

                </div>
            </div>


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

        <form
            x-ref="formPenawaran"
            method="POST"
            action="{{ route('pengusaha.pesan.penawaran', $percakapan->id) }}"
            class="hidden"
        >
            @csrf
            <input type="hidden" name="produk" value="{{ $produk?->slug }}">
            <input type="hidden" name="harga_penawaran" :value="hargaPenawaran">
            <input type="hidden" name="jumlah_penawaran" :value="jumlah">
            <input type="hidden" name="isi" :value="pesanBaru">
        </form>

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
                            @click="jumlah = Math.max(1, Number(jumlah || 1) - 1)"
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

                <div class="flex items-center justify-between gap-2">
                    <p>
                        Total:
                        <span
                            class="font-bold text-green-700"
                            x-text="formatRupiah(totalPenawaran)"
                        ></span>
                    </p>

                    <p
                        x-show="Number(jumlah) < minimalPembelian"
                        x-cloak
                        class="text-right font-medium text-red-600"
                    >
                        <span x-text="'⚠ Minimal pembelian ' + minimalPembelian + ' kg.'"></span>
                    </p>
                </div>

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
                :class="penawaranDisepakati || !daftarPesan.some(p => p.tipe === 'penawaran' && p.status === 'menunggu')
                    ? 'cursor-not-allowed bg-gray-200 text-gray-400'
                    : tampilkanPenawaran
                        ? 'bg-amber-100 text-amber-700'
                        : 'bg-white text-gray-600'"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full shadow-sm transition"
                title="Ajukan penawaran"
            >
                <span class="text-lg">🏷️</span>
            </button>

            {{-- TOMBOL KIRIM --}}
            <button
                type="button"
                @click="kirimPesan()"
                :disabled="tampilkanPenawaran && Number(jumlah) < minimalPembelian"
                :class="tampilkanPenawaran && Number(jumlah) < minimalPembelian
                    ? 'cursor-not-allowed opacity-40'
                    : ''"
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
