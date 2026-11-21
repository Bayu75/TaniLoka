
@extends('layouts.petani')

@section('content')

<style>
    [x-cloak] {
        display: none !important;
    }

    input[type="number"] {
        appearance: textfield;
        -moz-appearance: textfield;
    }

    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
</style>

@if (! $percakapanAktif)

    {{-- DAFTAR PERCAKAPAN --}}
    <div class="max-w-4xl mx-auto px-4 space-y-4 pb-24">

        <x-page-header judul="Pesan" :kembali="false" lebar="max-w-4xl" />

        <p class="text-sm text-text-muted -mt-2">
            Percakapan dan negosiasi harga dengan pembeli.
        </p>

        @if (session('sukses'))
            <p class="text-sm bg-green-50 text-green-800 border border-green-200 rounded-xl px-4 py-2">
                {{ session('sukses') }}
            </p>
        @endif

        @if ($percakapan->isEmpty())
            <p class="text-sm text-text-muted bg-white border border-border rounded-2xl p-6 text-center">
                Belum ada percakapan dengan pembeli.
            </p>
        @else
            <div id="daftar-kontak" class="space-y-3">
                @foreach ($percakapan as $p)
                    <a
                        href="{{ route('petani.pesan.show', $p['id']) }}"
                        class="pesan-kontak block bg-white {{ $p['baru'] ? 'border-2 border-yellow-400' : 'border border-border' }} rounded-2xl p-4 hover:border-primary"
                    >
                        <div class="flex gap-3">
                            @if ($p['foto'])
                                <div
                                    class="w-14 h-14 rounded-xl bg-cover bg-center shrink-0 bg-green-100"
                                    style="background-image:url('{{ $p['foto'] }}')"
                                ></div>
                            @else
                                <div class="w-14 h-14 rounded-xl bg-green-700 text-white flex items-center justify-center font-bold shrink-0">
                                    {{ mb_substr($p['nama'], 0, 1) }}
                                </div>
                            @endif

                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between gap-2 items-start">
                                    <p class="font-semibold text-gray-900">
                                        {{ $p['nama'] }}
                                    </p>

                                    <div class="flex items-center gap-2 shrink-0">
                                        @if ($p['baru'])
                                            <span class="text-xs bg-yellow-400 text-gray-900 font-semibold px-2 py-0.5 rounded-full">
                                                Baru
                                            </span>
                                        @endif

                                        @if ($p['waktu'])
                                            <span class="text-xs text-text-muted whitespace-nowrap">
                                                {{ $p['waktu'] }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if ($p['produk'])
                                    <p class="text-sm font-medium text-gray-800 mt-0.5">
                                        {{ $p['produk'] }}
                                    </p>
                                @endif

                                <p class="text-sm text-text-muted mt-0.5 truncate">
                                    {{ $p['cuplikan'] }}
                                </p>

                                <span class="inline-block mt-2 text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full">
                                    {{ $p['status'] }}
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

@else

    {{-- DETAIL CHAT DENGAN ALPINE.JS --}}
    <div
        x-data="{
            tampilkanPenawaran: false,
            pesanBaru: '',
            mengirimPesan: false,
            errorKirim: '',

            daftarPesan: @js($daftarPesan),

            hargaPenawaran: @js((float) ($penawaranTerakhir?->harga_penawaran ?? $produk?->harga_per_kg ?? 0)),
            jumlahPenawaran: @js((float) ($penawaranTerakhir?->jumlah_penawaran ?? $produk?->min_order_kg ?? 1)),

            minimalPembelian: @js((float) ($produk?->min_order_kg ?? 1)),
            stokTersedia: @js((float) ($produk?->stok_kg ?? 0)),
            hargaNormal: @js((float) ($produk?->harga_per_kg ?? 0)),

            produkTersedia: @js((bool) $produk),
            negosiasiAktif: @js((bool) $negosiasiAktif),

            get totalPenawaran() {
                return Math.max(0, Number(this.hargaPenawaran) || 0) *
                    Math.max(0, Number(this.jumlahPenawaran) || 0);
            },

            get penawaranTerakhir() {
                return [...this.daftarPesan]
                    .filter(p => p.tipe === 'penawaran')
                    .sort((a, b) => Number(b.id) - Number(a.id))[0];
            },

            get menungguJawabanPengusaha() {
                return this.penawaranTerakhir?.status === 'menunggu' &&
                    this.penawaranTerakhir?.arah === 'keluar';
            },

            get penawaranDisepakati() {
                return this.penawaranTerakhir?.status === 'disepakati';
            },

            get penawaranDitolak() {
                return this.penawaranTerakhir?.status === 'ditolak';
            },

            formatRupiah(nilai) {
                return 'Rp' + Number(nilai || 0).toLocaleString('id-ID');
            },

            async kirimPesan() {
                const isi = this.pesanBaru.trim();

                if (!isi || this.mengirimPesan) return;

                this.errorKirim = '';
                this.mengirimPesan = true;

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

                    if (!response.ok || !hasil.success) {
                        throw new Error(hasil.message || 'Gagal mengirim pesan.');
                    }

                    this.daftarPesan.push(hasil.pesan);
                    this.pesanBaru = '';
                    this.scrollKeBawah();

                } catch (error) {
                    console.error('Gagal mengirim pesan:', error);
                    this.errorKirim = error.message || 'Pesan gagal dikirim.';
                } finally {
                    this.mengirimPesan = false;
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
            }
        }"
        x-init="scrollKeBawah()"
        class="min-h-screen"
    >

        {{-- FORM TERSEMBUNYI UNTUK AJAX CHAT --}}
        <form
            x-ref="formKirim"
            method="POST"
            action="{{ route('petani.pesan.kirim', $percakapanAktif['id']) }}"
            class="hidden"
        >
            @csrf
            <input type="hidden" name="isi" :value="pesanBaru">
        </form>

        {{-- HEADER CHAT --}}
        <div class="fixed left-0 right-0 top-[65px] z-40 w-full border-b border-green-100 bg-white">
            <div class="mx-auto flex w-full max-w-3xl items-center gap-5 px-4">

                <x-page-header judul="Percakapan dengan pembeli" :kembali="route('petani.pesan')" lebar="max-w-4xl" />
                
                <div class="min-w-0">
                    <h1 class="text-lg font-bold text-gray-900">
                        {{ $namaPengusaha }}
                    </h1>

                    <p class="text-xs text-gray-500">
                        Percakapan dengan pembeli
                    </p>
                </div>
            </div>
        </div>

        {{-- NOTIFIKASI --}}
        @if (session('sukses'))
            <div class="fixed left-1/2 top-[130px] z-50 w-full max-w-3xl -translate-x-1/2 px-4">
                <p class="rounded-xl border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">
                    {{ session('sukses') }}
                </p>
            </div>
        @endif

        @if (session('gagal'))
            <div class="fixed left-1/2 top-[130px] z-50 w-full max-w-3xl -translate-x-1/2 px-4">
                <p class="rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700">
                    {{ session('gagal') }}
                </p>
            </div>
        @endif

        @if ($errors->any())
            <div class="fixed left-1/2 top-[130px] z-50 w-full max-w-3xl -translate-x-1/2 px-4">
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- AREA PERCAKAPAN --}}
        <div
            :style="tampilkanPenawaran
                ? 'height: calc(100dvh - 424px)'
                : 'height: calc(100dvh - 198px)'"
            class="fixed left-1/2 top-[124px] z-10 flex w-full max-w-4xl -translate-x-1/2 flex-col overflow-hidden border-x border-green-100 bg-white"
        >

            {{-- AREA SCROLL PESAN --}}
            <div
                x-ref="areaPesan"
                x-init="scrollKeBawah()"
                class="h-0 min-h-0 flex-1 overflow-x-hidden overflow-y-auto overscroll-y-contain px-4 py-6"
                style="scroll-behavior: auto;"
            >

                {{-- INFORMASI PRODUK --}}
                @if ($produk)
                    <div class="mb-4 rounded-2xl border border-green-100 bg-white p-4">
                        <div class="flex items-center gap-3">

                            @if ($produk->fotoUtama)
                                <img
                                    src="{{ $produk->fotoUtama->url }}"
                                    alt="{{ $produk->nama }}"
                                    class="h-16 w-16 shrink-0 rounded-xl object-cover"
                                >
                            @else
                                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl bg-green-50 text-3xl">
                                    🌱
                                </div>
                            @endif

                            <div class="min-w-0 flex-1">
                                <h2 class="font-bold text-gray-900">
                                    {{ $produk->nama }}
                                </h2>

                                <p class="mt-1 text-sm text-gray-500">
                                    Harga normal:
                                    Rp{{ number_format($produk->harga_per_kg, 0, ',', '.') }}/kg
                                </p>

                                <p class="text-sm text-gray-500">
                                    Minimal pembelian: {{ $produk->min_order_kg }} kg
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- DAFTAR PESAN ALPINE --}}
                <div class="space-y-4">

                    <template
                        x-for="(pesan, index) in [...daftarPesan].sort((a, b) => Number(a.id) - Number(b.id))"
                        :key="pesan.id ?? index"
                    >

                        <div :class="pesan.arah === 'masuk' ? 'flex justify-start' : 'flex justify-end'">

                            {{-- PESAN TEKS --}}
                            <template x-if="pesan.tipe !== 'penawaran'">
                                <div
                                    class="w-fit min-w-0 max-w-[85%] rounded-2xl p-3"
                                    :class="pesan.arah === 'masuk'
                                        ? 'rounded-tl-none bg-sky-100'
                                        : 'rounded-tr-none bg-green-100'"
                                >
                                    <p
                                        class="break-words whitespace-pre-wrap [overflow-wrap:anywhere] text-sm text-gray-800"
                                        x-text="pesan.isi"
                                    ></p>

                                    <p
                                        class="mt-1 text-right text-xs text-gray-400"
                                        x-text="(pesan.arah === 'keluar' ? 'Anda' : pesan.pengirim) + ' · ' + pesan.waktu"
                                    ></p>
                                </div>
                            </template>

                            {{-- KARTU PENAWARAN --}}
                            <template x-if="pesan.tipe === 'penawaran'">
                                <div class="flex w-full"
                                    :class="pesan.arah === 'masuk' ? 'justify-start' : 'justify-end'">

                                    <div
                                        class="w-full max-w-[420px] overflow-hidden rounded-2xl border-2 bg-white"
                                        :class="pesan.arah === 'masuk'
                                            ? 'rounded-tl-none border-sky-200'
                                            : 'rounded-tr-none border-green-200'"
                                    >

                                        {{-- HEADER KARTU --}}
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
                                                :class="pesan.status === 'disepakati'
                                                    ? 'text-green-800'
                                                    : pesan.status === 'ditolak'
                                                        ? 'text-red-700'
                                                        : pesan.status === 'ditawar'
                                                            ? 'text-blue-700'
                                                            : 'text-amber-700'"
                                                x-text="pesan.status === 'menunggu'
                                                    ? 'Menunggu jawaban'
                                                    : pesan.status === 'ditolak'
                                                        ? 'Ditolak'
                                                        : pesan.status === 'ditawar'
                                                            ? 'Ditawar'
                                                            : 'Disepakati'"
                                            ></span>
                                        </div>

                                        {{-- DETAIL KARTU --}}
                                        <div class="p-4">

                                            <div class="flex flex-wrap items-baseline gap-2">
                                                <span
                                                    class="text-2xl font-extrabold text-green-800"
                                                    x-text="formatRupiah(pesan.harga)"
                                                ></span>

                                                <span class="text-sm text-gray-600">
                                                    per kg
                                                </span>
                                            </div>

                                            <p class="mt-1 text-sm text-gray-800">
                                                Jumlah:
                                                <strong x-text="Number(pesan.jumlah).toLocaleString('id-ID') + ' kg'"></strong>

                                                <span class="mx-1">·</span>

                                                Total:
                                                <strong x-text="formatRupiah(pesan.total)"></strong>
                                            </p>

                                            <p class="mt-1 text-sm leading-relaxed text-gray-500">
                                                Harga normal:
                                                <span x-text="formatRupiah(pesan.harga_normal) + '/kg'"></span>
                                                ·
                                                <strong
                                                    class="font-medium"
                                                    x-text="Number(pesan.harga_normal) > 0
                                                        ? Math.round((pesan.harga_normal - pesan.harga) / pesan.harga_normal * 100) + '% di bawah harga normal'
                                                        : 'Harga normal tidak tersedia'"
                                                ></strong>
                                            </p>

                                            <template x-if="pesan.isi">
                                                <div class="mt-2 border-t border-green-100 pt-2">
                                                    <p
                                                        class="break-words whitespace-pre-wrap text-sm text-gray-800"
                                                        x-text="pesan.isi"
                                                    ></p>
                                                </div>
                                            </template>
                                        </div>

                                        {{-- WAKTU --}}
                                        <p
                                            class="px-4 pb-2 text-right text-xs text-gray-400"
                                            x-text="(pesan.arah === 'keluar' ? 'Anda' : pesan.pengirim) + ' · ' + pesan.waktu"
                                        ></p>

                                        {{-- TOMBOL TERIMA DAN TOLAK --}}
                                        <template x-if="pesan.arah === 'masuk' && pesan.status === 'menunggu'">
                                            <div class="flex gap-2 border-t border-green-100 p-3">

                                                <form
                                                    method="POST"
                                                    :action="'{{ route('petani.pesan.penawaran.terima', ['percakapan' => $percakapanAktif['id'], 'pesan' => '__ID__']) }}'.replace('__ID__', pesan.id)"
                                                    class="flex-1"
                                                >
                                                    @csrf

                                                    <button
                                                        type="submit"
                                                        class="w-full rounded-xl bg-green-700 px-4 py-3 font-bold text-white transition hover:bg-green-800"
                                                    >
                                                        Terima
                                                    </button>
                                                </form>

                                                <form
                                                    method="POST"
                                                    :action="'{{ route('petani.pesan.penawaran.tolak', ['percakapan' => $percakapanAktif['id'], 'pesan' => '__ID__']) }}'.replace('__ID__', pesan.id)"
                                                    class="flex-1"
                                                >
                                                    @csrf

                                                    <button
                                                        type="submit"
                                                        class="w-full rounded-xl border border-red-300 bg-white px-4 py-3 font-bold text-red-600 transition hover:bg-red-50"
                                                    >
                                                        Tolak
                                                    </button>
                                                </form>

                                            </div>
                                        </template>

                                    </div>
                                </div>
                            </template>

                        </div>
                    </template>

                </div>

                {{-- NOTIFIKASI PENAWARAN DISEPAKATI --}}
                <div
                    x-show="penawaranDisepakati"
                    x-cloak
                    class="my-5 flex justify-center"
                >
                    <div class="w-full max-w-[365px] rounded-2xl border-2 border-green-700 bg-green-50 px-4 py-3 text-center">
                        <p class="font-bold text-green-800">
                            Penawaran Disepakati
                        </p>

                        <p class="mt-1 text-sm text-green-800">
                            Penawaran terakhir telah disepakati.
                        </p>
                    </div>
                </div>

                {{-- NOTIFIKASI PENAWARAN DITOLAK --}}
                <div
                    x-show="penawaranDitolak"
                    x-cloak
                    class="my-5 flex justify-center"
                >
                    <div class="w-full max-w-[365px] rounded-2xl border-2 border-red-600 bg-red-50 px-4 py-3 text-center">
                        <p class="font-bold text-red-700">
                            Penawaran Ditolak
                        </p>

                        <p class="mt-1 text-sm text-red-700">
                            Penawaran terakhir telah ditolak.
                        </p>
                    </div>
                </div>

                {{-- CHAT KOSONG --}}
                <div
                    x-show="daftarPesan.length === 0"
                    x-cloak
                    class="flex min-h-[180px] items-center justify-center"
                >
                    <div class="text-center">
                        <p class="font-semibold text-gray-500">
                            Belum ada percakapan
                        </p>
                    </div>
                </div>

            </div>
        </div>

        {{-- PANEL INPUT BAWAH --}}
        <div class="fixed bottom-0 left-1/2 z-50 w-full max-w-3xl -translate-x-1/2 border-t border-green-100 bg-white/95 p-4 backdrop-blur">

            {{-- FORM PENAWARAN --}}
            @if ($negosiasiAktif && $produk)

                <div
                    x-show="tampilkanPenawaran"
                    x-cloak
                    x-transition
                    class="mb-3 rounded-2xl border-2 border-green-200 bg-white p-3"
                >

                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="font-bold text-gray-900">
                            Ajukan Penawaran
                        </h3>

                        <button
                            type="button"
                            @click="tampilkanPenawaran = false"
                            class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-red-500 text-xl font-bold text-red-500"
                        >
                            &times;
                        </button>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('petani.pesan.penawaran', $percakapanAktif['id']) }}"
                    >
                        @csrf

                        <div class="grid grid-cols-2 gap-3">

                            {{-- HARGA --}}
                            <div class="min-w-0">
                                <label class="text-xs font-medium text-gray-700">
                                    Harga per kg (Rp)
                                </label>

                                <div class="mt-1 flex items-center gap-1 rounded-xl border border-green-100 p-1">
                                    <button
                                        type="button"
                                        @click="hargaPenawaran = Math.max(1, Number(hargaPenawaran) - 500)"
                                        class="h-8 w-8 shrink-0 rounded-lg bg-gray-100 font-bold"
                                    >
                                        −
                                    </button>

                                    <input
                                        type="number"
                                        name="harga_penawaran"
                                        min="1"
                                        step="1"
                                        x-model.number="hargaPenawaran"
                                        class="w-full min-w-0 border-0 bg-transparent px-1 text-center text-sm font-bold focus:ring-0"
                                        required
                                    >

                                    <button
                                        type="button"
                                        @click="hargaPenawaran = Number(hargaPenawaran) + 500"
                                        class="h-8 w-8 shrink-0 rounded-lg bg-gray-100 font-bold"
                                    >
                                        +
                                    </button>
                                </div>
                            </div>

                            {{-- JUMLAH --}}
                            <div class="min-w-0">
                                <label class="text-xs font-medium text-gray-700">
                                    Jumlah (kg)
                                </label>

                                <div class="mt-1 flex items-center gap-1 rounded-xl border border-green-100 p-1">
                                    <button
                                        type="button"
                                        @click="jumlahPenawaran = Math.max(minimalPembelian, Number(jumlahPenawaran) - 1)"
                                        class="h-8 w-8 shrink-0 rounded-lg bg-gray-100 font-bold"
                                    >
                                        −
                                    </button>

                                    <input
                                        type="number"
                                        name="jumlah_penawaran"
                                        :min="minimalPembelian"
                                        :max="stokTersedia"
                                        step="0.01"
                                        x-model.number="jumlahPenawaran"
                                        class="w-full min-w-0 border-0 bg-transparent px-1 text-center text-sm font-bold focus:ring-0"
                                        required
                                    >

                                    <button
                                        type="button"
                                        @click="jumlahPenawaran = Math.min(stokTersedia, Number(jumlahPenawaran) + 1)"
                                        class="h-8 w-8 shrink-0 rounded-lg bg-gray-100 font-bold"
                                    >
                                        +
                                    </button>
                                </div>
                            </div>

                        </div>

                        <div class="mt-3 border-t border-amber-200 pt-2 text-xs text-gray-700">
                            <p>
                                Total:
                                <strong
                                    class="text-green-700"
                                    x-text="formatRupiah(totalPenawaran)"
                                ></strong>
                            </p>

                            <p class="mt-1">
                                Harga normal:
                                <strong x-text="formatRupiah(hargaNormal) + '/kg'"></strong>
                            </p>
                        </div>

                        <button
                            type="submit"
                            :disabled="Number(hargaPenawaran) <= 0 || Number(jumlahPenawaran) < minimalPembelian || Number(jumlahPenawaran) > stokTersedia || menungguJawabanPengusaha"
                            class="mt-3 w-full rounded-xl bg-green-700 py-2.5 text-sm font-bold text-white hover:bg-green-800 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Kirim Penawaran
                        </button>

                        <p
                            x-show="menungguJawabanPengusaha"
                            class="mt-2 text-xs text-amber-700"
                        >
                            Tunggu jawaban pembeli atas penawaran sebelumnya.
                        </p>

                    </form>
                </div>

            @endif

            {{-- CHAT BAR --}}
            <form
                @submit.prevent="kirimPesan()"
                class="flex items-center gap-2 rounded-full border border-gray-200 bg-gray-100 p-1.5"
            >

                <input
                    type="text"
                    x-model="pesanBaru"
                    placeholder="Tulis pesan..."
                    maxlength="2000"
                    class="min-w-0 flex-1 border-0 bg-transparent px-4 py-2 text-sm focus:ring-0"
                >

                {{-- TOMBOL TAG --}}
                @if ($negosiasiAktif && $produk)
                    <button
                        type="button"
                        @click="tampilkanPenawaran = !tampilkanPenawaran"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white shadow-sm transition hover:bg-amber-100"
                        title="Ajukan penawaran"
                    >
                        <span class="text-lg">🏷️</span>
                    </button>
                @else
                    <button
                        type="button"
                        disabled
                        class="flex h-10 w-10 shrink-0 cursor-not-allowed items-center justify-center rounded-full bg-gray-200 text-gray-400"
                        title="Negosiasi belum dimulai"
                    >
                        <span class="text-lg">🏷️</span>
                    </button>
                @endif

                {{-- TOMBOL KIRIM --}}
                <button
                    type="submit"
                    :disabled="!pesanBaru.trim() || mengirimPesan"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-700 text-white transition hover:bg-green-800 disabled:opacity-50"
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

            </form>

            <p
                x-show="errorKirim"
                x-text="errorKirim"
                class="mt-2 px-2 text-xs text-red-600"
            ></p>

            @error('isi')
                <p class="mt-2 px-2 text-xs text-red-600">{{ $message }}</p>
            @enderror

        </div>

    </div>

@endif

@endsection
