@extends('layouts.petani')

@section('content')
<x-page-header judul="Flash Sale" :kembali="route('petani.dashboard')" lebar="max-w-4xl" />

<div class="mx-auto max-w-4xl px-4 py-6">
    <p class="mb-4 text-sm text-gray-600">Produk yang masa tersedianya tinggal 2 hari atau kurang. Turunkan harganya supaya tampil paling atas di beranda pengusaha.</p>

    @if (session('sukses'))
        <div class="mb-4 rounded-xl bg-green-50 p-3 text-sm text-green-800">{{ session('sukses') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    @forelse ($produk as $item)
        @php
            $sisa = (int) now()->startOfDay()->diffInDays(\Illuminate\Support\Carbon::parse($item->tersedia_sampai)->startOfDay(), false);
            $aktif = $item->sedangFlashSale();
        @endphp
        <div class="mb-4 flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-4 sm:flex-row sm:items-center">
            <img src="{{ $item->fotoUtama ? asset('storage/'.$item->fotoUtama->path) : 'https://placehold.co/96x96?text=Produk' }}"
                 class="h-20 w-20 rounded-xl object-cover" alt="{{ $item->nama }}">

            <div class="flex-1">
                <p class="font-semibold text-gray-900">{{ $item->nama }}</p>
                <p class="text-sm text-gray-500">Harga normal Rp{{ number_format($item->harga_per_kg, 0, ',', '.') }}/kg</p>
                <span class="mt-1 inline-block rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">
                    {{ $sisa === 0 ? 'Tersedia sampai hari ini' : 'Tinggal '.$sisa.' hari lagi' }}
                </span>
            </div>

            @if ($aktif)
                <div class="text-right">
                    <p class="text-xs font-semibold text-red-600">FLASH SALE AKTIF</p>
                    <p class="text-lg font-bold text-red-600">Rp{{ number_format($item->harga_flash_sale, 0, ',', '.') }}/kg</p>
                    <form method="POST" action="{{ route('petani.flash-sale.destroy', $item->id) }}">
                        @csrf @method('DELETE')
                        <button class="mt-1 text-sm text-gray-500 underline hover:text-gray-700">Batalkan</button>
                    </form>
                </div>
            @else
                <form method="POST" action="{{ route('petani.flash-sale.store', $item->id) }}" class="flex items-center gap-2">
                    @csrf
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500">Rp</span>
                        <input type="number" name="harga_flash_sale" min="1" max="{{ (int) $item->harga_per_kg - 1 }}" required
                               placeholder="Harga baru"
                               class="w-36 rounded-xl border border-gray-300 py-2 pl-9 pr-3 text-sm focus:border-red-500 focus:ring-red-500">
                    </div>
                    <button class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Ajukan</button>
                </form>
            @endif
        </div>
    @empty
        <div class="rounded-2xl border border-dashed border-gray-300 p-8 text-center text-gray-500">
            Belum ada produk yang mendekati batas masa tersedia.
        </div>
    @endforelse
</div>
@endsection