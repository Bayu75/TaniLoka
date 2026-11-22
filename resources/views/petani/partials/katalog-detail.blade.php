@php
    $p = $produk;
    $kedaluwarsa = ! empty($p['kedaluwarsa']) || (($p['sisaHari'] ?? null) !== null && $p['sisaHari'] < 0);
    $sisa = $p['sisaHari'] ?? null;
    $hargaLabel = 'Rp'.number_format((int) ($p['harga'] ?? 0), 0, ',', '.');
    $idSaya = (int) ($idSaya ?? 0);
    $lain = collect($produkSemua ?? [])
        ->filter(fn ($row) => (int) ($row['petani_id'] ?? 0) === (int) ($p['petani_id'] ?? 0) && (int) $row['id'] !== (int) $p['id'])
        ->take(4)
        ->values();
    $kembaliQuery = request()->except('produk');
@endphp
<div class="pb-10">
    <div class="mx-auto max-w-4xl px-4 pt-5">
        <a href="{{ route('petani.katalog', $kembaliQuery) }}" class="inline-flex items-center gap-1 py-2 text-sm font-semibold text-[#2E7D32] hover:text-[#1E5F3E]">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0"><path d="m15 18-6-6 6-6" /></svg>
            <span>Kembali ke katalog</span>
        </a>
    </div>

    <div class="mx-auto max-w-4xl px-4 py-6">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <div class="relative aspect-square overflow-hidden rounded-2xl bg-[#EBF5EB]">
                    @if (empty($p['gambar']))
                        <div class="flex h-full w-full items-center justify-center bg-[#DDDDDD] px-4">
                            <span class="text-center text-2xl leading-tight font-semibold break-words text-[#A3A3A3]">{{ $p['nama'] }}</span>
                        </div>
                    @else
                        <img src="{{ $p['gambar'] }}" alt="{{ $p['nama'] }}" class="h-full w-full object-cover {{ $kedaluwarsa ? 'grayscale' : '' }}" onerror="this.classList.add('hidden'); this.nextElementSibling?.classList.remove('hidden')">
                        <div class="hidden flex h-full w-full items-center justify-center bg-[#DDDDDD] px-4">
                            <span class="text-center text-2xl leading-tight font-semibold break-words text-[#A3A3A3]">{{ $p['nama'] }}</span>
                        </div>
                    @endif
                    @if ($kedaluwarsa)
                        <div class="absolute inset-0 z-10 flex items-center justify-center bg-black/20">
                            <span class="rounded-full bg-black/70 px-5 py-2.5 text-center text-base font-bold text-white">Produk Tidak Tersedia</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-[#EBF5EB] px-3 py-1 text-xs font-bold text-[#2E7D32]">{{ $p['kategori'] }}</span>
                    @if (! empty($p['grade']))
                        <span class="rounded-full bg-[#FFF9E6] px-3 py-1 text-xs font-bold text-[#806000]">Grade {{ $p['grade'] }}</span>
                    @endif
                    @if ($kedaluwarsa)
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-600">● Tidak Tersedia</span>
                    @elseif (! empty($p['stok']))
                        <span class="rounded-full bg-[#DCFCE7] px-3 py-1 text-xs font-bold text-[#15803D]">● Tersedia</span>
                    @else
                        <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-600">● Stok Habis</span>
                    @endif
                </div>

                <h1 class="text-2xl font-extrabold text-[#1A2A1A]">{{ $p['nama'] }}</h1>

                <div class="rounded-2xl bg-[#EBF5EB] p-4">
                    <div class="text-2xl font-extrabold {{ $kedaluwarsa ? 'text-gray-400' : 'text-[#2E7D32]' }}">
                        <span>{{ $hargaLabel }}</span>
                        <span class="text-sm font-normal {{ $kedaluwarsa ? 'text-gray-400' : 'text-[#5A7A5A]' }}">/kg</span>
                    </div>
                    <p class="mt-1 text-sm text-[#5A7A5A]">
                        Minimal pesan: <strong class="text-[#1A2A1A]">{{ $p['minimal'] }} kg</strong>
                        · Stok: <strong class="text-[#1A2A1A]">{{ $p['stokKg'] }} kg</strong>
                    </p>
                    @if (! empty($p['tersediaSampai']))
                        <div class="mt-3 flex items-center gap-3 rounded-2xl border p-3 {{ $sisa < 0 ? 'border-red-200 bg-gray-50' : ($sisa <= 3 ? 'border-red-200 bg-red-50' : 'border-green-200 bg-green-50') }}">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $sisa < 0 ? 'bg-gray-200 text-red-600' : ($sisa <= 3 ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-700') }}">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs {{ $sisa < 0 ? 'text-gray-500' : ($sisa <= 3 ? 'text-red-600' : 'text-green-700') }}">Tersedia sampai</p>
                                <p class="text-sm font-bold {{ $sisa < 0 ? 'text-gray-800' : ($sisa <= 3 ? 'text-red-700' : 'text-green-800') }}">{{ $p['labelDetailTanggal'] }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $sisa < 0 ? 'bg-gray-200 text-red-700' : ($sisa <= 3 ? 'bg-red-600 text-white' : 'bg-green-600 text-white') }}">{{ $p['labelDetailSisa'] }}</span>
                        </div>
                    @endif
                    <p class="mt-3 text-xs text-[#8A762B]">★ Harga dapat dinegosiasikan untuk pembelian dalam jumlah besar.</p>
                </div>

                <div class="rounded-2xl border border-[#D4E8D0] bg-white p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#2E7D32] font-bold text-white">{{ mb_strtoupper(mb_substr(trim((string) ($p['petani'] ?? 'P')), 0, 1)) }}</div>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-[#1A2A1A]">{{ $p['petani'] }}</p>
                            @if (! empty($p['lokasi']))
                                <p class="text-xs text-[#5A7A5A]">📍 {{ $p['lokasi'] }}, Bali</p>
                            @endif
                            <p class="mt-1 text-xs text-[#5A7A5A]">Petani lokal</p>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="border-b border-[#D4E8D0] pb-2">
                        <span class="border-b-2 border-[#2E7D32] pb-2 text-sm font-bold text-[#2E7D32]">Informasi Produk</span>
                    </div>
                    <p class="mt-3 text-sm leading-relaxed text-[#435343]">{{ $p['deskripsi'] ?: 'Belum ada deskripsi dari petani.' }}</p>
                    <div class="mt-3 rounded-xl bg-[#EEF5EE] p-3">
                        <p class="text-xs font-bold text-[#789078]">Tanggal Panen</p>
                        <p class="mt-1 text-sm text-[#435343]">{{ $p['tanggalPanen'] ?: 'Tersedia sesuai jadwal panen petani.' }}</p>
                    </div>
                    @if (! empty($p['lokasi']))
                        <div class="mt-2 rounded-xl bg-[#EEF5EE] p-3">
                            <p class="text-xs font-bold text-[#789078]">Kabupaten</p>
                            <p class="mt-1 text-sm text-[#435343]">{{ $p['lokasi'] }}, Bali</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if ((int) ($p['petani_id'] ?? 0) === $idSaya)
            <div class="mt-7 rounded-2xl border border-[#D4E8D0] bg-white p-4">
                <p class="text-sm text-[#435343]">Ini produk Anda. Ubah stok, harga, atau ketersediaan lewat Kelola Produk.</p>
                <a href="{{ (\Illuminate\Support\Facades\Route::has('petani.produk.index') ? route('petani.produk.index') : url('/petani/produk')) }}" class="mt-3 inline-flex min-h-12 items-center justify-center rounded-xl bg-[#F4B400] px-5 font-bold text-[#1A2A00] transition hover:bg-[#E6A800]">Kelola Produk</a>
            </div>
        @endif

        @if ($kedaluwarsa)
            <div class="mt-7 rounded-2xl border border-gray-200 bg-gray-50 p-5">
                <p class="font-bold text-gray-700">Produk tidak tersedia</p>
                <p class="mt-1 text-sm text-gray-500">Produk ini sudah kedaluwarsa.</p>
            </div>
        @elseif (empty($p['stok']))
            <div class="mt-7 rounded-2xl border border-red-200 bg-red-50 p-5">
                <p class="font-bold text-red-600">Stok Habis</p>
                <p class="mt-1 text-sm text-red-500">Maaf, produk ini sedang tidak tersedia.</p>
            </div>
        @endif

        @if ($lain->isNotEmpty())
            <div class="mt-10">
                <h2 class="mb-4 text-lg font-bold text-[#1A2A1A]">Produk lain dari {{ $p['petani'] }}</h2>
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                    @foreach ($lain as $itemLain)
                        <a href="{{ route('petani.katalog', array_merge(request()->except('produk'), ['produk' => $itemLain['id']])) }}" class="overflow-hidden rounded-2xl border border-[#D4E8D0] bg-white text-left no-underline transition hover:shadow-md">
                            <div class="relative aspect-square bg-gray-200">
                                @if (! empty($itemLain['gambar']))
                                    <img src="{{ $itemLain['gambar'] }}" alt="{{ $itemLain['nama'] }}" class="h-full w-full object-cover">
                                @else
                                    <span class="flex h-full items-center justify-center px-2 text-center text-sm font-bold text-gray-400">{{ $itemLain['nama'] }}</span>
                                @endif
                            </div>
                            <div class="p-3">
                                <p class="truncate text-sm font-bold text-[#1A2A1A]">{{ $itemLain['nama'] }}</p>
                                <p class="mt-1 text-sm font-bold text-[#2E7D32]">Rp{{ number_format((int) $itemLain['harga'], 0, ',', '.') }}/kg</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
