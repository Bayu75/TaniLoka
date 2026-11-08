
@props([
    'nama',
    'gambar',
    'kategori',
    'harga',
    'satuan' => 'kg',
    'minimalPesan' => 1,
    'petani',
    'rating' => 0,
    'kabupaten',
    'badge' => null,
    'bisaNego' => false,
    'tersedia' => true,
    'detailUrl' => '#',
])

<a href="{{ $detailUrl }}"
   class="flex flex-col overflow-hidden rounded-2xl border border-[#D4E8D0] bg-white transition hover:-translate-y-1 hover:shadow-md {{ $tersedia ? '' : 'opacity-70' }}">

    <div class="relative h-40 overflow-hidden bg-[#EBF5EB]">
        <img
            src="{{ $gambar }}"
            alt="{{ $nama }}"
            class="h-full w-full object-cover {{ $tersedia ? '' : 'grayscale' }}"
        >

        @if (!$tersedia)
            <div class="absolute inset-0 flex items-center justify-center bg-black/45">
                <span class="rounded-full bg-black/70 px-4 py-1.5 font-bold text-white">
                    Stok Habis
                </span>
            </div>
        @endif

        <div class="absolute left-2 top-2 flex flex-col gap-1">
            @if ($badge)
                <span class="rounded-full bg-[#2E7D32] px-2 py-0.5 text-xs font-semibold text-white">
                    {{ $badge }}
                </span>
            @endif

            @if ($bisaNego)
                <span class="rounded-full bg-[#F4B400] px-2 py-0.5 text-xs font-semibold text-[#1A2A00]">
                    Bisa Nego
                </span>
            @endif
        </div>
    </div>

    <div class="flex flex-1 flex-col gap-2 p-3">
        <span class="self-start rounded-full bg-[#EBF5EB] px-2 py-0.5 text-xs font-medium text-[#1E5F3E]">
            {{ $kategori }}
        </span>

        <h3 class="line-clamp-2 text-base font-semibold leading-tight text-[#1A2A1A]">
            {{ $nama }}
        </h3>

        <div>
            <div class="text-lg font-bold text-[#2E7D32]">
                Rp{{ number_format($harga, 0, ',', '.') }}
                <span class="text-sm font-normal text-[#5A7A5A]">/{{ $satuan }}</span>
            </div>

            <div class="text-sm text-[#5A7A5A]">
                Minimal pesan {{ $minimalPesan }} {{ $satuan }}
            </div>
        </div>

        <div class="mt-auto border-t border-[#F0F4EF] pt-2">
            <div class="truncate text-sm font-medium text-[#5A7A5A]">
                {{ $petani }}
            </div>

            <div class="mt-0.5 flex items-center gap-1 text-sm text-[#5A7A5A]">
                <span class="text-[#F4B400]">★</span>
                <span>{{ number_format($rating, 1) }}</span>
                <span>•</span>
                <span class="truncate">{{ $kabupaten }}</span>
            </div>
        </div>
    </div>
</a>
