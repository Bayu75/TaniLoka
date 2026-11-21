@extends('layouts.petani')

@section('content')
@include('petani.partials.hitung-mundur')
@php
    // Warna & ikon per status (ditulis di view supaya kelas Tailwind ikut terbaca saat build).
    $gayaStatus = [
        'menunggu_konfirmasi' => ['ikon' => '⏳', 'kepala' => 'bg-yellow-50 border-yellow-100', 'label' => 'text-yellow-700', 'kotak' => 'bg-yellow-100'],
        'menunggu_pembayaran' => ['ikon' => '💳', 'kepala' => 'bg-orange-50 border-orange-100', 'label' => 'text-orange-700', 'kotak' => 'bg-orange-100'],
        'diproses' => ['ikon' => '📦', 'kepala' => 'bg-blue-50 border-blue-100', 'label' => 'text-blue-700', 'kotak' => 'bg-blue-100'],
        'dikirim' => ['ikon' => '🚚', 'kepala' => 'bg-green-50 border-green-100', 'label' => 'text-green-700', 'kotak' => 'bg-green-100'],
        'siap_diambil' => ['ikon' => '🚚', 'kepala' => 'bg-green-50 border-green-100', 'label' => 'text-green-700', 'kotak' => 'bg-green-100'],
        'terkirim' => ['ikon' => '📬', 'kepala' => 'bg-teal-50 border-teal-100', 'label' => 'text-teal-700', 'kotak' => 'bg-teal-100'],
        'selesai' => ['ikon' => '🎉', 'kepala' => 'bg-emerald-50 border-emerald-100', 'label' => 'text-emerald-700', 'kotak' => 'bg-emerald-100'],
        'dibatalkan' => ['ikon' => '✕', 'kepala' => 'bg-red-50 border-red-100', 'label' => 'text-red-700', 'kotak' => 'bg-red-100 text-red-700'],
        'ditolak' => ['ikon' => '✕', 'kepala' => 'bg-red-50 border-red-100', 'label' => 'text-red-700', 'kotak' => 'bg-red-100 text-red-700'],
    ];
    $tabIds = array_column($tabs, 'id');
@endphp

