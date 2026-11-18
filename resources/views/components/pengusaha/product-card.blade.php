@props([
    'produk',
    'satuan' => 'kg',
    'badge' => null,
    'bisaNego' => false,
])

@php
    $gambar = $produk->fotoUtama?->url ?? 'https://placehold.co/300x300?text=TaniLoka';
    $kabupaten = $produk->petani?->alamatUtama?->kabupaten;
    $tersediaSampai = $produk->tersedia_sampai ? \Illuminate\Support\Carbon::parse($produk->tersedia_sampai) : null;
    $sisaHari = $tersediaSampai ? (int) now()->startOfDay()->diffInDays($tersediaSampai->copy()->startOfDay(), false) : null;
    $kadaluarsa = $tersediaSampai && $sisaHari < 0;
    $mendesak = $tersediaSampai && $sisaHari >= 0 && $sisaHari <= 2;
    $tersedia = (float) $produk->stok_kg > 0 && ! $kadaluarsa;
    $flashSale = $produk->sedangFlashSale() && ! $kadaluarsa;
@endphp

<a href="{{ route('pengusaha.produk.detail', $produk) }}"
   class="flex flex-col overflow-hidden rounded-2xl border border-[#D4E8D0] bg-white transition hover:-translate-y-1 hover:shadow-md {{ $tersedia ? '' : 'opacity-70' }} {{ $flashSale ? 'ring-2 ring-red-500' : '' }}">

    <div class="relative h-40 overflow-hidden bg-[#EBF5EB]">
        <img src="{{ $gambar }}" alt="{{ $produk->nama }}"
             class="h-full w-full object-cover {{ $tersedia ? '' : 'grayscale' }}">

        @if (! $tersedia)
            <div class="absolute inset-0 flex items-center justify-center bg-black/45">
                <span class="rounded-full bg-black/70 px-4 py-1.5 text-sm font-bold text-white">
                    {{ $kadaluarsa ? 'Produk Tidak Tersedia' : 'Stok Habis' }}
                </span>
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

            @if ($mendesak && $tersedia)
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
            @if ($flashSale)
                @php $diskon = (int) round((1 - $produk->harga_flash_sale / $produk->harga_per_kg) * 100); @endphp
                <div class="mb-1 flex items-center gap-1.5">
                    <span class="rounded-md bg-red-600 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">⚡ Flash Sale</span>
                    <span class="rounded-md bg-red-50 px-1.5 py-0.5 text-[10px] font-bold text-red-600">-{{ $diskon }}%</span>
                </div>
                <div class="flex flex-wrap items-baseline gap-x-1.5">
                    <span class="text-lg font-bold text-red-600">Rp{{ number_format($produk->harga_flash_sale, 0, ',', '.') }}</span>
                    <span class="text-sm font-normal text-[#5A7A5A]">/{{ $satuan }}</span>
                </div>
                <div class="text-xs text-gray-400 line-through">Rp{{ number_format($produk->harga_per_kg, 0, ',', '.') }}</div>
            @else
                <div class="text-lg font-bold {{ $tersedia ? 'text-[#2E7D32]' : 'text-gray-400' }}">
                    Rp{{ number_format($produk->harga_per_kg, 0, ',', '.') }}
                    <span class="text-sm font-normal text-[#5A7A5A]">/{{ $satuan }}</span>
                </div>
            @endif
            <div class="text-sm text-[#5A7A5A]">
                Minimal pesan {{ (float) $produk->min_order_kg }} {{ $satuan }}
            </div>
        </div>

        {{-- Pita tersedia sampai --}}
        @if ($tersediaSampai)
            <div class="flex items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-semibold {{ $kadaluarsa ? 'bg-gray-100 text-gray-500' : ($mendesak ? 'bg-red-50 text-red-600' : 'bg-[#F5FAF4] text-[#2E7D32]') }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                <span class="truncate">
                    @if ($kadaluarsa)
                        Sudah kedaluwarsa
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