@extends('layouts.pengusaha')

@section('title', 'Keranjang - TaniLoka')

@section('content')

{{-- HEADER KERANJANG --}}
<div class="relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen border-b border-[#D4E8D0] bg-white">
    <div class="mx-auto max-w-5xl px-4 py-3">
        <p class="text-sm font-semibold text-[#087C3E]">Belanja</p>
        <h1 class="mt-1 text-2xl font-extrabold text-[#1A2A1A]">Keranjang Saya</h1>
        <p class="mt-1 text-sm text-[#6B806B]">Periksa produk dan jumlahnya sebelum melanjutkan pesanan.</p>
    </div>
</div>

<div class="mx-auto max-w-5xl space-y-6 px-4">

    {{-- PESAN SUKSES / ERROR --}}
    @if (session('sukses'))
        <div class="mt-6 rounded-xl border border-[#D4E8D0] bg-[#EAF5E8] px-4 py-3 text-sm text-[#087C3E]">
            {{ session('sukses') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid items-stretch gap-5 py-6 lg:grid-cols-3">

        {{-- KOTAK DAFTAR PRODUK --}}
        <div class="lg:col-span-2">
            <div class="h-full rounded-2xl border border-[#D4E8D0] bg-white p-4 md:p-5">

                <div class="mb-4 flex items-center justify-between border-b border-[#E5EEE2] pb-4">
                    <span class="font-bold text-[#1A2A1A]">Daftar Produk</span>
                    <span class="text-sm text-[#6B806B]">{{ $items->count() }} produk</span>
                </div>

                @if ($items->isEmpty())

                    <div class="flex min-h-[250px] flex-col items-center justify-center text-center">
                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[#EAF5E8] text-3xl">🛒</div>
                        <h2 class="text-lg font-bold text-[#1A2A1A]">Keranjang masih kosong</h2>
                        <p class="mt-2 text-sm text-[#6B806B]">Yuk, temukan bahan pangan segar dari petani lokal.</p>
                        <a href="{{ route('pengusaha.katalog') }}"
                           class="mt-5 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-dark">
                            Mulai Belanja
                        </a>
                    </div>

                @else

                    @foreach ($perPetani as $petaniId => $itemPetani)
                        @php $petani = $itemPetani->first()->produk->petani; @endphp

                        {{-- NAMA PETANI (satu pesanan per petani saat checkout) --}}
                        <div class="mt-2 flex items-center justify-between rounded-lg bg-[#F5FAF4] px-3 py-2">
                            <a href="{{ route('pengusaha.toko', $petani) }}"
                               class="text-sm font-semibold text-primary hover:underline">
                                {{ $petani->name }}
                            </a>
                            <span class="text-xs text-[#6B806B]">{{ $itemPetani->count() }} produk</span>
                        </div>

                        @foreach ($itemPetani as $item)
                            @php $produk = $item->produk; @endphp

                            <div class="flex flex-col gap-4 border-b border-[#E5EEE2] py-5 last:border-b-0 sm:flex-row sm:items-center">

                                {{-- GAMBAR PRODUK --}}
                                <a href="{{ route('pengusaha.produk.detail', $produk) }}" class="shrink-0">
                                    <img src="{{ $produk->fotoUtama?->url ?? 'https://placehold.co/300x300?text=TaniLoka' }}"
                                         alt="{{ $produk->nama }}"
                                         class="h-24 w-full rounded-xl object-cover sm:w-28">
                                </a>

                                {{-- INFORMASI PRODUK --}}
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('pengusaha.produk.detail', $produk) }}"
                                       class="font-bold text-text hover:text-primary">
                                        {{ $produk->nama }}
                                    </a>
                                    <p class="mt-1 text-sm text-text-muted">
                                        Rp{{ number_format($produk->harga_per_kg, 0, ',', '.') }} / kg
                                    </p>
                                    <p class="mt-1 text-xs text-text-muted">
                                        Min. {{ (float) $produk->min_order_kg }} kg · Stok {{ (float) $produk->stok_kg }} kg
                                    </p>
                                    <p class="mt-2 font-bold text-primary">
                                        Rp{{ number_format($item->subtotal, 0, ',', '.') }}
                                    </p>
                                </div>

                                {{-- AKSI PRODUK --}}
                                <div class="flex w-full flex-col gap-3 sm:w-40 sm:shrink-0">

                                    {{-- FORM UBAH JUMLAH --}}
                                    <form action="{{ route('pengusaha.keranjang.ubah', $item) }}" method="POST"
                                          class="flex w-full flex-col gap-2">
                                        @csrf
                                        @method('PATCH')

                                        <div class="w-full">
                                            <label class="mb-1 block text-xs font-medium text-text-muted">Jumlah (kg)</label>
                                            <input
                                                type="number"
                                                name="jumlah_kg"
                                                step="0.01"
                                                value="{{ (float) $item->jumlah_kg }}"
                                                min="{{ (float) $produk->min_order_kg }}"
                                                max="{{ (float) $produk->stok_kg }}"
                                                required
                                                oninvalid="
                                                    if (this.validity.valueMissing) {
                                                        this.setCustomValidity('Jumlah pembelian wajib diisi.');
                                                    } else if (this.validity.rangeUnderflow) {
                                                        this.setCustomValidity('Jumlah pembelian minimal {{ (float) $produk->min_order_kg }} kg.');
                                                    } else if (this.validity.rangeOverflow) {
                                                        this.setCustomValidity('Jumlah pembelian maksimal {{ (float) $produk->stok_kg }} kg sesuai stok tersedia.');
                                                    }
                                                "
                                                oninput="this.setCustomValidity('')"
                                                class="h-10 w-full rounded-lg border border-border px-3 text-sm outline-none focus:border-primary"
                                            >
                                        </div>

                                        <button type="submit"
                                                class="flex h-10 w-full items-center justify-center rounded-lg bg-primary px-4 text-sm font-semibold text-white transition hover:bg-primary-dark">
                                            Ubah Jumlah
                                        </button>
                                    </form>

                                    {{-- FORM HAPUS --}}
                                    <form action="{{ route('pengusaha.keranjang.hapus', $item) }}" method="POST"
                                          class="w-full" onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="flex h-10 w-full items-center justify-center rounded-lg border border-red-200 px-4 text-sm font-semibold text-red-500 transition hover:bg-red-50">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    @endforeach

                @endif
            </div>
        </div>

        {{-- KOTAK RINGKASAN --}}
        <div class="h-full">
            <div class="h-full rounded-2xl border border-[#D4E8D0] bg-white p-5">

                <h2 class="text-lg font-bold text-[#1A2A1A]">Ringkasan Belanja</h2>
                <p class="mt-1 text-xs text-[#6B806B]">Pesanan akan dipisah per petani saat checkout.</p>

                <div class="mt-6 flex justify-between gap-3 text-sm">
                    <span class="text-[#6B806B]">Jumlah produk</span>
                    <span class="font-semibold text-[#1A2A1A]">{{ $items->count() }} produk</span>
                </div>

                <div class="mt-4 flex justify-between gap-3 text-sm">
                    <span class="text-[#6B806B]">Jumlah petani</span>
                    <span class="font-semibold text-[#1A2A1A]">{{ $perPetani->count() }} petani</span>
                </div>

                <div class="mt-4 flex justify-between gap-3 text-sm">
                    <span class="text-[#6B806B]">Total kuantitas</span>
                    <span class="font-semibold text-[#1A2A1A]">{{ (float) $items->sum('jumlah_kg') }} kg</span>
                </div>

                <div class="my-5 border-t border-[#E5EEE2]"></div>

                <div class="flex items-start justify-between gap-3">
                    <span class="font-semibold text-[#1A2A1A]">Total Belanja</span>
                    <span class="text-right text-lg font-extrabold text-primary">
                        Rp{{ number_format($total, 0, ',', '.') }}
                    </span>
                </div>

                <div class="mt-6">
                    @if ($bisaCheckout)
                        {{-- Ganti href="#" jadi route('pengusaha.checkout') setelah branch Ayudia di-merge --}}
                        <a href="#"
                           class="block w-full rounded-xl bg-primary px-5 py-3 text-center text-sm font-semibold text-white hover:bg-primary-dark">
                            Lanjutkan Pesanan
                        </a>
                    @else
                        <button type="button" disabled
                                class="w-full cursor-not-allowed rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white opacity-50">
                            Lanjutkan Pesanan
                        </button>

                        @unless (auth()->user()->terverifikasi())
                            <p class="mt-2 text-center text-xs text-[#8A9A8A]">
                                Akunmu belum terverifikasi, jadi belum bisa checkout.
                            </p>
                        @endunless
                    @endif
                </div>

                <a href="{{ route('pengusaha.katalog') }}"
                   class="mt-5 block text-center text-sm font-medium text-primary hover:underline">
                    + Tambah Produk Lain
                </a>
            </div>
        </div>

    </div>
</div>
@endsection