@extends('layouts.pengusaha')

@section('title', 'Keranjang - TaniLoka')

@section('content')

{{-- HEADER KERANJANG --}}
<x-page-header judul="Keranjang Saya" :kembali="false" lebar="max-w-5xl" />
<div class="mx-auto max-w-5xl px-4">
    <p class="pt-6 text-sm text-[#6B806B]">Periksa produk dan jumlahnya sebelum melanjutkan pesanan.</p>

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
                                        @if (! is_null($item->harga_per_kg))
                                            <span class="font-semibold text-primary">Rp{{ number_format($item->harga_per_kg, 0, ',', '.') }} / kg</span>
                                            <span class="ml-1 rounded-full bg-green-100 px-1.5 py-0.5 text-[10px] font-bold text-green-700">HARGA NEGO</span>
                                        @elseif ($produk->sedangFlashSale())
                                            <span class="mr-1 text-gray-400 line-through">Rp{{ number_format($produk->harga_per_kg, 0, ',', '.') }}</span>
                                            <span class="font-semibold text-red-600">Rp{{ number_format($produk->harga_flash_sale, 0, ',', '.') }} / kg</span>
                                            <span class="ml-1 rounded-full bg-red-100 px-1.5 py-0.5 text-[10px] font-bold text-red-600">⚡ FLASH SALE</span>
                                        @else
                                            Rp{{ number_format($produk->harga_per_kg, 0, ',', '.') }} / kg
                                        @endif
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
@php
    $minKg = (int) ceil($produk->min_order_kg);
    $maxKg = (int) floor($produk->stok_kg);
    $nowKg = (int) round($item->jumlah_kg);
@endphp
<form action="{{ route('pengusaha.keranjang.ubah', $item) }}" method="POST"
      class="flex w-full flex-col gap-2">
    @csrf
    @method('PATCH')

    <label class="block text-xs font-medium text-text-muted">Jumlah (kg)</label>

    <div class="flex h-10 w-full items-center overflow-hidden rounded-lg border border-border">
        <button type="submit" name="jumlah_kg" value="{{ $nowKg - 1 }}"
                @disabled($nowKg <= $minKg)
                class="h-full w-10 shrink-0 text-lg font-bold text-primary hover:bg-gray-50 disabled:cursor-not-allowed disabled:text-gray-300">−</button>

        <input type="number" step="1" min="{{ $minKg }}" max="{{ $maxKg }}"
               value="{{ $nowKg }}"
               onchange="
                   let v = Math.round(this.value);
                   if (v < {{ $minKg }} || v > {{ $maxKg }}) {
                       alert('Jumlah harus antara {{ $minKg }} – {{ $maxKg }} kg.');
                       this.value = {{ $nowKg }};
                       return;
                   }
                   this.form.querySelector('[name=jumlah_kg_ketik]').value = v;
                   this.form.querySelector('[name=jumlah_kg_ketik]').name = 'jumlah_kg';
                   this.form.submit();
               "
               class="h-full w-full border-x border-border text-center text-sm outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
        <input type="hidden" name="jumlah_kg_ketik">

        <button type="submit" name="jumlah_kg" value="{{ $nowKg + 1 }}"
                @disabled($nowKg >= $maxKg)
                class="h-full w-10 shrink-0 text-lg font-bold text-primary hover:bg-gray-50 disabled:cursor-not-allowed disabled:text-gray-300">+</button>
    </div>

    <p class="text-[11px] text-text-muted">Min {{ $minKg }} kg · Maks {{ $maxKg }} kg</p>
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
                                        @php
                        $jumlahTidakValid = $items->contains(fn ($i) =>
                            $i->jumlah_kg < $i->produk->min_order_kg || $i->jumlah_kg > $i->produk->stok_kg
                        );
                    @endphp

                    @if ($jumlahTidakValid)
                        <div class="mb-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-center text-xs text-red-700">
                            Ada produk dengan jumlah di bawah minimal atau melebihi stok. Perbaiki dulu sebelum melanjutkan.
                        </div>
                    @endif

                    @if ($bisaCheckout && ! $jumlahTidakValid)
                        <a href="{{ route('pengusaha.checkout') }}"
                           class="block w-full rounded-xl bg-primary px-5 py-3 text-center text-sm font-semibold text-white hover:bg-primary-dark">
                            Lanjutkan Pesanan
                        </a>
                    @else
                        <button type="button" disabled
                                class="w-full cursor-not-allowed rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white opacity-50">
                            Lanjutkan Pesanan
                        </button>

                        @unless (auth()->user()->terverifikasi())
                            @php $statusVerif = auth()->user()->status_verifikasi; @endphp
                            <div class="mt-3 rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-center text-xs text-yellow-800">
                                @if ($statusVerif === 'menunggu')
                                    Pengajuan verifikasimu sedang ditinjau admin. Kamu bisa checkout setelah disetujui.
                                @elseif ($statusVerif === 'perlu_perbaikan')
                                    Pengajuan verifikasimu perlu diperbaiki sebelum bisa checkout.
                                    <a href="{{ route('pengusaha.verifikasi') }}" class="mt-1 block font-semibold text-primary hover:underline">Perbaiki Verifikasi →</a>
                                @else
                                    Akunmu belum terverifikasi, jadi belum bisa checkout.
                                    <a href="{{ route('pengusaha.verifikasi') }}" class="mt-1 block font-semibold text-primary hover:underline">Verifikasi Sekarang →</a>
                                @endif
                            </div>
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
    </div>
@endsection