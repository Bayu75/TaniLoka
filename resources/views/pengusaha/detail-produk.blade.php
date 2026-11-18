@extends('layouts.pengusaha')

@section('title', $produk->nama . ' - TaniLoka')

@section('content')

@php
    $petani = $produk->petani;
    $kabupaten = $petani->alamatUtama?->kabupaten;
    $fotoUtama = $produk->foto->first()?->url ?? 'https://placehold.co/800x800?text=TaniLoka';
    $minimal = (float) $produk->min_order_kg;
    $stok = (float) $produk->stok_kg;
    $tersediaSampai = $produk->tersedia_sampai ? \Illuminate\Support\Carbon::parse($produk->tersedia_sampai) : null;
    $sisaHari = $tersediaSampai ? (int) now()->startOfDay()->diffInDays($tersediaSampai->copy()->startOfDay(), false) : null;
    $kadaluarsa = $tersediaSampai && $sisaHari < 0;
    $bisaDibeli = $stok > 0 && ! $kadaluarsa;
    $flashSale = $produk->sedangFlashSale() && ! $kadaluarsa;
    $redup = $bisaDibeli ? '' : 'grayscale opacity-60';
@endphp

{{-- HEADER HALAMAN --}}
<x-page-header judul="Detail Produk" :kembali="route('pengusaha.katalog')" lebar="max-w-4xl" />

