@extends('layouts.petani')

@section('content')
<x-page-header judul="Flash Sale" :kembali="route('petani.dashboard')" lebar="max-w-4xl" />

@php
    $jumlahAktif = $produk->filter(fn ($p) => $p->sedangFlashSale())->count();
    $jumlahBelum = $produk->count() - $jumlahAktif;
@endphp

<div class="mx-auto max-w-4xl space-y-5 px-4 py-6">

    {{-- HERO --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-red-600 via-red-500 to-orange-500 p-5 text-white shadow-lg">
        <div class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full bg-white/10"></div>
        <div class="relative flex items-start gap-4">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/20 text-2xl">⚡</span>
            <div>
                <h2 class="text-lg font-extrabold">Jual lebih cepat dengan Flash Sale</h2>
                <p class="mt-1 text-sm text-white/90">Produk yang masa tersedianya tinggal 2 hari atau kurang bisa kamu diskon. Produk flash sale tampil paling atas di beranda pembeli. Sifatnya opsional.</p>
            </div>
        </div>
        <div class="relative mt-4 grid grid-cols-2 gap-3">
            <div class="rounded-xl bg-white/15 p-3">
                <p class="text-2xl font-extrabold">{{ $jumlahBelum }}</p>
                <p class="text-xs text-white/90">Bisa diajukan</p>
            </div>
            <div class="rounded-xl bg-white/15 p-3">
                <p class="text-2xl font-extrabold">{{ $jumlahAktif }}</p>
                <p class="text-xs text-white/90">Sedang flash sale</p>
            </div>
        </div>
    </div>

    @if (session('sukses'))
        <div class="flex items-center gap-2 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">✅ {{ session('sukses') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    {{-- DAFTAR PRODUK --}}
    @forelse ($produk as $item)
        @php
            $sisa = (int) now()->startOfDay()->diffInDays(\Illuminate\Support\Carbon::parse($item->tersedia_sampai)->startOfDay(), false);
            $aktif = $item->sedangFlashSale();
            $hargaNormal = (int) $item->harga_per_kg;
            $foto = $item->fotoUtama?->url ?? 'https://placehold.co/160x160?text=TaniLoka';
            $diskon = $aktif ? (int) round((1 - $item->harga_flash_sale / $hargaNormal) * 100) : 0;
        @endphp

        <div class="overflow-hidden rounded-2xl border bg-white shadow-sm {{ $aktif ? 'border-red-300 ring-1 ring-red-200' : 'border-gray-200' }}"
             x-data="{ harga: '' }">
            <div class="flex gap-4 p-4">
                <div class="relative shrink-0">
                    <img src="{{ $foto }}" alt="{{ $item->nama }}" class="h-20 w-20 rounded-xl object-cover sm:h-24 sm:w-24">
                    @if ($aktif)
                        <span class="absolute -left-1 -top-1 rounded-md bg-red-600 px-1.5 py-0.5 text-[10px] font-bold text-white shadow">-{{ $diskon }}%</span>
                    @endif
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="font-bold text-gray-900">{{ $item->nama }}</p>
                        @if ($aktif)
                            <span class="rounded-full bg-red-600 px-2 py-0.5 text-[10px] font-bold uppercase text-white">⚡ Aktif</span>
                        @endif
                    </div>

                    @if ($aktif)
                        <p class="mt-1">
                            <span class="text-lg font-extrabold text-red-600">Rp{{ number_format($item->harga_flash_sale, 0, ',', '.') }}</span>
                            <span class="text-sm text-gray-500">/kg</span>
                            <span class="ml-1 text-sm text-gray-400 line-through">Rp{{ number_format($hargaNormal, 0, ',', '.') }}</span>
                        </p>
                    @else
                        <p class="mt-1 text-sm text-gray-500">Harga normal <span class="font-semibold text-gray-800">Rp{{ number_format($hargaNormal, 0, ',', '.') }}/kg</span></p>
                    @endif

                    <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-600">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        {{ $sisa === 0 ? 'Tersedia sampai hari ini' : 'Tinggal '.$sisa.' hari lagi' }}
                    </span>
                </div>
            </div>

            <div class="border-t px-4 py-3 {{ $aktif ? 'border-red-100 bg-red-50/60' : 'border-gray-100 bg-gray-50' }}">
                @if ($aktif)
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-xs text-gray-600">Berlaku sampai {{ \Illuminate\Support\Carbon::parse($item->flash_sale_sampai)->translatedFormat('d F Y') }}</p>
                        <form method="POST" action="{{ route('petani.flash-sale.destroy', $item->id) }}"
                              onsubmit="return confirm('Batalkan flash sale {{ $item->nama }}?')">
                            @csrf @method('DELETE')
                            <button class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-600 hover:border-red-400 hover:text-red-600">Batalkan</button>
                        </form>
                    </div>
                @else
                    <form method="POST" action="{{ route('petani.flash-sale.store', $item->id) }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        @csrf
                        <div class="relative flex-1">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500">Rp</span>
                            <input type="number" name="harga_flash_sale" x-model="harga" min="1" max="{{ $hargaNormal - 1 }}" required
                                   placeholder="Harga flash sale per kg"
                                   class="w-full rounded-xl border border-gray-300 bg-white py-2.5 pl-10 pr-3 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                        </div>
                        <span x-show="harga > 0 && harga < {{ $hargaNormal }}" x-cloak
                              class="rounded-lg bg-red-100 px-2.5 py-1 text-center text-xs font-bold text-red-600"
                              x-text="'Hemat ' + Math.round((1 - harga / {{ $hargaNormal }}) * 100) + '%'"></span>
                        <button class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-red-700">⚡ Ajukan Flash Sale</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="rounded-2xl border-2 border-dashed border-gray-200 bg-white p-10 text-center">
            <p class="text-4xl">🌱</p>
            <p class="mt-2 font-semibold text-gray-700">Belum ada produk yang mendekati batas</p>
            <p class="mt-1 text-sm text-gray-500">Produk dengan masa tersedia 2 hari atau kurang akan muncul di sini.</p>
        </div>
    @endforelse
</div>
@endsection