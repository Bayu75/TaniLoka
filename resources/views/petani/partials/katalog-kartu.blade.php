@php
    $p = $produk;
    $minimal = (float) ($p['minimal'] ?? 0);
    $minimalLabel = fmod($minimal, 1.0) === 0.0
        ? (string) (int) $minimal
        : rtrim(rtrim(number_format($minimal, 2, '.', ''), '0'), '.');
    $hargaLabel = 'Rp'.number_format((int) ($p['harga'] ?? 0), 0, ',', '.');
    $sisa = $p['sisaHari'] ?? null;
    $kedaluwarsa = ! empty($p['kedaluwarsa']) || ($sisa !== null && $sisa < 0);
    $stokHabis = empty($p['stok']);
    $overlay = $kedaluwarsa || $stokHabis;
    $pilClass = $sisa === null
        ? ''
        : ($kedaluwarsa ? 'bg-[#F3F4F6] text-[#8A8F98]' : ($sisa <= 3 ? 'bg-[#FEF2F2] text-[#E7000B]' : 'bg-[#F0FDF4] text-[#2E7D32]'));
    $teksNama = $kedaluwarsa ? 'text-[#8A8F98]' : 'text-[#1A2A1A]';
    $teksHalus = $kedaluwarsa ? 'text-[#B0B8C4]' : 'text-[#5A7A5A]';
    $teksPetani = $kedaluwarsa ? 'text-[#8A8F98]' : 'text-[#5A5A5A]';
    $teksHarga = $kedaluwarsa ? 'text-[#B0B8C4]' : 'text-[#2E7D32]';
    $chipKategori = $kedaluwarsa ? 'bg-[#F3F4F6] text-[#9CA3AF]' : 'bg-[#EBF5EB] text-[#1E5F3E]';
@endphp
<div
    role="button"
    tabindex="0"
    @click="buka(produkById({{ (int) $p['id'] }}))"
    @keydown.enter.prevent="buka(produkById({{ (int) $p['id'] }}))"
    class="flex h-full cursor-pointer flex-col overflow-hidden rounded-xl border border-[#D4E8D0] bg-white transition hover:shadow-md"
>
    <div class="relative aspect-[234/140] w-full overflow-hidden bg-[#DDDDDD]">
        @if (empty($p['gambar']) && ! $overlay)
            <span class="absolute inset-0 flex items-center justify-center px-2 text-center text-lg leading-tight font-semibold break-words text-[#A3A3A3]">{{ $p['nama'] }}</span>
        @endif
        @if (! empty($p['gambar']))
            <img src="{{ $p['gambar'] }}" alt="{{ $p['nama'] }}" class="absolute inset-0 h-full w-full object-cover {{ $overlay ? 'grayscale contrast-90 brightness-105' : '' }}" onerror="this.classList.add('hidden')">
        @endif
        <div class="absolute top-2 left-2 z-20 flex flex-col items-start gap-1">
            @foreach ($p['label'] ?? [] as $label)
                <span class="inline-flex items-center rounded-full bg-[#2E7D32]/90 px-2.5 py-0.5 text-xs font-bold text-white">{{ $label }}</span>
            @endforeach
            @if (! empty($p['nego']))
                <span class="inline-flex items-center rounded-full bg-[#F4B400] px-2.5 py-0.5 text-xs font-bold text-[#1A2A00]">Bisa Nego</span>
            @endif
            @if (! empty($p['segeraHabis']) && ! $kedaluwarsa)
                <span class="inline-flex items-center gap-1 rounded-full bg-[#E7000B] px-2.5 py-0.5 text-xs font-bold whitespace-nowrap text-white">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></svg>
                    Segera Habis
                </span>
            @endif
        </div>
        @if ($kedaluwarsa)
            <div class="absolute inset-0 z-10 flex items-center justify-center bg-black/10">
                <span class="min-w-[200px] rounded-full bg-[#4B5563]/80 px-6 py-2 text-center text-sm font-bold text-white">Produk Tidak Tersedia</span>
            </div>
        @elseif ($stokHabis)
            <div class="absolute inset-0 z-10 flex items-center justify-center bg-black/10">
                <span class="min-w-[200px] rounded-full bg-[#4B5563]/80 px-6 py-2 text-center text-sm font-bold text-white">Stok Habis</span>
            </div>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-3">
        <span class="inline-block self-start rounded-full px-2.5 py-0.5 text-xs font-medium {{ $chipKategori }}">{{ $p['kategori'] }}</span>
        <p class="mt-1.5 text-base leading-snug font-semibold {{ $teksNama }}">{{ $p['nama'] }}</p>
        <p class="mt-0.5"><span class="text-lg font-bold {{ $teksHarga }}">{{ $hargaLabel }}</span><span class="text-sm font-normal {{ $teksHalus }}"> /kg</span></p>
        <p class="text-sm {{ $teksHalus }}">Minimal pesan {{ $minimalLabel }} kg</p>
        @if (! empty($p['tersediaSampai']))
            <div class="mt-2 flex w-full items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold {{ $pilClass }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></svg>
                <span>{{ $p['labelKartuTersedia'] }}</span>
            </div>
        @endif
        <div class="mt-auto border-t border-gray-100 pt-2">
            <p class="text-sm {{ $teksPetani }}">{{ $p['petani'] }}</p>
            @if (! empty($p['lokasi']))
                <p class="mt-0.5 text-sm {{ $teksHalus }}">📍 {{ $p['lokasi'] }}</p>
            @endif
        </div>
    </div>
</div>
