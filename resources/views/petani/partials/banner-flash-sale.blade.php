@php
    $hampirHabis = auth()->user()->produk()
        ->where('aktif', true)
        ->whereNull('harga_flash_sale')
        ->whereNotNull('tersedia_sampai')
        ->whereDate('tersedia_sampai', '>=', now()->startOfDay())
        ->whereDate('tersedia_sampai', '<=', now()->addDays(2))
        ->count();
@endphp

@if ($hampirHabis > 0)
    <div class="mb-6 flex flex-col gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-xl">⏰</span>
            <div>
                <p class="font-semibold text-red-800">{{ $hampirHabis }} produk hampir habis masa tersedianya</p>
                <p class="text-sm text-red-700">Mau ajukan flash sale supaya cepat terjual? Ini opsional.</p>
            </div>
        </div>
        <a href="{{ route('petani.flash-sale') }}"
           class="rounded-xl bg-red-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-red-700">
            Lihat & Ajukan
        </a>
    </div>
@endif