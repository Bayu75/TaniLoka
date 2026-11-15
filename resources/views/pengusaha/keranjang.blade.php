
@extends('layouts.pengusaha')

@section('title', 'Keranjang - TaniLoka')

@section('content')

{{-- HEADER KERANJANG --}}
<div class="relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen border-b border-[#D4E8D0] bg-white">
    <div class="mx-auto max-w-5xl px-4 py-3">
        <p class="text-sm font-semibold text-[#087C3E]">
            Belanja
        </p>

        <h1 class="mt-1 text-2xl font-extrabold text-[#1A2A1A]">
            Keranjang Saya
        </h1>

        <p class="mt-1 text-sm text-[#6B806B]">
            Pilih produk yang ingin dibeli sebelum melanjutkan pesanan.
        </p>
    </div>
</div>

{{-- KONTEN HALAMAN --}}
<div class="mx-auto max-w-5xl space-y-6 px-4">

{{-- KERANJANG DAN RINGKASAN --}}
<div
    class="grid items-stretch gap-5 py-6 lg:grid-cols-3"
    x-data="{
        selected: @js(array_keys($keranjangValid)),
        products: @js($produkList),
        quantities: @js($keranjangValid),

        selectedTotal() {
            return this.selected.reduce((total, slug) => {
                return total + (
                    this.products[slug].harga *
                    Number(this.quantities[slug] || 0)
                );
            }, 0);
        },

        allSelected() {
            return this.selected.length === Object.keys(this.quantities).length
                && this.selected.length > 0;
        },

        toggleAll(event) {
            this.selected = event.target.checked
                ? Object.keys(this.quantities)
                : [];
        },

        formatRupiah(value) {
            return 'Rp' + Number(value).toLocaleString('id-ID');
        }
    }"
