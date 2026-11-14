
@extends('layouts.pengusaha')

@section('title', 'Konfirmasi Pesanan - TaniLoka')


@php
    $metodePengiriman = $metodePengiriman ?? request('metode', 'diantar');
    $kurir = $kurir ?? request('kurir', 'kendaraan');
    $metodeBayar = request('pembayaran', 'transfer');

    $namaPembayaran = [
        'transfer' => 'Transfer Bank',
        'gopay' => 'GoPay',
        'ovo' => 'OVO',
        'cod' => 'COD (Bayar di Tempat)',
    ];

    $namaPengiriman = [
        'kendaraan' => 'Kendaraan Petani',
        'jnt' => 'J&T Express',
    ];
@endphp

@section('content')

<div class="mx-auto w-full max-w-4xl">

    {{-- Header Halaman --}}
    <div class="relative left-1/2 -mt-4 w-screen -translate-x-1/2 border-b border-green-200 bg-white">
        <div class="mx-auto max-w-4xl px-4 py-5">
            <div class="flex items-center gap-5">
                <a href="{{ route('pengusaha.pembayaran', request()->query()) }}"
                    class="font-semibold text-primary hover:text-green-700">
                    ‹ Kembali
                </a>

                <h1 class="text-xl font-extrabold text-gray-900">
                    Konfirmasi Pesanan
                </h1>
            </div>
        </div>
    </div>

    {{-- Progress Tahapan --}}
    <div class="relative left-1/2 w-screen -translate-x-1/2 border-b border-green-200 bg-white">
        <div class="mx-auto flex max-w-4xl items-center justify-center gap-3 px-4 py-3">

            <div class="flex items-center gap-2 text-sm font-semibold text-green-700">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-green-600 text-white">✓</span>
                Pengiriman
            </div>

            <div class="h-0.5 w-12 bg-green-600"></div>

            <div class="flex items-center gap-2 text-sm font-semibold text-green-700">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-green-600 text-white">✓</span>
                Pembayaran
            </div>

            <div class="h-0.5 w-12 bg-green-600"></div>

            <div class="flex items-center gap-2 text-sm font-semibold text-gray-900">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-500 text-white">3</span>
                Konfirmasi
            </div>

        </div>
    </div>

    {{-- Konten Konfirmasi --}}
    <div class="mx-auto grid w-full max-w-4xl grid-cols-1 items-start gap-6 pt-6 lg:grid-cols-[minmax(0,1fr)_266px]">

        {{-- Kolom Kiri --}}
        <div class="min-w-0 space-y-4">


    {{-- Detail Pesanan --}}
    <div class="space-y-4">

        @foreach ($itemsCheckout->groupBy(fn($item) => $item['produk']->petani_id) as $itemsPetani)
            @php
                $petani = $itemsPetani->first()['produk']->petani;
                $subtotalPetani = $itemsPetani->sum('subtotal');
            @endphp

            <div class="overflow-hidden rounded-2xl border border-green-200 bg-white">

                {{-- Informasi Penjual --}}
                <div class="border-b border-green-200 bg-green-50 px-5 py-4">
                    <h2 class="font-bold text-gray-900">
                        {{ $petani->profilPetani->nama_usaha_tani ?? $petani->name ?? 'Petani' }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ $itemsPetani->count() }} produk ·
                        Rp {{ number_format($subtotalPetani, 0, ',', '.') }}
                    </p>
                </div>

                {{-- Daftar Produk --}}
                <div class="divide-y divide-gray-100">
                    @foreach ($itemsPetani as $item)
                        @php
                            $produk = $item['produk'];
                            $foto = $produk->fotoUtama?->path;
                        @endphp

                        <div class="flex items-center justify-between gap-3 p-5">
                            <div class="flex min-w-0 items-center gap-3">

                                <div class="h-14 w-14 shrink-0 overflow-hidden rounded-xl bg-green-100">
                                    @if ($foto)
                                        <img
                                            src="{{ \Illuminate\Support\Str::startsWith($foto, ['http://', 'https://']) ? $foto : asset('storage/' . $foto) }}"
                                            alt="{{ $produk->nama }}"
                                            class="h-full w-full object-cover"
                                        >
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-xs text-gray-400">
                                            Tanpa foto
                                        </div>
                                    @endif
                                </div>

                                <div class="min-w-0">
                                    <h3 class="font-bold text-gray-900">
                                        {{ $produk->nama }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ $item['jumlah'] }} kg ×
                                        Rp {{ number_format($produk->harga_per_kg, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>

                            <p class="shrink-0 font-bold text-gray-900">
                                Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                            </p>
                        </div>
                    @endforeach
                </div>

            </div>
        @endforeach

        {{-- Subtotal dan Ongkir --}}
        <div class="rounded-2xl border border-green-200 bg-white px-5 py-4">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Subtotal</span>
                <span class="text-gray-700">
                    Rp {{ number_format($subtotal, 0, ',', '.') }}
                </span>
            </div>

            <div class="mt-2 flex justify-between text-sm">
                <span class="text-gray-600">Ongkos kirim</span>
                <span class="text-gray-700">
                    {{ $ongkir == 0 ? 'Gratis' : 'Rp ' . number_format($ongkir, 0, ',', '.') }}
                </span>
            </div>
        </div>

    </div>



    {{-- Alamat dan Informasi Pembayaran --}}
    <div class="rounded-2xl border border-green-100 bg-white p-4">

        <p class="text-sm text-gray-600">
            Alamat:
            <span class="font-medium text-gray-900">
                {{ $alamat->label ?: $alamat->nama_penerima }}
                · {{ $alamat->alamat_lengkap }},
                {{ $alamat->kecamatan }},
                {{ $alamat->kabupaten }}
                {{ $alamat->kode_pos }}
            </span>
        </p>

        <p class="mt-2 text-sm text-gray-600">
            Penerima:
            <span class="font-medium text-gray-900">
                {{ $alamat->nama_penerima }} · {{ $alamat->no_hp }}
            </span>
        </p>

        {{-- Rincian Pengiriman per Petani --}}
        <div class="mt-3 space-y-3">
            <p class="text-sm font-semibold text-gray-700">
                Pengiriman:
            </p>

            @foreach ($itemsCheckout->groupBy(fn($item) => $item['produk']->petani_id) as $petaniId => $itemsPetani)
                @php
                    $petani = $itemsPetani->first()['produk']->petani;
                    $pilihan = $pengirimanPerPetani[$petaniId];
                @endphp

                <div class="rounded-xl bg-green-50 p-3">
                    <p class="text-sm font-semibold text-gray-800">
                        {{ $petani->profilPetani->nama_usaha_tani ?? $petani->name ?? 'Petani' }}
                    </p>

                    <div class="mt-1 flex justify-between gap-2 text-sm">
                        <span class="text-gray-600">Metode</span>
                        <span class="font-medium text-gray-800">
                            @if ($pilihan['metode'] === 'ambil')
                                Ambil Sendiri
                            @elseif ($pilihan['kurir'] === 'jnt')
                                J&T Express
                            @else
                                Kendaraan Petani
                            @endif
                        </span>
                    </div>

                    <div class="mt-1 flex justify-between text-sm">
                        <span class="text-gray-600">Ongkos kirim</span>
                        <span class="font-medium text-gray-800">
                            {{ $pilihan['ongkir'] == 0 ? 'Gratis' : 'Rp ' . number_format($pilihan['ongkir'], 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <p class="mt-2 text-sm text-gray-600">
            Metode bayar:
            <span class="font-medium text-gray-900">
                {{ $namaPembayaran[$metodeBayar] ?? 'Transfer Bank' }}
            </span>
        </p>

        <p class="mt-2 text-sm leading-relaxed text-gray-600">
            Bayar setelah petani mengonfirmasi pesanan (maks. 24 jam).
            Petani yang tidak merespons membuat pesanan batal otomatis.
        </p>

    </div>

    {{-- Tombol Kirim Pesanan --}}
    <form action="{{ route('pengusaha.konfirmasi.store') }}" method="POST">
        @csrf

        <input
            type="hidden"
            name="pengiriman"
            value="{{ json_encode($pengirimanPerPetani) }}"
        >

        <input
            type="hidden"
            name="pembayaran"
            value="{{ $metodeBayar }}"
        >

        <input
            type="hidden"
            name="alamat_id"
            value="{{ $alamat->id }}"
        >

        <button
            type="submit"
            class="w-full rounded-xl bg-amber-500 px-4 py-4 text-center font-bold text-gray-900 transition hover:bg-amber-600"
        >
            Kirim Pesanan · Rp {{ number_format($total, 0, ',', '.') }}
        </button>
    </form>

</div>


        {{-- Kolom Kanan: Ringkasan --}}
        <div class="self-start lg:sticky lg:top-24">
            <div class="rounded-2xl border border-green-100 bg-white p-4">

                <h2 class="mb-4 text-lg font-bold text-gray-800">
                    Ringkasan
                </h2>


        {{-- Daftar Produk --}}
        <div class="space-y-3">
            <p class="text-sm font-semibold text-gray-700">
                Produk ({{ $itemsCheckout->count() }})
            </p>

            @foreach ($itemsCheckout as $item)
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm text-gray-600">
                            {{ $item['produk']->nama }}
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


        <div class="mt-3 space-y-2 border-t border-gray-200 pt-3 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600">Subtotal</span>
                <span class="text-gray-700">
                    Rp {{ number_format($subtotal, 0, ',', '.') }}
                </span>
            </div>

            <div class="flex justify-between">
                <span class="text-gray-600">Ongkos kirim</span>
                <span class="text-gray-700">
                    {{ $ongkir === 0 ? 'Gratis' : 'Rp ' . number_format($ongkir, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <div class="mt-3 flex justify-between border-t border-gray-200 pt-3">
            <span class="font-bold text-primary">Total</span>
            <span class="font-extrabold text-primary">
                Rp {{ number_format($total, 0, ',', '.') }}
            </span>
        </div>

    </div>

</div>

@endsection
