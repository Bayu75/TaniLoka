@extends('layouts.petani')

@section('title', 'Katalog Produk - TaniLoka')

@section('main-class', 'mx-auto max-w-6xl px-4 pb-24 pt-1 md:pb-10 md:pt-16')

@section('content')
@if (! empty($pilihProduk))
    @include('petani.partials.katalog-detail', [
        'produk' => $pilihProduk,
        'produkSemua' => $produk,
        'idSaya' => $idSaya ?? 0,
    ])
@else
@php
    $ikonKategori = [
        'sayuran' => '🥬',
        'buah' => '🍅',
        'bumbu' => '🌶️',
        'umbi' => '🥕',
        'beras-biji' => '🌾',
        'beras & biji' => '🌾',
        'beras dan biji' => '🌾',
    ];
    $ikonUntuk = function ($item) use ($ikonKategori) {
        $nama = mb_strtolower(trim((string) ($item->nama ?? '')));
        if (isset($ikonKategori[$nama])) {
            return $ikonKategori[$nama];
        }
        if (str_contains($nama, 'beras')) {
            return '🌾';
        }
        if (str_contains($nama, 'buah')) {
            return '🍅';
        }
        if (str_contains($nama, 'bumbu')) {
            return '🌶️';
        }
        if (str_contains($nama, 'sayur')) {
            return '🥬';
        }
        if (str_contains($nama, 'umbi')) {
            return '🥕';
        }
        $slug = (string) ($item->slug ?? '');
        if (isset($ikonKategori[$slug])) {
            return $ikonKategori[$slug];
        }
        foreach ($ikonKategori as $kunci => $ikon) {
            if ($slug !== '' && str_starts_with($slug, $kunci)) {
                return $ikon;
            }
        }

        return $item->ikon ?: '🌱';
    };
    $nilaiKategori = fn ($item) => (string) data_get($item, $kategoriKey, $item->id ?? '');
