@php
    $produkSaya = auth()->user()->produk();
    $hampirHabis = (clone $produkSaya)->where('aktif', true)->whereNull('harga_flash_sale')
        ->whereNotNull('tersedia_sampai')
        ->whereDate('tersedia_sampai', '>=', now()->startOfDay())
        ->whereDate('tersedia_sampai', '<=', now()->addDays(2))
        ->count();
    $sedangFlashSale = (clone $produkSaya)->whereNotNull('harga_flash_sale')
        ->whereDate('flash_sale_sampai', '>=', now()->startOfDay())
        ->count();
@endphp

@if ($hampirHabis > 0 || $sedangFlashSale > 0)
    <a href="{{ route('petani.flash-sale') }}"
       class="group relative block overflow-hidden rounded-2xl bg-gradient-to-r from-red-600 via-red-500 to-orange-500 p-4 shadow-lg transition hover:shadow-xl sm:p-5">
        <div class="pointer-events-none absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute -bottom-10 right-16 h-24 w-24 rounded-full bg-white/10"></div>

        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-3">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/20 text-2xl">⚡</span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-white/80">Flash Sale</p>
                    @if ($hampirHabis > 0)
                        <p class="text-base font-bold text-white sm:text-lg">{{ $hampirHabis }} produk hampir habis masa tersedianya</p>
                        <p class="text-sm text-white/90">Turunkan harganya supaya tampil paling atas di beranda pembeli.</p>
                    @else
                        <p class="text-base font-bold text-white sm:text-lg">{{ $sedangFlashSale }} produk sedang flash sale</p>
                        <p class="text-sm text-white/90">Produkmu sedang tampil paling atas di beranda pembeli.</p>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if ($hampirHabis > 0 && $sedangFlashSale > 0)
                    <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-semibold text-white">{{ $sedangFlashSale }} aktif</span>
                @endif
                <span class="inline-flex items-center gap-1 rounded-xl bg-white px-4 py-2 text-sm font-bold text-red-600 transition group-hover:translate-x-0.5">
                    {{ $hampirHabis > 0 ? 'Ajukan Sekarang' : 'Kelola' }} →
                </span>
            </div>
        </div>
    </a>
@endif