>

    {{-- KOTAK DAFTAR PRODUK --}}
    <div class="lg:col-span-2">
        <div class="h-full rounded-2xl border border-[#D4E8D0] bg-white p-4 md:p-5">

            {{-- PILIH SEMUA --}}
            <div class="mb-4 flex items-center justify-between border-b border-[#E5EEE2] pb-4">
                <label class="flex cursor-pointer items-center gap-3">
                    <input
                        type="checkbox"
                        :checked="allSelected()"
                        @change="toggleAll($event)"
                        class="h-5 w-5 rounded border-gray-300 accent-green-700"
                    >

                    <span class="font-bold text-[#1A2A1A]">
                        Pilih Semua
                    </span>
                </label>

                <span class="text-sm text-[#6B806B]">
                    {{ $jumlahProduk }} produk
                </span>
            </div>

            @if ($jumlahProduk === 0)

                <div class="flex min-h-[250px] flex-col items-center justify-center text-center">
                    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[#EAF5E8] text-3xl">
                        🛒
                    </div>

                    <h2 class="text-lg font-bold text-[#1A2A1A]">
                        Keranjang masih kosong
                    </h2>

                    <p class="mt-2 text-sm text-[#6B806B]">
                        Yuk, temukan bahan pangan segar dari petani lokal.
                    </p>

                    <a
                        href="{{ route('pengusaha.katalog') }}"
                        class="mt-5 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-dark"
                    >
                        Mulai Belanja
                    </a>
                </div>

            @else

                @foreach ($keranjangValid as $slug => $jumlah)
                    @php
                        $produk = $produkList[$slug];
                        $subtotal = $produk['harga'] * $jumlah;
                    @endphp

                    <div class="flex flex-col gap-4 border-b border-[#E5EEE2] py-5 last:border-b-0 sm:flex-row sm:items-center">

                        {{-- CHECKBOX PRODUK --}}
                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                value="{{ $slug }}"
                                x-model="selected"
                                aria-label="Pilih {{ $produk['nama'] }}"
                                class="h-5 w-5 cursor-pointer rounded border-gray-300 accent-green-700"
                            >
                        </div>

                        {{-- GAMBAR PRODUK --}}
                        <img
                            src="{{ $produk['gambar'] }}"
                            alt="{{ $produk['nama'] }}"
                            class="h-24 w-full rounded-xl object-cover sm:w-28"
                        >

                        {{-- INFORMASI PRODUK --}}
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-primary">
                                {{ $produk['petani'] }}
                            </p>

                            <h2 class="mt-1 font-bold text-text">
                                {{ $produk['nama'] }}
                            </h2>

                            <p class="mt-1 text-sm text-text-muted">
                                Rp{{ number_format($produk['harga'], 0, ',', '.') }} / kg
                            </p>

                            <p class="mt-2 font-bold text-primary">
                                Rp{{ number_format($subtotal, 0, ',', '.') }}
                            </p>
                        </div>


{{-- AKSI PRODUK --}}
<div class="flex w-full flex-col gap-3 sm:w-40 sm:shrink-0">

    {{-- FORM UBAH JUMLAH --}}
    <form
        action="{{ route('pengusaha.keranjang.update') }}"
        method="POST"
        class="flex w-full flex-col gap-2"
    >
        @csrf

        <input type="hidden" name="slug" value="{{ $slug }}">

        <div class="w-full">
            <label class="mb-1 block text-xs font-medium text-text-muted">
                Jumlah (kg)
            </label>

           <input
    type="number"
    name="jumlah"
    value="{{ $jumlah }}"
    min="{{ $produk['minimal'] }}"
    max="{{ $produk['stok'] }}"
    required
    oninvalid="
        if (this.validity.valueMissing) {
            this.setCustomValidity('Jumlah pembelian wajib diisi.');
        } else if (this.validity.rangeUnderflow) {
            this.setCustomValidity('Jumlah pembelian minimal {{ $produk['minimal'] }} kg.');
        } else if (this.validity.rangeOverflow) {
            this.setCustomValidity('Jumlah pembelian maksimal {{ $produk['stok'] }} kg sesuai stok tersedia.');
        }
    "
    oninput="this.setCustomValidity('')"
    class="h-10 w-full rounded-lg border border-border px-3 text-sm outline-none focus:border-primary"
>
        </div>

        <button
            type="submit"
            class="flex h-10 w-full items-center justify-center rounded-lg bg-primary px-4 text-sm font-semibold text-white transition hover:bg-primary-dark"
        >
            Ubah Jumlah
        </button>
    </form>

    {{-- FORM HAPUS --}}
    <form
        action="{{ route('pengusaha.keranjang.hapus') }}"
        method="POST"
        class="w-full"
        onsubmit="return confirm('Yakin ingin menghapus produk ini?')"
    >
        @csrf

        <input type="hidden" name="slug" value="{{ $slug }}">

        <button
            type="submit"
            class="flex h-10 w-full items-center justify-center rounded-lg border border-red-200 px-4 text-sm font-semibold text-red-500 transition hover:bg-red-50"
        >
            Hapus
        </button>
    </form>

</div>

                    </div>
                @endforeach

            @endif
        </div>
    </div>

    {{-- KOTAK RINGKASAN --}}
    <div class="h-full">
        <div class="h-full rounded-2xl border border-[#D4E8D0] bg-white p-5">

            <h2 class="text-lg font-bold text-[#1A2A1A]">
                Ringkasan Belanja
            </h2>

            <p class="mt-1 text-xs text-[#6B806B]">
                Berdasarkan produk yang kamu pilih.
            </p>

            <div class="mt-6 flex justify-between gap-3 text-sm">
                <span class="text-[#6B806B]">
                    Produk dipilih
                </span>

                <span
                    class="font-semibold text-[#1A2A1A]"
                    x-text="selected.length + ' produk'"
                ></span>
            </div>

            <div class="mt-4 flex justify-between gap-3 text-sm">
                <span class="text-[#6B806B]">
                    Total kuantitas
                </span>

                <span
                    class="font-semibold text-[#1A2A1A]"
                    x-text="selected.reduce((total, slug) => total + Number(quantities[slug] || 0), 0) + ' kg'"
                ></span>
            </div>

            <div class="my-5 border-t border-[#E5EEE2]"></div>

            <div class="flex items-start justify-between gap-3">
                <span class="font-semibold text-[#1A2A1A]">
                    Total Belanja
                </span>

                <span
                    class="text-right text-lg font-extrabold text-primary"
                    x-text="formatRupiah(selectedTotal())"
                ></span>
            </div>

            <div class="mt-6">
                <form action="{{ route('pengusaha.checkout.proses') }}" method="POST">
                    @csrf

                    <input
                        type="hidden"
                        name="items"
                        :value="JSON.stringify(selected.map(slug => ({
                            slug: slug,
                            jumlah: quantities[slug]
                        })))"
                    >

                    <button
                        type="submit"
                        :disabled="selected.length === 0"
                        :class="selected.length === 0
                            ? 'w-full cursor-not-allowed rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white opacity-50'
                            : 'w-full rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-dark'"
                    >
                        Lanjutkan Pesanan
                    </button>
                </form>

    <p class="mt-2 text-center text-xs text-[#8A9A8A]">
        Lanjutkan untuk menyelesaikan pesanan produk pilihanmu.
    </p>
</div>

            <a
                href="{{ route('pengusaha.katalog') }}"
                class="mt-5 block text-center text-sm font-medium text-primary hover:underline"
            >
                + Tambah Produk Lain
            </a>

        </div>
    </div>

</div>
@endsection