@endphp
<script>
  function katalogPage() {
    return {
      filterOpen: false,
      kategoriOverflow: false,
      kategoriBisaKiri: false,
      kategoriBisaKanan: false,
      pilih: null,
      toko: null,
      riwayatNavigasi: [],
      tabDetail: 'info',
      fotoAktif: 0,
      banner: @js($banner),
      namaSaya: @js($namaSaya),
      idSaya: @js($idSaya ?? 0),
      produk: (function () { const raw = @js($produk); return Array.isArray(raw) ? raw : Object.values(raw || {}); })(),
      updateKategoriScroll() {
        const track = this.$refs.kategoriTrack;
        if (!track || !track.clientWidth) return;
        const chips = Array.from(track.children);
        const contentWidth = chips.reduce((width, chip) => width + chip.offsetWidth, 0)
            + Math.max(0, chips.length - 1) * 8;
        this.kategoriOverflow = contentWidth > (track.parentElement?.clientWidth || track.clientWidth) + 1;
        this.$nextTick(() => {
            const max = Math.max(0, track.scrollWidth - track.clientWidth);
            this.kategoriBisaKiri = this.kategoriOverflow && track.scrollLeft > 1;
            this.kategoriBisaKanan = this.kategoriOverflow && track.scrollLeft < max - 1;
        });
      },
      geserKategori(arah) {
        const track = this.$refs.kategoriTrack;
        if (!track) return;
        const reduced = window.matchMedia
            && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        track.scrollBy({
            left: arah * Math.max(120, track.clientWidth * 0.75),
            behavior: reduced ? 'auto' : 'smooth'
        });
      },
      sudahKedaluwarsa(p) {
        if (!p) return false;
        if (p.kedaluwarsa) return true;
        return p.sisaHari !== null && p.sisaHari !== undefined && Number(p.sisaHari) < 0;
      },
      produkToko() {
        return this.produk
          .filter(p => p.petani === this.toko)
          .slice()
          .sort((a, b) => Number(this.sudahKedaluwarsa(a)) - Number(this.sudahKedaluwarsa(b)));
      },
      infoToko() { return this.produkToko()[0] || {}; },
      produkById(id) {
        const list = Array.isArray(this.produk) ? this.produk : Object.values(this.produk || {});
        return list.find(p => Number(p.id) === Number(id)) || null;
      },
      daftarFoto(p) {
        if (p && Array.isArray(p.foto) && p.foto.length) return p.foto.filter(Boolean);
        if (p && p.gambar) return [p.gambar];
        return [];
      },
      fotoUtama(p) {
        if (!p) return '';
        const foto = this.daftarFoto(p);
        if (this.fotoAktif && foto.includes(this.fotoAktif)) return this.fotoAktif;
        return foto[0] || p.gambar || '';
      },
      produkLain() {
        if (!this.pilih) return [];
        return this.produk.filter(p => p.petani_id === this.pilih.petani_id && p.id !== this.pilih.id).slice(0, 4);
      },
      inisialDepan(n) {
        const s = (n || '?').toString().trim();
        return (s.charAt(0) || '?').toUpperCase();
      },
      buka(p) {
        if (!p) return;
        this.riwayatNavigasi.push({
            pilih: this.pilih,
            toko: this.toko,
            tabDetail: this.tabDetail,
            fotoAktif: this.fotoAktif
        });
        this.toko = null;
        this.pilih = p;
        this.tabDetail = 'info';
        this.fotoAktif = this.daftarFoto(p)[0] || p.gambar || '';
        this.filterOpen = false;
        window.scrollTo(0, 0);
      },
      kembaliInduk() {
        const induk = this.riwayatNavigasi.pop() || { pilih: null, toko: null };
        this.pilih = induk.pilih;
        this.toko = induk.toko;
        if (induk.tabDetail !== undefined) this.tabDetail = induk.tabDetail;
        if (induk.fotoAktif !== undefined) this.fotoAktif = induk.fotoAktif;
        window.scrollTo(0, 0);
      },
      tutup() { this.kembaliInduk(); },
      bukaToko(nama) {
        this.riwayatNavigasi.push({
            pilih: this.pilih,
            toko: this.toko,
            tabDetail: this.tabDetail,
            fotoAktif: this.fotoAktif
        });
        this.toko = nama;
        window.scrollTo(0, 0);
      },
      tutupToko() { this.kembaliInduk(); },
      inisial(n) {
        const s = (n || '?').toString().trim();
        const bagian = s.split(/\s+/).filter(Boolean);
        return (bagian.pop() || '?').charAt(0);
      },
      rupiah(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID'); },
      rupiahKartu(n) { return 'Rp' + Number(n || 0).toLocaleString('id-ID'); },
      bintang(r) {
        const n = Math.round(Number(r || 0));
        return '★★★★★'.slice(0, n) + '☆☆☆☆☆'.slice(0, Math.max(0, 5 - n));
      }
    };
  }
  window.katalogPage = katalogPage;
  document.addEventListener('alpine:init', function () {
    if (window.Alpine && typeof Alpine.data === 'function') {
      Alpine.data('katalogPage', window.katalogPage);
    }
  });
</script>
<div class="pb-10" x-data="katalogPage()">

<div x-show="!pilih && !toko" x-init="$nextTick(() => updateKategoriScroll())" @resize.window="$nextTick(() => updateKategoriScroll())">

{{-- JUDUL DALAM BILAH PUTIH BERBORDIR --}}
<x-page-header judul="Katalog Produk" :kembali="false" lebar="max-w-6xl" />

<div class="pb-10" x-data="{ filterOpen: false }">
{{-- HEADER PUTIH KATALOG --}}
<div class="relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen border-b border-[#D4E8D0] bg-white">
    <div class="mx-auto max-w-6xl px-4 py-5 md:py-6">


    {{-- PENCARIAN --}}
    <section class="mb-5">
        <p class="mb-3 text-sm text-[#5A7A5A]">Temukan hasil pertanian segar langsung dari petani Bali.</p>

        <form action="{{ route('pengusaha.katalog') }}" method="GET">
            <div class="flex flex-col gap-2 sm:flex-row">

                {{-- INPUT PENCARIAN (penuh di HP) --}}
                <div class="relative w-full min-w-0 sm:flex-1">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#5A7A5A]">🔍</span>
                    <input
                        type="text"
                        name="q"
                        value="{{ $q }}"
                        placeholder="Cari produk, petani, lokasi..."
                        class="min-h-12 w-full rounded-xl border-2 border-[#D4E8D0] bg-white pl-11 pr-4 outline-none focus:border-[#2E7D32]"
                    >
                </div>

                {{-- TOMBOL CARI & SARING (di bawah input saat HP) --}}
                <div class="grid grid-cols-2 gap-2 sm:flex">
                    <button
                        type="submit"
                        class="flex min-h-12 items-center justify-center rounded-xl bg-[#F4B400] px-5 font-bold text-[#1A2A00] transition hover:bg-[#E6A800]"
                    >
                        🔍 Cari
                    </button>

                    <button
                        type="button"
                        @click="filterOpen = true"
                        class="flex min-h-12 items-center justify-center gap-2 rounded-xl border-2 border-[#D4E8D0] bg-white px-4 font-bold text-[#1A2A1A] hover:bg-[#EBF5EB]"
                    >
                        <span>☷</span>
                        Saring
                        @if ($activeCount > 0)
                            <span class="rounded-full bg-[#F4B400] px-2 py-0.5 text-xs">{{ $activeCount }}</span>
                        @endif
                    </button>
                </div>
            </div>

            {{-- MEMPERTAHANKAN FILTER SAAT SEARCH --}}
            <input type="hidden" name="cat" value="{{ $cat }}">
            <input type="hidden" name="district" value="{{ $district }}">
            <input type="hidden" name="min_price" value="{{ $minPrice }}">
            <input type="hidden" name="max_price" value="{{ $maxPrice }}">
            <input type="hidden" name="sort" value="{{ $sort }}">
        </form>
    </section>

            {{-- MEMPERTAHANKAN FILTER SAAT SEARCH --}}
            <input type="hidden" name="cat" value="{{ $cat }}">
            <input type="hidden" name="district" value="{{ $district }}">
            <input type="hidden" name="min_price" value="{{ $minPrice }}">
            <input type="hidden" name="max_price" value="{{ $maxPrice }}">
            <input type="hidden" name="sort" value="{{ $sort }}">
        </form>
    </section>

{{-- FILTER KATEGORI --}}
<section class="mb-5">
    <div class="relative" :class="kategoriOverflow ? 'px-9' : ''">
        <button
            type="button"
            x-show="kategoriOverflow"
            x-cloak
            :disabled="!kategoriBisaKiri"
            @click="geserKategori(-1)"
            aria-label="Geser kategori ke kiri"
            aria-controls="tl-katalog-categories"
            class="absolute top-1/2 left-0 z-10 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full bg-[#E5E7E5] text-[#4B554E] disabled:cursor-default disabled:opacity-35"
        >‹</button>

        <div
            id="tl-katalog-categories"
            x-ref="kategoriTrack"
            tabindex="0"
            role="group"
            aria-label="Kategori produk; geser untuk melihat semua kategori"
            @scroll.passive="updateKategoriScroll()"
            @keydown.left.prevent="geserKategori(-1)"
            @keydown.right.prevent="geserKategori(1)"
            class="flex gap-2 overflow-x-auto pb-1"
        >
            <a
                href="{{ route('petani.katalog', request()->except('cat')) }}"
                class="flex h-10 shrink-0 items-center rounded-full px-5 text-sm font-semibold {{ $cat === '' ? 'bg-[#2E7D32] text-white' : 'border border-[#D4E8D0] bg-white text-[#1A2A1A]' }}"
            >
                Semua
            </a>

            @foreach ($kategoriList as $item)
                <a
                    href="{{ route('petani.katalog', array_merge(request()->query(), ['cat' => $nilaiKategori($item)])) }}"
                    class="flex h-10 shrink-0 items-center gap-1.5 rounded-full px-4 text-sm font-semibold {{ $cat === $nilaiKategori($item) ? 'bg-[#2E7D32] text-white' : 'border border-[#D4E8D0] bg-white text-[#1A2A1A]' }}"
                >
                    <span>{{ $ikonUntuk($item) }}</span>
                    <span>{{ $item->nama }}</span>
                </a>
            @endforeach
        </div>

        <button
            type="button"
            x-show="kategoriOverflow"
            x-cloak
            :disabled="!kategoriBisaKanan"
            @click="geserKategori(1)"
            aria-label="Geser kategori ke kanan"
            aria-controls="tl-katalog-categories"
            class="absolute top-1/2 right-0 z-10 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full bg-[#E5E7E5] text-[#4B554E] disabled:cursor-default disabled:opacity-35"
        >›</button>
    </div>
</section>

    {{-- HASIL DAN PENGURUTAN --}}
    <section>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <div class="text-sm text-[#5A7A5A]">
                <strong class="text-[#1A2A1A]">{{ $produkList->count() }}</strong>
                produk ditemukan
            </div>

            <form method="GET" action="{{ route('petani.katalog') }}">
                @foreach (request()->except('sort') as $key => $value)
                    @if (! is_array($value))
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach

                <select
                    name="sort"
                    onchange="this.form.submit()"
                    class="h-10 rounded-xl border border-[#D4E8D0] bg-white px-3 text-sm font-semibold text-[#1A2A1A] outline-none focus:border-[#2E7D32]"
                >
                    <option value="newest" @selected($sort === 'newest')>Terbaru</option>
                    <option value="price-asc" @selected($sort === 'price-asc')>Harga Termurah</option>
                    <option value="price-desc" @selected($sort === 'price-desc')>Harga Termahal</option>
                    <option value="rating" @selected($sort === 'rating')>Rating Tertinggi</option>
                </select>
            </form>
        </div>

        @if ($q || $activeCount > 0)
            <div class="mb-4 flex flex-wrap items-center gap-2">
                <span class="text-sm text-[#5A7A5A]">Penyaringan aktif:</span>

                @if ($q)
                    <span class="rounded-full bg-[#EBF5EB] px-3 py-1 text-xs font-semibold text-[#1E5F3E]">
                        "{{ $q }}"
                    </span>
                @endif

                @if ($cat)
                    <span class="rounded-full bg-[#EBF5EB] px-3 py-1 text-xs font-semibold text-[#1E5F3E]">
                        {{ $kategoriList->firstWhere($kategoriKey, $cat)?->nama ?? $cat }}
                    </span>
                @endif

                <a
                    href="{{ route('petani.katalog') }}"
                    class="px-2 py-2 text-sm font-bold text-[#2E7D32] hover:underline"
                >
                    Hapus semua
                </a>
            </div>
        @endif

{{-- GRID PRODUK --}}
@if ($produkList->count() > 0)

    <div class="grid grid-cols-2 items-stretch gap-3 lg:grid-cols-4">

        @foreach ($produkList as $itemProduk)
            @include('petani.partials.katalog-kartu', ['produk' => $itemProduk])
        @endforeach

    </div>

@else

    {{-- EMPTY STATE --}}
    <div class="rounded-2xl border-2 border-dashed border-[#D4E8D0] bg-white px-5 py-12 text-center">

        <div class="mb-4 text-5xl">🔍</div>

        <h2 class="mb-2 text-xl font-extrabold text-[#1A2A1A]">
            Produk tidak ditemukan
        </h2>

        <p class="mx-auto mb-5 max-w-sm text-sm text-[#5A7A5A]">
            Coba ubah kata kunci pencarian atau hapus beberapa filter.
        </p>

        <a
            href="{{ route('petani.katalog') }}"
            class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[#2E7D32] px-5 font-bold text-white hover:bg-[#1E5F3E]"
        >
            Hapus Pencarian & Filter
        </a>

    </div>

@endif

    </section>

</div>

    {{-- MODAL FILTER --}}
    <div
        x-cloak
        x-show="filterOpen"
        x-transition.opacity
        @keydown.escape.window="if (!pilih && !toko) filterOpen = false"
        class="fixed inset-0 z-[80] flex items-end justify-center bg-black/50 md:items-center"
        role="dialog"
        aria-modal="true"
        aria-label="Saring produk"
    >
        <div
            @click.outside="filterOpen = false"
            x-transition
            class="relative max-h-[90vh] w-full overflow-y-auto rounded-t-3xl bg-white p-5 md:max-w-lg md:rounded-3xl"
        >
            <div class="mb-5 flex items-center justify-between border-b border-[#D4E8D0] pb-4">
                <h2 class="text-xl font-extrabold text-[#1A2A1A]">
                    Saring Produk
                </h2>

                <button
                    type="button"
                    @click="filterOpen = false"
                    class="min-h-11 rounded-xl px-3 font-bold text-[#2E7D32] hover:bg-[#EBF5EB]"
                >
                    Tutup
                </button>
            </div>

            <form method="GET" action="{{ route('petani.katalog') }}" class="space-y-5">

                <div>
                    <label class="mb-2 block font-semibold text-[#1A2A1A]">
                        Kategori
                    </label>

                    <select name="cat" class="min-h-12 w-full rounded-xl border-2 border-[#D4E8D0] bg-white px-3">
                        <option value="">Semua kategori</option>
                      @foreach ($kategoriList as $item)
                        <option value="{{ $nilaiKategori($item) }}" @selected($cat === $nilaiKategori($item))>
                            {{ $item->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block font-semibold text-[#1A2A1A]">
                        Kabupaten
                    </label>

                    <select name="district" class="min-h-12 w-full rounded-xl border-2 border-[#D4E8D0] bg-white px-3">
                        <option value="">Semua kabupaten</option>
                        @foreach ($kabupatenList as $kabupaten)
                            <option value="{{ $kabupaten }}" @selected($district === $kabupaten)>
                                {{ $kabupaten }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block font-semibold text-[#1A2A1A]">
                        Rentang Harga (Rp/kg)
                    </label>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <span class="mb-1 block text-xs text-[#5A7A5A]">Harga terendah</span>
                            <input
                                type="number"
                                name="min_price"
                                min="0"
                                step="1000"
                                value="{{ $minPrice }}"
                                class="min-h-12 w-full rounded-xl border-2 border-[#D4E8D0] px-3"
                            >
                        </div>

                        <div>
                            <span class="mb-1 block text-xs text-[#5A7A5A]">Harga tertinggi</span>
                            <input
                                type="number"
                                name="max_price"
                                min="0"
                                step="1000"
                                value="{{ $maxPrice }}"
                                class="min-h-12 w-full rounded-xl border-2 border-[#D4E8D0] px-3"
                            >
                        </div>
                    </div>
                </div>


                <input type="hidden" name="q" value="{{ $q }}">
                <input type="hidden" name="sort" value="{{ $sort }}">

                <div class="flex gap-3 border-t border-[#D4E8D0] pt-4">
                    <a
                        href="{{ route('petani.katalog') }}"
                        class="flex min-h-12 flex-1 items-center justify-center rounded-xl border-2 border-[#D4E8D0] font-bold text-[#1A2A1A]"
                    >
                        Atur Ulang
                    </a>

                    <button
                        type="submit"
                        class="min-h-12 flex-1 rounded-xl bg-[#F4B400] font-bold text-[#1A2A00] hover:bg-[#E6A800]"
                    >
                        Terapkan Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

    {{-- ===== DETAIL PRODUK ===== --}}
    <template x-if="pilih && !toko">
        <div class="pb-10">
            <div class="mx-auto max-w-4xl px-4 pt-5">
                <button type="button" @click="tutup()" class="inline-flex items-center gap-1 py-2 text-sm font-semibold text-[#2E7D32] hover:text-[#1E5F3E] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2E7D32]">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                    <span>Detail Produk</span>
                </button>
            </div>

            <div class="mx-auto max-w-4xl px-4 py-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                    {{-- FOTO PRODUK --}}
                    <div>
                        <div class="relative aspect-square overflow-hidden rounded-2xl bg-[#EBF5EB]">
                            <div x-show="!fotoUtama(pilih)" class="flex h-full w-full items-center justify-center bg-[#DDDDDD] px-4">
                                <span class="text-center text-2xl leading-tight font-semibold break-words text-[#A3A3A3]" x-text="pilih.nama"></span>
                            </div>
                            <img x-show="fotoUtama(pilih)" :src="fotoUtama(pilih)" :alt="pilih.nama" class="h-full w-full object-cover" :class="sudahKedaluwarsa(pilih) && 'grayscale'" x-on:error="fotoAktif = ''">
                            <div x-show="sudahKedaluwarsa(pilih)" class="absolute inset-0 z-10 flex items-center justify-center bg-black/20">
                                <span class="rounded-full bg-black/70 px-5 py-2.5 text-center text-base font-bold text-white">Produk Tidak Tersedia</span>
                            </div>
                        </div>

                        <div class="mt-3 flex gap-2" x-show="daftarFoto(pilih).length > 1">
                            <template x-for="url in daftarFoto(pilih)" :key="url">
                                <button type="button"
                                        @click="fotoAktif = url"
                                        :class="fotoUtama(pilih) === url ? 'border-[#2E7D32]' : 'border-[#D4E8D0]'"
                                        class="h-16 w-16 overflow-hidden rounded-xl border-2">
                                    <img :src="url" :alt="pilih.nama" class="h-full w-full object-cover">
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- INFORMASI PRODUK --}}
                    <div class="flex flex-col gap-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-[#EBF5EB] px-3 py-1 text-xs font-bold text-[#2E7D32]" x-text="pilih.kategori"></span>
                            <span x-show="pilih.grade" class="rounded-full bg-[#FFF9E6] px-3 py-1 text-xs font-bold text-[#806000]" x-text="'Grade ' + pilih.grade"></span>
                            <span x-show="!sudahKedaluwarsa(pilih) && pilih.stok" class="rounded-full bg-[#DCFCE7] px-3 py-1 text-xs font-bold text-[#15803D]">● Tersedia</span>
                            <span x-show="!sudahKedaluwarsa(pilih) && !pilih.stok" class="rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-600">● Stok Habis</span>
                            <span x-show="sudahKedaluwarsa(pilih)" class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-600">● Tidak Tersedia</span>
                        </div>

                        <h1 class="text-2xl font-extrabold text-[#1A2A1A]" x-text="pilih.nama"></h1>

                        <div class="rounded-2xl bg-[#EBF5EB] p-4">
                            <div class="text-2xl font-extrabold" :class="sudahKedaluwarsa(pilih) ? 'text-gray-400' : 'text-[#2E7D32]'">
                                <span x-text="rupiahKartu(pilih.harga)"></span>
                                <span class="text-sm font-normal" :class="sudahKedaluwarsa(pilih) ? 'text-gray-400' : 'text-[#5A7A5A]'">/kg</span>
                            </div>
                            <p class="mt-1 text-sm text-[#5A7A5A]">
                                Minimal pesan: <strong class="text-[#1A2A1A]" x-text="pilih.minimal + ' kg'"></strong>
                                · Stok: <strong class="text-[#1A2A1A]" x-text="pilih.stokKg + ' kg'"></strong>
                            </p>

                            <div x-show="pilih.tersediaSampai" x-cloak class="mt-3 flex items-center gap-3 rounded-2xl border p-3"
                                :class="pilih.sisaHari < 0 ? 'border-red-200 bg-gray-50' : (pilih.sisaHari <= 3 ? 'border-red-200 bg-red-50' : 'border-green-200 bg-green-50')">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full"
                                    :class="pilih.sisaHari < 0 ? 'bg-gray-200 text-red-600' : (pilih.sisaHari <= 3 ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-700')">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs" :class="pilih.sisaHari < 0 ? 'text-gray-500' : (pilih.sisaHari <= 3 ? 'text-red-600' : 'text-green-700')">Tersedia sampai</p>
                                    <p class="text-sm font-bold" :class="pilih.sisaHari < 0 ? 'text-gray-800' : (pilih.sisaHari <= 3 ? 'text-red-700' : 'text-green-800')" x-text="pilih.labelDetailTanggal"></p>
                                </div>
                                <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold"
                                    :class="pilih.sisaHari < 0 ? 'bg-gray-200 text-red-700' : (pilih.sisaHari <= 3 ? 'bg-red-600 text-white' : 'bg-green-600 text-white')"
                                    x-text="pilih.labelDetailSisa"></span>
                            </div>

                            <p class="mt-3 text-xs text-[#8A762B]">
                                ★ Harga dapat dinegosiasikan untuk pembelian dalam jumlah besar.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-[#D4E8D0] bg-white p-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#2E7D32] font-bold text-white" x-text="inisialDepan(pilih.petani)"></div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-[#1A2A1A]" x-text="pilih.petani"></p>
                                    <p x-show="pilih.lokasi" class="text-xs text-[#5A7A5A]" x-text="'📍 ' + pilih.lokasi + ', Bali'"></p>
                                    <p class="mt-1 text-xs text-[#5A7A5A]">Petani lokal</p>
                                </div>
                                <button type="button" @click="bukaToko(pilih.petani)" class="shrink-0 text-xs font-bold text-[#2E7D32] hover:underline">
                                    Kunjungi Toko →
                                </button>
                            </div>
                        </div>

                        <div>
                            <div class="border-b border-[#D4E8D0] pb-2">
                                <span class="border-b-2 border-[#2E7D32] pb-2 text-sm font-bold text-[#2E7D32]">Informasi Produk</span>
                            </div>

                            <p class="mt-3 text-sm leading-relaxed text-[#435343]" x-text="pilih.deskripsi || 'Belum ada deskripsi dari petani.'"></p>

                            <div class="mt-3 rounded-xl bg-[#EEF5EE] p-3">
                                <p class="text-xs font-bold text-[#789078]">Tanggal Panen</p>
                                <p class="mt-1 text-sm text-[#435343]" x-text="pilih.tanggalPanen || 'Tersedia sesuai jadwal panen petani.'"></p>
                            </div>

                            <div x-show="pilih.lokasi" class="mt-2 rounded-xl bg-[#EEF5EE] p-3">
                                <p class="text-xs font-bold text-[#789078]">Kabupaten</p>
                                <p class="mt-1 text-sm text-[#435343]" x-text="pilih.lokasi + ', Bali'"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="Number(pilih.petani_id) === Number(idSaya)" class="mt-7 rounded-2xl border border-[#D4E8D0] bg-white p-4">
                    <p class="text-sm text-[#435343]">Ini produk Anda. Ubah stok, harga, atau ketersediaan lewat Kelola Produk.</p>
                    <a href="{{ (\Illuminate\Support\Facades\Route::has('petani.produk.index') ? route('petani.produk.index') : url('/petani/produk')) }}" class="mt-3 inline-flex min-h-12 items-center justify-center rounded-xl bg-[#F4B400] px-5 font-bold text-[#1A2A00] transition hover:bg-[#E6A800]">Kelola Produk</a>
                </div>

                <div x-show="sudahKedaluwarsa(pilih)" class="mt-7 rounded-2xl border border-gray-200 bg-gray-50 p-5">
                    <p class="font-bold text-gray-700">Produk tidak tersedia</p>
                    <p class="mt-1 text-sm text-gray-500">Produk ini sudah kedaluwarsa.</p>
                </div>

                <div x-show="!pilih.stok && !sudahKedaluwarsa(pilih)" class="mt-7 rounded-2xl border border-red-200 bg-red-50 p-5">
                    <p class="font-bold text-red-600">Stok Habis</p>
                    <p class="mt-1 text-sm text-red-500">Maaf, produk ini sedang tidak tersedia.</p>
                </div>

                <div class="mt-10" x-show="produkLain().length">
                    <h2 class="mb-4 text-lg font-bold text-[#1A2A1A]">Produk lain dari <span x-text="pilih.petani"></span></h2>
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <template x-for="lain in produkLain()" :key="lain.id">
                            <button type="button" @click="buka(lain)" class="overflow-hidden rounded-2xl border border-[#D4E8D0] bg-white text-left transition hover:shadow-md">
                                <div class="relative aspect-square bg-gray-200">
                                    <span x-show="!lain.gambar" class="flex h-full items-center justify-center px-2 text-center text-sm font-bold text-gray-400" x-text="lain.nama"></span>
                                    <img x-show="lain.gambar" :src="lain.gambar" :alt="lain.nama" class="h-full w-full object-cover" x-on:error="lain.gambar = ''">
                                </div>
                                <div class="p-3">
                                    <p class="truncate text-sm font-bold text-[#1A2A1A]" x-text="lain.nama"></p>
                                    <p class="mt-1 text-sm font-bold text-[#2E7D32]" x-text="rupiahKartu(lain.harga) + '/kg'"></p>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- ===== PROFIL TOKO ===== --}}
    <template x-if="toko">
        <div class="pb-16">
            <div class="relative left-1/2 box-border w-screen max-w-[100vw] -translate-x-1/2 bg-white border-b border-gray-200">
                <div class="max-w-4xl mx-auto px-4 py-3 flex items-center gap-4">
                    <button type="button" @click="tutupToko()" class="inline-flex items-center gap-1 py-2 text-sm font-semibold text-green-700 hover:text-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-700 shrink-0">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                <span>Kembali</span>
            </button>
                    <div class="min-w-0 text-left">
                        <p class="font-bold text-sm truncate">Profil Toko</p>
                        <p class="text-xs text-gray-500 truncate" x-text="toko"></p>
                    </div>
                </div>
            </div>

            <div class="relative left-1/2 box-border w-screen max-w-[100vw] -translate-x-1/2 h-32 md:h-40 bg-gradient-to-r from-green-800 to-green-600 overflow-hidden">
                <img x-show="banner" :src="banner" alt="Banner toko" x-on:error="banner = ''" class="w-full h-full object-cover">
            </div>

            <div class="relative left-1/2 box-border w-screen max-w-[100vw] -translate-x-1/2 bg-white border-b border-gray-200">
                <div class="max-w-4xl mx-auto px-4 pb-5">
                    <div class="w-16 h-16 -mt-8 relative rounded-full bg-green-700 border-4 border-white text-white flex items-center justify-center text-2xl font-bold" x-text="inisial(toko)"></div>
                    <div class="flex items-start justify-between mt-2">
                        <div>
                            <h1 class="text-lg font-bold" x-text="toko"></h1>
                            <p class="text-sm text-gray-500" x-text="infoToko().lokasi + ', Bali'"></p>
                        </div>
                        <span x-show="infoToko().terverifikasi" class="bg-green-50 text-green-800 text-xs font-semibold px-3 py-1 rounded-full">✓ Terverifikasi</span>
                    </div>
                    <div class="flex items-center gap-6 border-t border-gray-100 mt-4 pt-4">
                        <div><p class="font-bold" x-text="produkToko().length"></p><p class="text-xs text-gray-500">Produk</p></div>
                        <div><p class="font-bold" x-text="infoToko().transaksi"></p><p class="text-xs text-gray-500">Transaksi</p></div>
                        <div class="flex items-center gap-2">
                            <span class="text-yellow-500" x-text="bintang(infoToko().rating)"></span>
                            <div><p class="font-bold" x-text="infoToko().rating"></p><p class="text-xs text-gray-500">Rating</p></div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a x-show="toko === namaSaya" href="{{ url('/petani/produk') }}" class="inline-block bg-yellow-400 hover:bg-yellow-500 rounded-xl px-5 py-3 text-sm font-semibold">Kelola Produk</a>
                    </div>
                </div>
            </div>

            <div class="max-w-4xl mx-auto px-4 py-5">
                <h2 class="font-semibold mb-3">Produk dari <span x-text="toko"></span></h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <template x-for="p in produkToko()" :key="p.id">
                        <div @click="buka(p)" class="flex h-full cursor-pointer flex-col overflow-hidden rounded-xl border border-[#D4E8D0] bg-white transition hover:shadow-md">
                            <div class="relative flex aspect-[5/3] w-full items-center justify-center bg-[#DDDDDD]">
                                <span x-show="!p.gambar && !sudahKedaluwarsa(p) && p.stok" class="px-2 text-center text-lg leading-tight font-semibold break-words text-[#A3A3A3]" x-text="p.nama"></span>
                                <img x-show="p.gambar" :src="p.gambar" :alt="p.nama" x-on:error="p.gambar = ''" class="absolute inset-0 h-full w-full object-cover" :class="(sudahKedaluwarsa(p) || !p.stok) && 'grayscale'">
                                <div class="absolute top-3 left-3 z-20 flex flex-col items-start gap-1">
                                    <template x-for="l in p.label" :key="l">
                                        <span class="inline-flex items-center rounded-full bg-[#2E7D32] px-2.5 py-1 text-xs font-bold text-white" x-text="l"></span>
                                    </template>
                                    <span x-show="p.nego" class="inline-flex items-center rounded-full bg-[#F4B400] px-2.5 py-1 text-xs font-bold text-[#1A2A00]">Bisa Nego</span>
                                    <span x-show="p.segeraHabis && !sudahKedaluwarsa(p)" x-cloak class="inline-flex items-center gap-1 rounded-full bg-[#E53935] px-2.5 py-1 text-xs font-bold whitespace-nowrap text-white"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></svg> Segera Habis</span>
                                </div>
                                <div x-show="sudahKedaluwarsa(p)" class="absolute inset-0 z-10 flex items-center justify-center bg-black/20">
                                    <span class="rounded-full bg-black/70 px-5 py-2.5 text-center text-sm font-bold text-white md:text-base">Produk Tidak Tersedia</span>
                                </div>
                                <div x-show="!p.stok && !sudahKedaluwarsa(p)" class="absolute inset-0 z-10 flex items-center justify-center bg-black/20">
                                    <span class="rounded-full bg-black/70 px-5 py-2.5 text-center text-sm font-bold text-white md:text-base">Stok Habis</span>
                                </div>
                            </div>
                            <div class="flex flex-1 flex-col p-3">
                                <span class="inline-block self-start rounded-full bg-[#EBF5EB] px-2.5 py-0.5 text-xs font-medium text-[#2E7D32]" x-text="p.kategori"></span>
                                <p class="mt-1.5 text-base leading-snug font-semibold" :class="sudahKedaluwarsa(p) ? 'text-gray-600' : 'text-[#1A2A1A]'" x-text="p.nama"></p>
                                <p class="mt-0.5"><span class="text-lg font-bold" :class="sudahKedaluwarsa(p) ? 'text-gray-400' : 'text-[#2E7D32]'" x-text="rupiahKartu(p.harga)"></span><span class="text-sm font-normal" :class="sudahKedaluwarsa(p) ? 'text-gray-400' : 'text-[#5A7A5A]'"> /kg</span></p>
                                <p class="text-sm" :class="sudahKedaluwarsa(p) ? 'text-gray-400' : 'text-[#5A7A5A]'">Minimal pesan <span x-text="p.minimal"></span> kg</p>
                                <div x-show="p.tersediaSampai" x-cloak class="mt-2 flex w-full items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold sm:text-sm"
                                    :class="sudahKedaluwarsa(p) ? 'bg-gray-100 text-gray-500' : (p.sisaHari <= 3 ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-700')">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></svg>
                                    <span x-text="p.labelKartuTersedia"></span>
                                </div>
                                <div class="mt-auto border-t border-gray-100 pt-2">
                                    <p class="text-sm" :class="sudahKedaluwarsa(p) ? 'text-gray-600' : 'text-[#435343]'" x-text="p.petani"></p>
                                    <p x-show="p.lokasi" class="mt-0.5 flex items-center gap-1 text-sm" :class="sudahKedaluwarsa(p) ? 'text-gray-400' : 'text-[#5A7A5A]'"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="shrink-0" :class="sudahKedaluwarsa(p) ? 'text-gray-400' : 'text-[#E91E63]'"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/></svg><span x-text="p.lokasi"></span></p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>
</div>
@endif
@endsection