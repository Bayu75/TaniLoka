@extends('layouts.pengusaha')

@section('title', $produk->nama . ' - TaniLoka')

@section('content')

@php
    $petani = $produk->petani;
    $kabupaten = $petani->profilPetani?->kabupaten;
    $fotoUtama = $produk->foto->first()?->url ?? 'https://placehold.co/800x800?text=TaniLoka';
    $minimal = (float) $produk->min_order_kg;
    $stok = (float) $produk->stok_kg;
@endphp

{{-- KEMBALI KE KATALOG --}}
<div class="mx-auto mb-5 max-w-4xl pt-5">
    <a href="{{ route('pengusaha.katalog') }}"
       class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 transition hover:text-green-700">
        <span class="text-xl leading-none">‹</span>
        <span>Kembali ke Katalog</span>
    </a>
</div>

<div class="mx-auto max-w-4xl" x-data="{ fotoAktif: @js($fotoUtama) }">

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
        <div>
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
                <span class="rounded-full bg-[#EBF5EB] px-3 py-1 text-xs font-bold text-[#2E7D32]">
                    {{ $produk->kategori?->nama }}
                </span>

                @if ($produk->grade)
                    <span class="rounded-full bg-[#FFF9E6] px-3 py-1 text-xs font-bold text-[#806000]">
                        Grade {{ $produk->grade }}
                    </span>
                @endif

                @if ($stok > 0)
                    <span class="rounded-full bg-[#DCFCE7] px-3 py-1 text-xs font-bold text-[#15803D]">● Tersedia</span>
                @else
                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-600">● Stok Habis</span>
                @endif
            </div>

            <h1 class="text-2xl font-extrabold text-[#1A2A1A]">{{ $produk->nama }}</h1>

            {{-- HARGA --}}
            <div class="rounded-2xl bg-[#EBF5EB] p-4">
                <div class="text-2xl font-extrabold text-[#2E7D32]">
                    Rp{{ number_format($produk->harga_per_kg, 0, ',', '.') }}
                    <span class="text-sm font-normal text-[#5A7A5A]">/kg</span>
                </div>

                <p class="mt-1 text-sm text-[#5A7A5A]">
                    Minimal pesan: <strong class="text-[#1A2A1A]">{{ $minimal }} kg</strong>
                    · Stok: <strong class="text-[#1A2A1A]">{{ $stok }} kg</strong>
                </p>

                <p class="mt-3 text-xs text-[#8A762B]">
                    ★ Harga dapat dinegosiasikan untuk pembelian dalam jumlah besar.
                </p>
            </div>

            {{-- INFORMASI PETANI --}}
            <div class="rounded-2xl border border-[#D4E8D0] bg-white p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#2E7D32] font-bold text-white">
                        {{ strtoupper(substr($petani->name, 0, 1)) }}
                    </div>

                    <div class="min-w-0 flex-1">
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
            <div>
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
    @if ($stok > 0)

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
                        Rp{{ number_format($produk->harga_per_kg * $minimal, 0, ',', '.') }}
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

    @else

        <div class="mt-7 rounded-2xl border border-red-200 bg-red-50 p-5">
            <p class="font-bold text-red-600">Stok Habis</p>
            <p class="mt-1 text-sm text-red-500">Maaf, produk ini sedang tidak tersedia.</p>
        </div>

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
                                Rp{{ number_format($lain->harga_per_kg, 0, ',', '.') }}/kg
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>

{{-- SCRIPT JUMLAH DAN TOTAL HARGA --}}
@if ($stok > 0)
<script>
    const hargaProduk = {{ (float) $produk->harga_per_kg }};
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