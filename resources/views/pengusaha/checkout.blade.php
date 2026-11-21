
@extends('layouts.pengusaha')

@section('title', 'Checkout - TaniLoka')

@section('content')

    <x-page-header
        judul="Checkout"
        :kembali="route('pengusaha.keranjang')"
        lebar="max-w-4xl"
    />

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
                // Pilihan pengiriman masing-masing petani
                pengiriman: @js(
                    $perPetani->keys()->mapWithKeys(fn ($id) => [
                        (string) $id => [
                            'metode' => 'diantar',
                            'kurir' => 'kendaraan',
                        ],
                    ])->all()
                ),

                // Tarif pengiriman
                ongkosKirim: {
                    kendaraan: 15000,
                    ambil: 0,
                },

                // Menghitung ongkir setiap petani
                biayaKirim(petaniId) {
                    const pilihan = this.pengiriman[petaniId];

                    if (!pilihan || pilihan.metode === 'ambil') {
                        return 0;
                    }

                    return this.ongkosKirim[pilihan.kurir] ?? 0;
                },

                // Total seluruh ongkir
                get totalOngkir() {
                    return Object.keys(this.pengiriman).reduce(
                        (total, id) => total + this.biayaKirim(id),
                        0
                    );
                },

                // Menghitung PPN 10% dari subtotal produk
                get totalPPN() {
                    return @js($subtotal) * 0.10;
                },

                // Total pembayaran (subtotal + ongkir + PPN)
                get totalPembayaran() {
                    return @js($subtotal) + this.totalOngkir + this.totalPPN;
                },

                // Data alamat
                tampilAlamat: false,

                daftarAlamat: @js($daftarAlamat->map(fn ($alamat) => [
                    'id' => $alamat->id,
                    'nama' => $alamat->label,
                    'label' => $alamat->utama ? 'Utama' : '',
                    'kontak' => $alamat->nama_penerima . ' · ' . $alamat->no_hp,
                    'alamat' => $alamat->alamat_lengkap
                        . ', Kec. ' . $alamat->kecamatan
                        . ', ' . $alamat->kabupaten
                        . ($alamat->kode_pos ? ' ' . $alamat->kode_pos : ''),
                ])->values()),

                alamatAktif: @js(
                    $daftarAlamat->search(
                        fn ($alamat) => $alamat->id === $alamatUtama?->id
                    ) ?: 0
                ),
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

                        {{-- Kelola Alamat --}}
                        <a
                            href="{{ route('pengusaha.alamat') }}"
                            class="inline-block pt-3 text-sm font-bold text-primary underline"
                        >
                            Kelola Alamat
                        </a>

                    </div>

                    {{-- Alamat Terpilih --}}
                    <div
                        x-show="!tampilAlamat && daftarAlamat.length > 0"
                    >
                        <p
                            class="font-bold text-gray-800"
                            x-text="daftarAlamat[alamatAktif]?.nama ?? ''"
                        ></p>

                        <p
                            class="text-sm text-gray-600"
                            x-text="daftarAlamat[alamatAktif]?.kontak ?? ''"
                        ></p>

                        <p
                            class="text-sm text-gray-600"
                            x-text="daftarAlamat[alamatAktif]?.alamat ?? ''"
                        ></p>
                    </div>

                    {{-- Jika Belum Ada Alamat --}}
                    <div
                        x-show="daftarAlamat.length === 0"
                        class="rounded-xl bg-amber-50 p-4 text-sm text-amber-800"
                    >
                        Belum ada alamat pengiriman. Silakan tambahkan alamat terlebih dahulu.

                        <a
                            href="{{ route('pengusaha.alamat') }}"
                            class="font-bold underline"
                        >
                            Kelola Alamat
                        </a>
                    </div>

                </div>

                {{-- Pesanan Berdasarkan Petani --}}
                @foreach ($perPetani as $petaniId => $itemsPetani)
                    <div class="mt-4 overflow-hidden rounded-2xl border border-green-100 bg-white">

                        {{-- Header Pesanan --}}
                        <div class="border-b border-green-100 bg-green-50 p-4">
                            <h3 class="font-bold text-gray-800">
                                Pesanan dari {{ $itemsPetani->first()->produk->petani->name }}
                            </h3>

                            <p class="text-sm text-gray-600">
                                {{ $itemsPetani->count() }} produk ·
                                Rp {{ number_format(
                                    $itemsPetani->sum(fn ($item) => $item->subtotal),
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </p>
                        </div>

                        <div class="space-y-3 p-4">

                            {{-- Daftar Produk dari Petani --}}
                            <div class="space-y-3">
                                @foreach ($itemsPetani as $item)
                                    <div class="flex items-center gap-3 border-b border-gray-100 pb-3">

                                        {{-- Informasi Produk --}}
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold text-gray-800">
                                                {{ $item->produk->nama }}
                                            </p>

                                            <p class="text-xs text-gray-500">
                                                {{ (float) $item->jumlah_kg }} kg
                                            </p>

                                            <p class="text-xs text-gray-500">
                                                Rp {{ number_format(
                                                    $item->harga_per_kg ?? $item->produk->harga_per_kg,
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }} / kg
                                            </p>
                                        </div>

                                        {{-- Subtotal Produk --}}
                                        <div class="shrink-0 text-right">
                                            <p class="text-sm font-bold text-gray-800">
                                                Rp {{ number_format(
                                                    $item->subtotal,
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}
                                            </p>
                                        </div>

                                    </div>
                                @endforeach
                            </div>

                            {{-- Pilihan Metode Pengiriman --}}
                            <div class="grid grid-cols-2 gap-2">

                                <button
                                    type="button"
                                    @click="pengiriman['{{ $petaniId }}'].metode = 'diantar'"
                                    :class="pengiriman['{{ $petaniId }}'].metode === 'diantar'
                                        ? 'border-2 border-green-700 bg-green-50 text-green-800'
                                        : 'border-2 border-green-100 bg-white text-gray-600'"
                                    class="rounded-xl px-3 py-4 text-sm font-bold"
                                >
                                    Diantar
                                </button>

                                <button
                                    type="button"
                                    @click="pengiriman['{{ $petaniId }}'].metode = 'ambil'"
                                    :class="pengiriman['{{ $petaniId }}'].metode === 'ambil'
                                        ? 'border-2 border-green-700 bg-green-50 text-green-800'
                                        : 'border-2 border-green-100 bg-white text-gray-600'"
                                    class="rounded-xl px-3 py-4 text-sm font-bold"
                                >
                                    Ambil Sendiri (Siap Diambil)
                                </button>

                            </div>

                            {{-- Jika Memilih Diantar --}}
                            <div
                                x-show="pengiriman['{{ $petaniId }}'].metode === 'diantar'"
                                x-transition
                                class="space-y-3"
                            >

                                {{-- Kendaraan Petani --}}
                                <button
                                    type="button"
                                    @click="pengiriman['{{ $petaniId }}'].kurir = 'kendaraan'"
                                    :class="pengiriman['{{ $petaniId }}'].kurir === 'kendaraan'
                                        ? 'border-2 border-green-700 bg-green-50'
                                        : 'border-2 border-green-100 bg-white'"
                                    class="flex w-full items-center gap-3 rounded-xl p-3 text-left"
                                >
                                    <span
                                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-gray-400"
                                        :class="pengiriman['{{ $petaniId }}'].kurir === 'kendaraan'
                                            ? 'border-green-700'
                                            : ''"
                                    >
                                        <span
                                            x-show="pengiriman['{{ $petaniId }}'].kurir === 'kendaraan'"
                                            class="h-3 w-3 rounded-full bg-green-700"
                                        ></span>
                                    </span>

                                    <span class="text-xl">🚚</span>

                                    <span class="flex-1">
                                        <span class="block text-sm font-bold text-gray-800">
                                            Kendaraan Petani
                                        </span>
                                        <span class="block text-xs text-gray-500">
                                            Pengiriman langsung oleh petani. Khusus Bali.
                                        </span>
                                    </span>

                                    <span class="text-sm font-bold text-green-700">
                                        Rp 15.000
                                    </span>
                                </button>

                            </div>

                            {{-- Jika Memilih Ambil Sendiri --}}
                            <div
                                x-show="pengiriman['{{ $petaniId }}'].metode === 'ambil'"
                                x-transition
                                class="rounded-xl bg-green-50 p-3 text-sm leading-relaxed text-gray-600"
                            >
                                Anda mengambil sendiri pesanan di kebun petani.
                                Tanpa ongkos kirim. Petani akan menandai pesanan
                                "Siap Diambil".
                            </div>

                        </div>
                    </div>
                @endforeach

                {{-- Tombol Lanjut ke Pembayaran --}}
                <a
                    :href="'{{ route('pengusaha.pembayaran') }}?alamat_id='
                        + (daftarAlamat[alamatAktif]?.id ?? '')
                        + '&pengiriman='
                        + encodeURIComponent(JSON.stringify(pengiriman))"
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

                    {{-- Produk dari Keranjang Database --}}
                    @forelse ($items as $item)
                        <div class="flex items-center justify-between gap-2 border-b border-gray-200 pb-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm text-gray-600">
                                    {{ $item->produk->nama }}
                                </p>

                                <p class="text-xs text-gray-500">
                                    {{ (float) $item->jumlah_kg }} kg
                                </p>
                            </div>

                            <p class="shrink-0 text-sm font-bold text-gray-800">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">
                            Belum ada produk dalam keranjang.
                        </p>
                    @endforelse

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
                                x-text="totalOngkir === 0
                                    ? 'Gratis'
                                    : 'Rp ' + totalOngkir.toLocaleString('id-ID')"
                            ></span>
                        </div>

                        {{-- PPN 10% --}}
                        <div class="flex justify-between">
                            <span class="text-gray-600">PPN (10%)</span>
                            <span
                                class="text-gray-600"
                                x-text="'Rp ' + totalPPN.toLocaleString('id-ID')"
                            ></span>
                        </div>
                    </div>

                    {{-- Total Pembayaran --}}
                    <div class="mt-3 flex justify-between border-t border-gray-200 pt-3">
                        <span class="font-bold text-primary">
                            Total
                        </span>

                        <span
                            class="font-extrabold text-primary"
                            x-text="'Rp ' + totalPembayaran.toLocaleString('id-ID')"
                        ></span>
                    </div>

                </div>
            </div>

        </div>
    </div>

@endsection
