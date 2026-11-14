
@extends('layouts.pengusaha')

@section('title', 'Detail Produk - TaniLoka')

@section('content')

{{-- KEMBALI KE KATALOG --}}
<div class="mx-auto mb-5 max-w-4xl pt-5">
    <a href="{{ route('pengusaha.katalog') }}"
       class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 transition hover:text-green-700">
        <span class="text-xl leading-none">‹</span>
        <span>Kembali ke Katalog</span>
    </a>
</div>

<div class="mx-auto max-w-4xl">

    {{-- INFORMASI UTAMA --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

        {{-- FOTO PRODUK --}}
        <div>
            <div class="aspect-square overflow-hidden rounded-2xl bg-[#EBF5EB]">
                <img
                    src="{{ $produk['gambar'] }}"
                    alt="{{ $produk['nama'] }}"
                    class="h-full w-full object-cover"
                >
            </div>

            {{-- THUMBNAIL --}}
            <div class="mt-3 flex gap-2">
                @for ($i = 0; $i < 3; $i++)
                    <div class="h-16 w-16 overflow-hidden rounded-xl border-2
                        {{ $i === 0 ? 'border-[#2E7D32]' : 'border-[#D4E8D0]' }}">
                        <img
                            src="{{ $produk['gambar'] }}"
                            alt="Foto {{ $produk['nama'] }}"
                            class="h-full w-full object-cover"
                        >
                    </div>
                @endfor
            </div>
        </div>

        {{-- INFORMASI PRODUK --}}
        <div class="flex flex-col gap-4">

            {{-- KATEGORI DAN STOK --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full bg-[#EBF5EB] px-3 py-1 text-xs font-bold text-[#2E7D32]">
                    {{ $produk['kategori'] }}
                </span>

                @if ($produk['stok'] > 0)
                    <span class="rounded-full bg-[#DCFCE7] px-3 py-1 text-xs font-bold text-[#15803D]">
                        ● Tersedia
                    </span>
                @else
                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-600">
                        ● Stok Habis
                    </span>
                @endif
            </div>

            <h1 class="text-2xl font-extrabold text-[#1A2A1A]">
                {{ $produk['nama'] }}
            </h1>

            {{-- HARGA --}}
            <div class="rounded-2xl bg-[#EBF5EB] p-4">
                <div class="text-2xl font-extrabold text-[#2E7D32]">
                    Rp{{ number_format($produk['harga'], 0, ',', '.') }}
                    <span class="text-sm font-normal text-[#5A7A5A]">/kg</span>
                </div>

                <p class="mt-1 text-sm text-[#5A7A5A]">
                    Minimal pesan:
                    <strong class="text-[#1A2A1A]">
                        {{ $produk['minimal'] }} kg
                    </strong>
                    ·
                    Stok:
                    <strong class="text-[#1A2A1A]">
                        {{ $produk['stok'] }} kg
                    </strong>
                </p>

                <p class="mt-3 text-xs text-[#8A762B]">
                    ★ Harga dapat dinegosiasikan untuk pembelian dalam jumlah besar.
                </p>
            </div>

            {{-- INFORMASI PETANI --}}
            <div class="rounded-2xl border border-[#D4E8D0] bg-white p-4">
                <div class="flex items-center gap-3">

                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#2E7D32] font-bold text-white">
                        {{ strtoupper(substr($produk['petani'], 0, 1)) }}
                    </div>

                    <div class="min-w-0 flex-1">
    <p class="font-bold text-[#1A2A1A]">
        {{ $produk['petani'] }}
    </p>

    <p class="text-xs text-[#5A7A5A]">
        📍 {{ $produk['kabupaten'] }}, Bali
    </p>

    <p class="mt-1 text-sm text-[#EAB308]">
        ★★★★★
        <span class="text-xs text-[#5A7A5A]">
            {{ $produk['rating'] }} · Petani lokal
        </span>
    </p>
</div>

{{-- TOMBOL KUNJUNGI TOKO --}}
<a href="{{ route('pengusaha.toko', [
    'petani' => \Illuminate\Support\Str::slug($produk['petani'])
]) }}"
   class="shrink-0 text-xs font-bold text-[#2E7D32] hover:underline">
    Kunjungi Toko →
</a>
                </div>
            </div>

            {{-- INFORMASI PRODUK --}}
            <div>
                <div class="border-b border-[#D4E8D0] pb-2">
                    <span class="border-b-2 border-[#2E7D32] pb-2 text-sm font-bold text-[#2E7D32]">
                        Informasi Produk
                    </span>
                    <span class="ml-5 text-sm font-semibold text-[#8A9A8A]">
                        Pengiriman
                    </span>
                </div>

                <p class="mt-3 text-sm leading-relaxed text-[#435343]">
                    {{ $produk['deskripsi'] }}
                </p>

                <div class="mt-3 rounded-xl bg-[#EEF5EE] p-3">
                    <p class="text-xs font-bold text-[#789078]">
                        Periode Panen
                    </p>
                    <p class="mt-1 text-sm text-[#435343]">
                        Tersedia sesuai jadwal panen petani.
                    </p>
                </div>

                <div class="mt-2 rounded-xl bg-[#EEF5EE] p-3">
                    <p class="text-xs font-bold text-[#789078]">
                        Kabupaten
                    </p>
                    <p class="mt-1 text-sm text-[#435343]">
                        {{ $produk['kabupaten'] }}, Bali
                    </p>
                </div>
            </div>

        </div>
    </div>

    {{-- PILIHAN PEMBELIAN --}}
    @if ($produk['stok'] > 0)

        <form action="{{ route('pengusaha.keranjang.tambah') }}"
              method="POST"
              class="mt-7 rounded-2xl border border-[#D4E8D0] bg-white p-4">

            @csrf

            <input type="hidden" name="slug" value="{{ $slug }}">

            {{-- JUMLAH DAN TOTAL --}}
            <div class="flex flex-wrap items-center justify-between gap-4">

                <div>
                    <label for="jumlah"
                           class="mb-2 block text-sm font-bold text-[#1A2A1A]">
                        Jumlah Pembelian
                    </label>

                    <div class="flex items-center gap-2">

                        <button type="button"
                                onclick="ubahJumlah(-1)"
                                class="h-10 w-10 rounded-xl bg-[#F1F5F1] font-bold text-[#2E7D32] hover:bg-[#DDEBDD]">
                            −
                        </button>

                        <input
                        type="number"
                        id="jumlah"
                        name="jumlah"
                        min="{{ $produk['minimal'] }}"
                        max="{{ $produk['stok'] }}"
                        value="{{ $produk['minimal'] }}"
                        required
                        oninput="hitungTotal(); this.setCustomValidity('')"
                        class="h-10 w-20 rounded-xl border border-[#D4E8D0] text-center font-bold text-[#1A2A1A]"
                    >

                        <span class="text-sm text-[#5A7A5A]">kg</span>

                        <button type="button"
                                onclick="ubahJumlah(1)"
                                class="h-10 w-10 rounded-xl bg-[#F1F5F1] font-bold text-[#2E7D32] hover:bg-[#DDEBDD]">
                            +
                        </button>

                    </div>

                    <p class="mt-2 text-xs text-[#789078]">
                        Minimal {{ $produk['minimal'] }} kg.
                    </p>
                </div>

                <div class="text-sm font-semibold text-[#5A7A5A]">
                    Total
                    <span id="totalHarga"
                          class="ml-1 text-base font-extrabold text-[#2E7D32]">
                        Rp{{ number_format($produk['harga'] * $produk['minimal'], 0, ',', '.') }}
                    </span>
                </div>

            </div>

            {{-- TOMBOL PEMBELIAN --}}
            <div class="mt-5 grid grid-cols-1 gap-3 md:grid-cols-2">

                <button type="submit"
                        name="aksi"
                        value="keranjang"
                        class="min-h-12 rounded-xl border-2 border-[#2E7D32] bg-white px-4 font-bold text-[#2E7D32] transition hover:bg-[#EBF5EB]">
                    🛒 Tambah ke Keranjang
                </button>

                <button type="submit"
                        name="aksi"
                        value="beli"
                        class="min-h-12 rounded-xl bg-[#F4B400] px-4 font-bold text-[#1A2A00] transition hover:bg-[#E6A800]">
                    Beli Sekarang
                </button>

                <a href="{{ route('pengusaha.pesan', ['produk' => $slug]) }}"
                   class="flex min-h-12 items-center justify-center rounded-xl border-2 border-[#F4D36B] bg-[#FFF9E6] px-4 font-bold text-[#806000] transition hover:bg-[#FFF1C2] md:col-span-2">
                    Tawar Harga
                </a>

            </div>

        </form>

    @else

        <div class="mt-7 rounded-2xl border border-red-200 bg-red-50 p-5">
            <p class="font-bold text-red-600">
                Stok Habis
            </p>
            <p class="mt-1 text-sm text-red-500">
                Maaf, produk ini sedang tidak tersedia.
            </p>
        </div>

    @endif

</div>

{{-- SCRIPT JUMLAH DAN TOTAL HARGA --}}
@if ($produk['stok'] > 0)
<script>
    const hargaProduk = {{ $produk['harga'] }};
    const minimalProduk = {{ $produk['minimal'] }};
    const stokProduk = {{ $produk['stok'] }};

    const inputJumlah = document.getElementById('jumlah');

    function ubahJumlah(perubahan) {
        let jumlah = parseInt(inputJumlah.value) || minimalProduk;

        jumlah += perubahan;

        jumlah = Math.max(
            minimalProduk,
            Math.min(stokProduk, jumlah)
        );

        inputJumlah.value = jumlah;

        inputJumlah.setCustomValidity('');

        hitungTotal();
    }

    function hitungTotal() {
        let jumlah = parseInt(inputJumlah.value) || 0;

        jumlah = Math.max(0, Math.min(stokProduk, jumlah));

        document.getElementById('totalHarga').textContent =
            'Rp' + (hargaProduk * jumlah).toLocaleString('id-ID');
    }

    // VALIDASI JUMLAH PEMBELIAN
    inputJumlah.addEventListener('invalid', function () {

        if (this.validity.valueMissing) {
            this.setCustomValidity(
                'Silakan masukkan jumlah pembelian.'
            );

        } else if (this.validity.rangeUnderflow) {
            this.setCustomValidity(
                'Jumlah minimal pembelian adalah ' +
                minimalProduk + ' kg.'
            );

        } else if (this.validity.rangeOverflow) {
            this.setCustomValidity(
                'Jumlah pembelian melebihi stok yang tersedia. ' +
                'Maksimal pembelian adalah ' + stokProduk + ' kg.'
            );

        } else if (this.validity.badInput) {
            this.setCustomValidity(
                'Silakan masukkan jumlah pembelian yang valid.'
            );
        }

    });

    // HAPUS PESAN ERROR KETIKA INPUT DIPERBAIKI
    inputJumlah.addEventListener('input', function () {
        this.setCustomValidity('');
    });

</script>
@endif

@endsection