<div class="mx-auto max-w-4xl px-4 py-6" x-data="{ fotoAktif: @js($fotoUtama) }">

    {{-- PESAN SUKSES / ERROR --}}
    @if (session('sukses'))
        <div class="mb-5 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-[#D4E8D0] bg-[#EAF5E8] px-4 py-3 text-sm text-[#087C3E]">
            <span>{{ session('sukses') }}</span>
            <a href="{{ route('pengusaha.keranjang') }}" class="font-bold hover:underline">Lihat Keranjang →</a>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- INFORMASI UTAMA --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

        {{-- FOTO PRODUK --}}
        <div class="{{ $redup }}">
            <div class="aspect-square overflow-hidden rounded-2xl bg-[#EBF5EB]">
                <img :src="fotoAktif" src="{{ $fotoUtama }}" alt="{{ $produk->nama }}" class="h-full w-full object-cover">
            </div>

            @if ($produk->foto->count() > 1)
                <div class="mt-3 flex gap-2">
                    @foreach ($produk->foto as $foto)
                        <button type="button"
                                @click="fotoAktif = @js($foto->url)"
                                :class="fotoAktif === @js($foto->url) ? 'border-[#2E7D32]' : 'border-[#D4E8D0]'"
                                class="h-16 w-16 overflow-hidden rounded-xl border-2">
                            <img src="{{ $foto->url }}" alt="Foto {{ $produk->nama }}" class="h-full w-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- INFORMASI PRODUK --}}
        <div class="flex flex-col gap-4">

            {{-- KATEGORI, GRADE, DAN STOK --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full bg-[#EBF5EB] px-3 py-1 text-xs font-bold text-[#2E7D32] {{ $redup }}">
                    {{ $produk->kategori?->nama }}
                </span>

                @if ($produk->grade)
                    <span class="rounded-full bg-[#FFF9E6] px-3 py-1 text-xs font-bold text-[#806000] {{ $redup }}">
                        Grade {{ $produk->grade }}
                    </span>
                @endif

                @if ($bisaDibeli)
                    <span class="rounded-full bg-[#DCFCE7] px-3 py-1 text-xs font-bold text-[#15803D]">● Tersedia</span>
                @else
                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-600">● {{ $kadaluarsa ? 'Tidak Tersedia' : 'Stok Habis' }}</span>
                @endif
            </div>

            <h1 class="text-2xl font-extrabold {{ $bisaDibeli ? 'text-[#1A2A1A]' : 'text-gray-400' }}">{{ $produk->nama }}</h1>

            {{-- STATUS TIDAK TERSEDIA --}}
            @unless ($bisaDibeli)
                <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 p-4">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M5.6 5.6l12.8 12.8"/>
                        </svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="font-bold text-red-600">{{ $kadaluarsa ? 'Produk Tidak Tersedia' : 'Stok Habis' }}</p>
                        <p class="mt-0.5 text-sm text-red-500">
                            {{ $kadaluarsa
                                ? 'Masa tersedia produk ini berakhir pada ' . $tersediaSampai->translatedFormat('d F Y') . '.'
                                : 'Maaf, stok produk ini sedang habis.' }}
                        </p>
                    </div>
                </div>
            @endunless

            {{-- HARGA --}}
            <div class="rounded-2xl bg-[#EBF5EB] p-4 {{ $redup }}">
                <div class="text-2xl font-extrabold text-[#2E7D32]">
                    @if ($flashSale)
                        <span class="mb-1 block w-fit rounded-full bg-red-600 px-2.5 py-0.5 text-xs font-bold text-white">⚡ FLASH SALE</span>
                        <span class="mr-2 text-base font-normal text-gray-400 line-through">Rp{{ number_format($produk->harga_per_kg, 0, ',', '.') }}</span>
                        <span class="text-red-600">Rp{{ number_format($produk->harga_flash_sale, 0, ',', '.') }}</span>
                    @else
                        <span class="{{ $bisaDibeli ? '' : 'line-through' }}">Rp{{ number_format($produk->harga_per_kg, 0, ',', '.') }}</span>
                    @endif
                    <span class="text-sm font-normal text-[#5A7A5A]">/kg</span>
                </div>

                <p class="mt-1 text-sm text-[#5A7A5A]">
                    Minimal pesan: <strong class="text-[#1A2A1A]">{{ $minimal }} kg</strong>
                    · Stok: <strong class="text-[#1A2A1A]">{{ $stok }} kg</strong>
                </p>

                @if ($tersediaSampai && ! $kadaluarsa)
                    @php $mendesak = $sisaHari <= 2; @endphp
                    <div class="mt-3 flex items-center gap-3 rounded-xl border px-4 py-3 {{ $mendesak ? 'border-red-200 bg-red-50' : 'border-[#D4E8D0] bg-[#F5FAF4]' }}">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $mendesak ? 'bg-red-100 text-red-600' : 'bg-[#E8F5E9] text-[#2E7D32]' }}">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 7v5l3 2"/>
                            </svg>
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium {{ $mendesak ? 'text-red-500' : 'text-[#5A7A5A]' }}">Tersedia sampai</p>
                            <p class="font-bold {{ $mendesak ? 'text-red-600' : 'text-[#1A2A1A]' }}">
                                {{ $tersediaSampai->translatedFormat('d F Y') }}
                            </p>
                        </div>

                        @if ($sisaHari === 0)
                            <span class="shrink-0 rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white">Hari ini</span>
                        @elseif ($mendesak)
                            <span class="shrink-0 rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white">{{ $sisaHari }} hari lagi</span>
                        @else
                            <span class="shrink-0 rounded-full bg-[#E8F5E9] px-3 py-1 text-xs font-bold text-[#2E7D32]">{{ $sisaHari }} hari lagi</span>
                        @endif
                    </div>
                @endif

                @if ($bisaDibeli)
                    <p class="mt-3 text-xs text-[#8A762B]">
                        ★ Harga dapat dinegosiasikan untuk pembelian dalam jumlah besar.
                    </p>
                @endif
            </div>

            {{-- INFORMASI PETANI --}}
            <div class="rounded-2xl border border-[#D4E8D0] bg-white p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#2E7D32] font-bold text-white {{ $redup }}">
                        {{ strtoupper(substr($petani->name, 0, 1)) }}
                    </div>

                    <div class="min-w-0 flex-1 {{ $redup }}">
                        <p class="font-bold text-[#1A2A1A]">{{ $petani->name }}</p>
                        @if ($kabupaten)
                            <p class="text-xs text-[#5A7A5A]">📍 {{ $kabupaten }}, Bali</p>
                        @endif
                        <p class="mt-1 text-xs text-[#5A7A5A]">Petani lokal</p>
                    </div>

                    <a href="{{ route('pengusaha.toko', $petani) }}"
                       class="shrink-0 text-xs font-bold text-[#2E7D32] hover:underline">
                        Kunjungi Toko →
                    </a>
                </div>
            </div>

            {{-- DESKRIPSI DAN DETAIL --}}
            <div class="{{ $redup }}">
                <div class="border-b border-[#D4E8D0] pb-2">
                    <span class="border-b-2 border-[#2E7D32] pb-2 text-sm font-bold text-[#2E7D32]">Informasi Produk</span>
                </div>

                <p class="mt-3 text-sm leading-relaxed text-[#435343]">
                    {{ $produk->deskripsi ?: 'Belum ada deskripsi dari petani.' }}
                </p>

                <div class="mt-3 rounded-xl bg-[#EEF5EE] p-3">
                    <p class="text-xs font-bold text-[#789078]">Tanggal Panen</p>
                    <p class="mt-1 text-sm text-[#435343]">
                        {{ $produk->tanggal_panen?->translatedFormat('d F Y') ?? 'Tersedia sesuai jadwal panen petani.' }}
                    </p>
                </div>

                @if ($kabupaten)
                    <div class="mt-2 rounded-xl bg-[#EEF5EE] p-3">
                        <p class="text-xs font-bold text-[#789078]">Kabupaten</p>
                        <p class="mt-1 text-sm text-[#435343]">{{ $kabupaten }}, Bali</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- PILIHAN PEMBELIAN --}}
    @if ($bisaDibeli)

        @if ($sudahDiKeranjang)
            <p class="mt-7 rounded-xl bg-[#FFF9E6] px-4 py-3 text-sm text-[#806000]">
                Produk ini sudah ada di keranjangmu ({{ (float) $sudahDiKeranjang->jumlah_kg }} kg). Jumlah yang kamu tambahkan akan dijumlahkan.
            </p>
        @endif

        <form action="{{ route('pengusaha.keranjang.tambah', $produk) }}" method="POST"
              class="mt-4 rounded-2xl border border-[#D4E8D0] bg-white p-4">
            @csrf

            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <label for="jumlah" class="mb-2 block text-sm font-bold text-[#1A2A1A]">Jumlah Pembelian</label>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="ubahJumlah(-1)"
                                class="h-10 w-10 rounded-xl bg-[#F1F5F1] font-bold text-[#2E7D32] hover:bg-[#DDEBDD]">−</button>

                        <input type="number" id="jumlah" name="jumlah_kg" step="0.01"
                               min="{{ $minimal }}" max="{{ $stok }}"
                               value="{{ old('jumlah_kg', $minimal) }}" required
                               oninput="hitungTotal(); this.setCustomValidity('')"
                               class="h-10 w-20 rounded-xl border border-[#D4E8D0] text-center font-bold text-[#1A2A1A]">

                        <span class="text-sm text-[#5A7A5A]">kg</span>

                        <button type="button" onclick="ubahJumlah(1)"
                                class="h-10 w-10 rounded-xl bg-[#F1F5F1] font-bold text-[#2E7D32] hover:bg-[#DDEBDD]">+</button>
                    </div>

                    <p class="mt-2 text-xs text-[#789078]">Minimal {{ $minimal }} kg.</p>
                </div>

                <div class="text-sm font-semibold text-[#5A7A5A]">
                    Total
                    <span id="totalHarga" class="ml-1 text-base font-extrabold text-[#2E7D32]">
                        Rp{{ number_format($produk->hargaAktif() * $minimal, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-3 md:grid-cols-2">
                <button type="submit" name="aksi" value="keranjang"
                        class="min-h-12 rounded-xl border-2 border-[#2E7D32] bg-white px-4 font-bold text-[#2E7D32] transition hover:bg-[#EBF5EB]">
                    🛒 Tambah ke Keranjang
                </button>

                <button type="submit" name="aksi" value="beli"
                        class="min-h-12 rounded-xl bg-[#F4B400] px-4 font-bold text-[#1A2A00] transition hover:bg-[#E6A800]">
                    Beli Sekarang
                </button>

                <a href="{{ route('pengusaha.pesan', ['produk' => $produk->id]) }}"
                   class="flex min-h-12 items-center justify-center rounded-xl border-2 border-[#F4D36B] bg-[#FFF9E6] px-4 font-bold text-[#806000] transition hover:bg-[#FFF1C2] md:col-span-2">
                    Tawar Harga
                </a>
            </div>
        </form>

    @endif

    {{-- PRODUK LAIN DARI PETANI INI --}}
    @if ($produkLain->isNotEmpty())
        <div class="mt-10">
            <h2 class="mb-4 text-lg font-bold text-[#1A2A1A]">Produk lain dari {{ $petani->name }}</h2>

            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                @foreach ($produkLain as $lain)
                    <a href="{{ route('pengusaha.produk.detail', $lain) }}"
                       class="overflow-hidden rounded-2xl border border-[#D4E8D0] bg-white transition hover:shadow-md">
                        <img src="{{ $lain->fotoUtama?->url ?? 'https://placehold.co/300x300?text=TaniLoka' }}"
                             alt="{{ $lain->nama }}" class="aspect-square w-full object-cover">
                        <div class="p-3">
                            <p class="truncate text-sm font-bold text-[#1A2A1A]">{{ $lain->nama }}</p>
                            <p class="mt-1 text-sm font-bold text-[#2E7D32]">
                                Rp{{ number_format($lain->hargaAktif(), 0, ',', '.') }}/kg
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>

{{-- SCRIPT JUMLAH DAN TOTAL HARGA --}}
@if ($bisaDibeli)
<script>
    const hargaProduk = {{ (float) $produk->hargaAktif() }};
    const minimalProduk = {{ $minimal }};
    const stokProduk = {{ $stok }};
    const inputJumlah = document.getElementById('jumlah');

    function ubahJumlah(perubahan) {
        let jumlah = parseFloat(inputJumlah.value) || minimalProduk;
        jumlah = Math.max(minimalProduk, Math.min(stokProduk, jumlah + perubahan));
        inputJumlah.value = jumlah;
        inputJumlah.setCustomValidity('');
        hitungTotal();
    }

    function hitungTotal() {
        let jumlah = parseFloat(inputJumlah.value) || 0;
        jumlah = Math.max(0, Math.min(stokProduk, jumlah));
        document.getElementById('totalHarga').textContent =
            'Rp' + Math.round(hargaProduk * jumlah).toLocaleString('id-ID');
    }

    inputJumlah.addEventListener('invalid', function () {
        if (this.validity.valueMissing) {
            this.setCustomValidity('Silakan masukkan jumlah pembelian.');
        } else if (this.validity.rangeUnderflow) {
            this.setCustomValidity('Jumlah minimal pembelian adalah ' + minimalProduk + ' kg.');
        } else if (this.validity.rangeOverflow) {
            this.setCustomValidity('Jumlah pembelian melebihi stok. Maksimal ' + stokProduk + ' kg.');
        } else if (this.validity.badInput || this.validity.stepMismatch) {
            this.setCustomValidity('Silakan masukkan jumlah pembelian yang valid.');
        }
    });
</script>
@endif

@endsection