<div class="mx-auto w-full max-w-4xl pb-4" x-data="{
    tab: @js($tabAwal),
    tabIds: @js($tabIds),
    pesan: @js(session('sukses') ?? session('gagal') ?? ($errors->any() ? $errors->first() : '')),
    timer: null,
    init() {
        const hash = window.location.hash.slice(1);
        if (this.tabIds.includes(hash)) this.tab = hash;
        if (this.pesan) this.kabar(this.pesan);
    },
    pilih(id) {
        this.tab = id;
        history.replaceState(null, '', id === 'semua' ? window.location.pathname : '#' + id);
    },
    kabar(teks) {
        clearTimeout(this.timer);
        this.pesan = teks;
        this.timer = setTimeout(() => this.pesan = '', 3500);
    }
}" @hashchange.window="const h = window.location.hash.slice(1); tab = tabIds.includes(h) ? h : 'semua'">

    <x-page-header judul="Pesanan masuk" :kembali="false" lebar="max-w-4xl"/>

    {{-- Tab filter (tidak ada tab "Menunggu Verifikasi Pembayaran") --}}
    <div class="-mx-1 mb-1 flex gap-2 overflow-x-auto px-1 py-4" role="tablist" aria-label="Filter pesanan">
        @foreach ($tabs as $t)
            <button type="button" role="tab" @click="pilih('{{ $t['id'] }}')"
                :aria-selected="tab === '{{ $t['id'] }}'"
                :class="tab === '{{ $t['id'] }}' ? 'bg-green-700 border-green-700 text-white' : 'bg-white border-green-200 text-gray-800 hover:border-green-600'"
                class="shrink-0 whitespace-nowrap rounded-full border px-4 py-2 text-xs font-semibold"
                data-tab="{{ $t['id'] }}">
                {{ $t['nama'] }} ({{ $t['jumlah'] }})
            </button>
        @endforeach
    </div>

    <div class="space-y-4">
        @foreach ($orders as $o)
            @php $g = $gayaStatus[$o['statusDb']] ?? $gayaStatus['dibatalkan']; @endphp
            <article x-show="tab === 'semua' || tab === '{{ $o['tab'] }}'"
                class="overflow-hidden rounded-2xl border border-green-200 bg-white shadow-sm"
                data-status="{{ $o['statusDb'] }}">
                <header class="flex items-center gap-3 border-b p-4 {{ $g['kepala'] }}">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-2xl font-bold {{ $g['kotak'] }}" aria-hidden="true">{{ $g['ikon'] }}</div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold {{ $g['label'] }}">{{ $o['label'] }}</p>
                        <h2 class="mt-0.5 truncate text-sm font-bold text-gray-900">{{ $o['pembeli'] }}</h2>
                        <p class="mt-0.5 text-xs text-gray-600">#{{ $o['nomor'] }} · {{ $o['tanggal'] }}</p>
                    </div>
                </header>

                <div class="space-y-3 p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-green-50">
                            @if ($o['gambar'])
                                <img src="{{ $o['gambar'] }}" alt="{{ $o['produk'] }}" class="h-full w-full object-cover" loading="lazy">
                            @else
                                <span class="text-xl" aria-hidden="true">🥬</span>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold">{{ $o['produk'] }}</p>
                            <p class="mt-0.5 text-xs text-gray-500">
                                {{ $o['jumlah'] }}@if ($o['produkLain'] > 0) · +{{ $o['produkLain'] }} produk lain @endif
                            </p>
                        </div>
                    </div>

                    <p class="text-sm font-bold">Total: <span class="text-green-700">Rp {{ number_format($o['total'], 0, ',', '.') }}</span></p>

                    @if ($o['tenggat'])
                        <p class="inline-flex items-center gap-1.5 rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-700"
                            x-data="hitungMundur(@js($o['tenggat']['batasIso']), @js($o['tenggat']['sisa']))"
                            title="Batas: {{ $o['tenggat']['batasWita'] }}">
                            <span aria-hidden="true">⏱</span>
                            <span>{{ $o['tenggat']['label'] }}: <span x-text="sisa">{{ $o['tenggat']['sisa'] }}</span></span>
                        </p>
                    @endif

                    @if ($o['tindakan'])
                        <p class="rounded-lg bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800">Perlu tindakan: {{ $o['tindakan'] }}</p>
                    @endif

                    @if ($o['alasanBatal'])
                        <p class="rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700"><span class="font-semibold">Alasan:</span> {{ $o['alasanBatal'] }}</p>
                    @endif

                    <a href="{{ $o['url'] }}" aria-label="Lihat detail pesanan {{ $o['nomor'] }}"
                        class="block w-full rounded-xl bg-green-700 py-3 text-center text-sm font-bold text-white hover:bg-green-800">
                        Lihat Detail
                    </a>
                </div>
            </article>
        @endforeach
    </div>

    @foreach ($tabs as $t)
        @if ($t['jumlah'] === 0)
            <div x-show="tab === '{{ $t['id'] }}'" x-cloak class="rounded-xl border border-green-200 bg-white p-8 text-center text-sm text-gray-500">
                {{ $t['id'] === 'semua' ? 'Belum ada pesanan masuk.' : 'Belum ada pesanan pada status ini.' }}
            </div>
        @endif
    @endforeach

    <div x-show="pesan" x-cloak x-transition role="status" aria-live="polite"
        class="fixed bottom-20 left-1/2 z-50 w-max max-w-[90vw] -translate-x-1/2 rounded-xl bg-gray-900 px-4 py-3 text-sm text-white shadow-lg"
        x-text="pesan">
    </div>
</div>

@endsection
