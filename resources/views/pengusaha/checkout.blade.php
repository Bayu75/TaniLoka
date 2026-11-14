@extends('layouts.pengusaha')

@section('title', 'Checkout - TaniLoka')

@section('content')

@php
    // Data ongkos kirim sementara
    $ongkosKirim = [
        'kendaraan' => 15000,
        'jnt' => 12000,
        'ambil' => 0,
    ];

    // Menghitung subtotal seluruh produk
    $subtotal = $itemsCheckout->sum('subtotal');
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
            pengiriman: @js(
                $itemsCheckout->groupBy(fn($item) => $item['produk']->petani_id)
                    ->keys()
                    ->mapWithKeys(fn($id) => [
                        $id => [
                            'metode' => 'diantar',
                            'kurir' => 'kendaraan',
                        ]
                    ])
                    ->all()
            ),

            modalAlamat: false,
            alamatAktif: 0,
            tampilAlamat: false,

            daftarAlamat: @js($daftarAlamat),

            ongkosKirim: @js($ongkosKirim),

            hitungOngkir(pilihan) {
                if (pilihan.metode === 'ambil') return this.ongkosKirim.ambil;
                return this.ongkosKirim[pilihan.kurir];
            },

            get biayaKirim() {
                return Object.values(this.pengiriman).reduce(
                    (total, pilihan) => total + this.hitungOngkir(pilihan),
                    0
                );
            },

            get totalPembayaran() {
                return {{ $subtotal }} + this.biayaKirim;
            }
        }"
            class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_266px]"
    >
        {{-- Kolom Kiri: Konten Checkout --}}
        <div class="min-w-0">

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


    {{-- Daftar Produk yang Dipesan --}}
    <div class="mt-4 space-y-4">

        @foreach ($itemsCheckout->groupBy(fn($item) => $item['produk']->petani_id) as $petaniId => $itemsPetani)

            @php
                $petani = $itemsPetani->first()['produk']->petani;
                $subtotalPetani = $itemsPetani->sum('subtotal');
            @endphp

            <div class="overflow-hidden rounded-2xl border border-green-100 bg-white">

                {{-- Header Petani --}}
                <div class="border-b border-green-100 bg-green-50 p-4">
                    <h3 class="font-bold text-gray-800">
                        Pesanan dari {{ $petani->profilPetani->nama_usaha_tani ?? $petani->name ?? 'Petani' }}
                    </h3>

                    <p class="text-sm text-gray-600">
                        {{ $itemsPetani->count() }} produk ·
                        Rp {{ number_format($subtotalPetani, 0, ',', '.') }}
                    </p>
                </div>

                {{-- Daftar Produk --}}
                <div class="space-y-3 p-4">

                    @foreach ($itemsPetani as $item)

                        @php
                            $produk = $item['produk'];
                            $foto = $produk->fotoUtama?->path;
                        @endphp

                        <div class="flex items-center gap-3 border-b border-gray-100 pb-3 last:border-0 last:pb-0">

                            @if ($foto)
                                <img
                                    src="{{ \Illuminate\Support\Str::startsWith($foto, ['http://', 'https://']) ? $foto : asset($foto) }}"
                                    alt="{{ $produk->nama }}"
                                    class="h-16 w-16 shrink-0 rounded-lg object-cover"
                                >
                            @else
                                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-xs text-gray-400">
                                    Tanpa foto
                                </div>
                            @endif

                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-gray-800">
                                    {{ $produk->nama }}
                                </p>

                                <p class="text-sm text-gray-500">
                                    {{ $item['jumlah'] }} kg ×
                                    Rp {{ number_format($produk->harga_per_kg, 0, ',', '.') }}
                                </p>
                            </div>

                            <p class="shrink-0 text-sm font-bold text-gray-800">
                                Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                            </p>

                        </div>

                    @endforeach

                </div>

                {{-- Pilihan Metode Pengiriman --}}
                <div class="border-t border-green-100 p-4">

                    <h3 class="mb-1 font-bold text-gray-800">
                        Metode Pengiriman
                    </h3>

                    <p class="mb-4 text-sm text-gray-600">
                        Pesanan dari {{ $petani->profilPetani->nama_usaha_tani ?? $petani->name ?? 'Petani' }}
                    </p>

                    {{-- Pilihan jenis pengiriman --}}
                    <div class="grid grid-cols-2 gap-2">

                        <button
                            type="button"
                            @click="pengiriman[{{ $petaniId }}].metode = 'diantar'"
                            :class="pengiriman[{{ $petaniId }}].metode === 'diantar'
                                ? 'border-2 border-green-700 bg-green-50 text-green-800'
                                : 'border-2 border-green-100 bg-white text-gray-600'"
                            class="rounded-xl px-3 py-4 text-sm font-bold"
                        >
                            Diantar
                        </button>

                        <button
                            type="button"
                            @click="pengiriman[{{ $petaniId }}].metode = 'ambil'"
                            :class="pengiriman[{{ $petaniId }}].metode === 'ambil'
                                ? 'border-2 border-green-700 bg-green-50 text-green-800'
                                : 'border-2 border-green-100 bg-white text-gray-600'"
                            class="rounded-xl px-3 py-4 text-sm font-bold"
                        >
                            Ambil Sendiri
                        </button>

                    </div>

                    {{-- Pilihan kurir --}}
                    <div
                        x-show="pengiriman[{{ $petaniId }}].metode === 'diantar'"
                        x-transition
                        class="mt-3 space-y-3"
                    >

                        <button
                            type="button"
                            @click="pengiriman[{{ $petaniId }}].kurir = 'kendaraan'"
                            :class="pengiriman[{{ $petaniId }}].kurir === 'kendaraan'
                                ? 'border-2 border-green-700 bg-green-50'
                                : 'border-2 border-green-100 bg-white'"
                            class="flex w-full items-center gap-3 rounded-xl p-3 text-left"
                        >
                            <span class="text-xl">🚚</span>

                            <span class="flex-1">
                                <span class="block text-sm font-bold text-gray-800">
                                    Kendaraan Petani
                                </span>
                                <span class="block text-xs text-gray-500">
                                    Pengiriman mandiri oleh petani, hanya di Bali
                                </span>
                            </span>

                            <span class="text-sm font-bold text-green-700">
                                Rp15.000
                            </span>
                        </button>

                        <button
                            type="button"
                            @click="pengiriman[{{ $petaniId }}].kurir = 'jnt'"
                            :class="pengiriman[{{ $petaniId }}].kurir === 'jnt'
                                ? 'border-2 border-green-700 bg-green-50'
                                : 'border-2 border-green-100 bg-white'"
                            class="flex w-full items-center gap-3 rounded-xl p-3 text-left"
                        >
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
                                Rp12.000
                            </span>
                        </button>

                    </div>

                    {{-- Informasi ambil sendiri --}}
                    <div
                        x-show="pengiriman[{{ $petaniId }}].metode === 'ambil'"
                        x-transition
                        class="mt-3 rounded-xl bg-green-50 p-3 text-sm leading-relaxed text-gray-600"
                    >
                        Anda mengambil sendiri pesanan di kebun petani. Tanpa ongkos kirim.
                    </div>

                </div>

            </div>

        @endforeach

    </div>

    {{-- Tombol Lanjut ke Pembayaran --}}
    <a
        :href="'{{ route('pengusaha.pembayaran') }}?alamat_id=' + daftarAlamat[alamatAktif].id + '&pengiriman=' + encodeURIComponent(JSON.stringify(pengiriman)) + '&ongkir=' + biayaKirim"
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


                {{-- Daftar Produk --}}
                <div class="space-y-3 border-b border-gray-200 pb-3">

                    <p class="text-sm font-semibold text-gray-700">
                        Produk ({{ $itemsCheckout->count() }})
                    </p>

                    @foreach ($itemsCheckout as $item)

                        @php
                            $produk = $item['produk'];
                            $foto = $produk->fotoUtama?->path;
                        @endphp

                        <div class="flex items-center gap-3">

                            @if ($foto)
                                <img
                                    src="{{ \Illuminate\Support\Str::startsWith($foto, ['http://', 'https://']) ? $foto : asset($foto) }}"
                                    alt="{{ $produk->nama }}"
                                    class="h-14 w-14 shrink-0 rounded-lg object-cover"
                                >
                            @else
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-xs text-gray-400">
                                    Tanpa foto
                                </div>
                            @endif

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-700">
                                    {{ $produk->nama }}
                                </p>

                                <p class="text-xs text-gray-500">
                                    {{ $item['jumlah'] }} kg
                                </p>
                            </div>

                            <p class="shrink-0 text-sm font-bold text-gray-800">
                                Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                            </p>

                        </div>

                    @endforeach

                </div>

                {{-- Subtotal dan Ongkos Kirim --}}
                <div class="mt-3 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="text-gray-700">
                            Rp {{ number_format($subtotal, 0, ',', '.') }}
                        </span>
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
