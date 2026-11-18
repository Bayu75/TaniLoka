@props([
    'produk',
    'satuan' => 'kg',
    'badge' => null,
    'bisaNego' => false,
])

@php
    $tersedia = (float) $produk->stok_kg > 0;
    $gambar = $produk->fotoUtama?->url ?? 'https://placehold.co/300x300?text=TaniLoka';
    $kabupaten = $produk->petani?->alamatUtama?->kabupaten;
    $tersediaSampai = $produk->tersedia_sampai ? \Illuminate\Support\Carbon::parse($produk->tersedia_sampai) : null;
    $sisaHari = $tersediaSampai ? (int) now()->startOfDay()->diffInDays($tersediaSampai->copy()->startOfDay(), false) : null;
    $mendesak = $tersediaSampai && $sisaHari >= 0 && $sisaHari <= 2;
@endphp

<a href="{{ route('pengusaha.produk.detail', $produk) }}"
   class="flex flex-col overflow-hidden rounded-2xl border border-[#D4E8D0] bg-white transition hover:-translate-y-1 hover:shadow-md {{ $tersedia ? '' : 'opacity-70' }}">

    <div class="relative h-40 overflow-hidden bg-[#EBF5EB]">
        <img src="{{ $gambar }}" alt="{{ $produk->nama }}"
             class="h-full w-full object-cover {{ $tersedia ? '' : 'grayscale' }}">

        @if (! $tersedia)
            <div class="absolute inset-0 flex items-center justify-center bg-black/45">
                <span class="rounded-full bg-black/70 px-4 py-1.5 font-bold text-white">Stok Habis</span>
            </div>
        @endif

        <div class="absolute left-2 top-2 flex flex-col gap-1">
            @if ($badge)
                <span class="rounded-full bg-[#2E7D32] px-2 py-0.5 text-xs font-semibold text-white">{{ $badge }}</span>
            @elseif ($produk->grade)
                <span class="rounded-full bg-[#2E7D32] px-2 py-0.5 text-xs font-semibold text-white">Grade {{ $produk->grade }}</span>
            @endif

            @if ($bisaNego)
                <span class="rounded-full bg-[#F4B400] px-2 py-0.5 text-xs font-semibold text-[#1A2A00]">Bisa Nego</span>
            @endif

            {{-- Badge segera habis --}}
            @if ($mendesak)
                <span class="inline-flex items-center gap-1 rounded-full bg-red-600 px-2 py-0.5 text-xs font-bold text-white shadow-sm">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    Segera Habis
                </span>
            @endif
        </div>
    </div>

    <div class="flex flex-1 flex-col gap-2 p-3">
        <span class="self-start rounded-full bg-[#EBF5EB] px-2 py-0.5 text-xs font-medium text-[#1E5F3E]">
            {{ $produk->kategori?->nama }}
        </span>

        <h3 class="line-clamp-2 text-base font-semibold leading-tight text-[#1A2A1A]">{{ $produk->nama }}</h3>

        <div>
            <div class="text-lg font-bold text-[#2E7D32]">
                Rp{{ number_format($produk->harga_per_kg, 0, ',', '.') }}
                <span class="text-sm font-normal text-[#5A7A5A]">/{{ $satuan }}</span>
            </div>
            <div class="text-sm text-[#5A7A5A]">
                Minimal pesan {{ (float) $produk->min_order_kg }} {{ $satuan }}
            </div>
        </div>

        {{-- Pita tersedia sampai --}}
        @if ($tersediaSampai)
            <div class="flex items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-semibold {{ $sisaHari < 0 ? 'bg-gray-100 text-gray-500' : ($mendesak ? 'bg-red-50 text-red-600' : 'bg-[#F5FAF4] text-[#2E7D32]') }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                <span class="truncate">
                    @if ($sisaHari < 0)
                        Masa tersedia sudah lewat
                    @elseif ($sisaHari === 0)
                        Tersedia sampai hari ini
                    @elseif ($mendesak)
                        Tinggal {{ $sisaHari }} hari lagi
                    @else
                        Tersedia s/d {{ $tersediaSampai->translatedFormat('d M') }}
                    @endif
                </span>
            </div>
        @endif

        <div class="mt-auto border-t border-[#F0F4EF] pt-2">
            <div class="truncate text-sm font-medium text-[#5A7A5A]">{{ $produk->petani?->name }}</div>
            @if ($kabupaten)
                <div class="mt-0.5 truncate text-sm text-[#5A7A5A]">📍 {{ $kabupaten }}</div>
            @endif
        </div>
    </div>
</a>