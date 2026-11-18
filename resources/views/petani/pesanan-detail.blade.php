@extends('layouts.petani')

@section('content')
@include('petani.partials.hitung-mundur')
@php
    // Warna & ikon per status (sama dengan petani/pesanan.blade.php).
    $gayaStatus = [
        'menunggu_konfirmasi' => ['ikon' => '⏳', 'kepala' => 'bg-yellow-50 border-yellow-200', 'label' => 'text-yellow-800', 'kotak' => 'bg-yellow-100'],
        'menunggu_pembayaran' => ['ikon' => '💳', 'kepala' => 'bg-orange-50 border-orange-200', 'label' => 'text-orange-800', 'kotak' => 'bg-orange-100'],
        'diproses' => ['ikon' => '📦', 'kepala' => 'bg-blue-50 border-blue-200', 'label' => 'text-blue-800', 'kotak' => 'bg-blue-100'],
        'dikirim' => ['ikon' => '🚚', 'kepala' => 'bg-green-50 border-green-200', 'label' => 'text-green-800', 'kotak' => 'bg-green-100'],
        'siap_diambil' => ['ikon' => '🚚', 'kepala' => 'bg-green-50 border-green-200', 'label' => 'text-green-800', 'kotak' => 'bg-green-100'],
        'terkirim' => ['ikon' => '📬', 'kepala' => 'bg-teal-50 border-teal-200', 'label' => 'text-teal-800', 'kotak' => 'bg-teal-100'],
        'selesai' => ['ikon' => '🎉', 'kepala' => 'bg-emerald-50 border-emerald-200', 'label' => 'text-emerald-800', 'kotak' => 'bg-emerald-100'],
        'dibatalkan' => ['ikon' => '✕', 'kepala' => 'bg-red-50 border-red-200', 'label' => 'text-red-800', 'kotak' => 'bg-red-100 text-red-700'],
        'ditolak' => ['ikon' => '✕', 'kepala' => 'bg-red-50 border-red-200', 'label' => 'text-red-800', 'kotak' => 'bg-red-100 text-red-700'],
    ];
    $g = $gayaStatus[$o['statusDb']] ?? $gayaStatus['dibatalkan'];
    $rp = fn ($n) => 'Rp '.number_format((int) $n, 0, ',', '.');
    $status = $o['statusDb'];
    $bukaTolak = $errors->has('alasan');
@endphp

<div class="mx-auto w-full max-w-xl py-4" x-data="{
    tolak: @js($bukaTolak),
    pesan: @js(session('sukses') ?? session('gagal') ?? (($errors->any() && ! $errors->has('alasan')) ? $errors->first() : '')),
    timer: null,
    init() { if (this.pesan) this.kabar(this.pesan); },
    kabar(teks) {
        clearTimeout(this.timer);
        this.pesan = teks;
        this.timer = setTimeout(() => this.pesan = '', 3500);
    }
}" @keydown.escape.window="tolak = false">

    {{-- Bar atas --}}
    <header class="mb-5 grid min-h-12 grid-cols-[5rem_minmax(0,1fr)_5rem] items-center gap-2 border-b border-green-100 pb-3">
        <a href="{{ route('petani.pesanan') }}" class="inline-flex items-center gap-1 justify-self-start py-2 text-sm font-semibold text-green-700 hover:text-green-800">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0"><path d="m15 18-6-6 6-6" /></svg>
            <span>Kembali</span>
        </a>
        <div class="min-w-0 text-center">
            <h1 class="text-sm font-bold">Detail Pesanan</h1>
            <p class="mt-0.5 truncate text-xs text-gray-500">#{{ $o['nomor'] }}</p>
        </div>
        <span aria-hidden="true"></span>
    </header>

    <div class="space-y-4">
        {{-- Hero status --}}
        <section class="flex items-center gap-4 rounded-2xl border p-5 {{ $g['kepala'] }}" data-hero="{{ $status }}">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl text-3xl font-bold {{ $g['kotak'] }}" aria-hidden="true">{{ $g['ikon'] }}</div>
            <div class="min-w-0">
                <h2 class="text-base font-bold {{ $g['label'] }}">{{ $o['label'] }}</h2>
                <p class="mt-1 text-xs text-gray-700">Dipesan {{ $o['dipesan'] }}</p>
                <p class="mt-0.5 text-xs text-gray-700">Anda sebagai penjual</p>
            </div>
        </section>

        {{-- Tenggat (sekali saja) --}}
        @if ($o['tenggat'])
            <section class="rounded-xl border border-orange-200 bg-white p-4" data-tenggat>
                <p class="inline-flex items-center gap-1.5 rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-700"
                    x-data="hitungMundur(@js($o['tenggat']['batasIso']), @js($o['tenggat']['sisa']))"
                    title="Batas: {{ $o['tenggat']['batasWita'] }}">
                    <span aria-hidden="true">⏱</span>
                    <span>{{ $o['tenggat']['label'] }}: <span x-text="sisa">{{ $o['tenggat']['sisa'] }}</span></span>
                </p>
                <p class="mt-2 text-xs text-gray-500">Lewat tenggat = dibatalkan otomatis.</p>
            </section>
        @endif

        {{-- Aksi per status --}}
        @if ($status === 'menunggu_konfirmasi')
            <div class="space-y-2">
                <form method="POST" action="{{ route('petani.pesanan.terima', $o['id']) }}"
                    onsubmit="return confirm('Terima pesanan #{{ $o['nomor'] }}?')">
                    @csrf
                    <button type="submit" class="w-full rounded-xl bg-green-700 py-3 text-sm font-bold text-white hover:bg-green-800">Terima Pesanan</button>
                </form>
                <button type="button" @click="tolak = true; $nextTick(() => $refs.alasan.focus())"
                    class="w-full rounded-xl border border-red-400 bg-white py-3 text-sm font-semibold text-red-600 hover:bg-red-50">
                    Tolak Pesanan
                </button>
            </div>
        @elseif ($status === 'menunggu_pembayaran')
            <p class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">Menunggu pembeli membayar.</p>
        @elseif ($status === 'diproses')
            <form method="POST" action="{{ route('petani.pesanan.kirim', $o['id']) }}"
                onsubmit="return confirm('{{ $o['ambilSendiri'] ? 'Tandai pesanan siap diambil pembeli?' : 'Tandai pesanan siap dikirim?' }}')">
                @csrf
                <button type="submit" class="w-full rounded-xl bg-yellow-400 py-3 text-sm font-bold text-gray-900 hover:bg-yellow-500">
                    {{ $o['ambilSendiri'] ? 'Siap Diambil' : 'Siap Dikirim' }}
                </button>
            </form>
        @elseif ($status === 'dikirim' || $status === 'siap_diambil')
            <form method="POST" action="{{ route('petani.pesanan.terkirim', $o['id']) }}"
                onsubmit="return confirm('{{ $status === 'siap_diambil' ? 'Tandai pesanan sudah diambil pembeli?' : 'Tandai pesanan sudah terkirim?' }}')">
                @csrf
                <button type="submit" class="w-full rounded-xl bg-yellow-400 py-3 text-sm font-bold text-gray-900 hover:bg-yellow-500">
                    {{ $status === 'siap_diambil' ? 'Tandai Sudah Diambil' : 'Tandai Terkirim' }}
                </button>
            </form>
        @elseif ($status === 'terkirim')
            <p class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">Menunggu konfirmasi dan penilaian dari pembeli.</p>
        @elseif ($status === 'selesai')
            @if ($o['ulasan'])
                <section class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-900">
                    <p class="font-semibold">Penilaian pembeli</p>
                    <p class="mt-1 text-lg text-yellow-500" aria-label="Rating {{ $o['ulasan']['rating'] }} dari 5">
                        {{ str_repeat('★', max(0, min(5, $o['ulasan']['rating']))) }}<span class="text-gray-300">{{ str_repeat('★', 5 - max(0, min(5, $o['ulasan']['rating']))) }}</span>
                        <span class="ml-1 text-xs font-semibold text-gray-700">{{ $o['ulasan']['rating'] }}/5</span>
                    </p>
                    @if ($o['ulasan']['komentar'])
                        <p class="mt-1 text-xs text-gray-700">“{{ $o['ulasan']['komentar'] }}”</p>
                    @endif
                </section>
            @else
                <p class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">Menunggu penilaian dari pembeli.</p>
            @endif
        @elseif ($o['alasanBatal'])
            <p class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <span class="font-semibold">{{ $status === 'ditolak' ? 'Pesanan ditolak.' : 'Pesanan dibatalkan.' }}</span> {{ $o['alasanBatal'] }}
            </p>
        @endif

        {{-- Perjalanan Pesanan --}}
        <section class="rounded-xl border border-green-200 bg-white p-4">
            <h2 class="mb-4 text-sm font-bold">Perjalanan Pesanan</h2>
            <ol>
                @foreach ($o['langkah'] as $l)
                    <li class="relative flex gap-3 pb-5 last:pb-0" data-langkah="{{ $l['keadaan'] }}">
                        @unless ($loop->last)
                            <span class="absolute bottom-0 left-3 top-6 w-px {{ $l['keadaan'] === 'selesai' ? 'bg-green-300' : 'bg-gray-200' }}" aria-hidden="true"></span>
                        @endunless
                        @if ($l['keadaan'] === 'selesai')
                            <span class="relative flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-green-600 text-xs font-bold text-white" aria-hidden="true">✓</span>
                        @elseif ($l['keadaan'] === 'aktif')
                            <span class="relative flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-yellow-400 text-xs font-bold text-gray-900" aria-hidden="true">{{ $l['nomor'] }}</span>
                        @elseif ($l['keadaan'] === 'batal')
                            <span class="relative flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white" aria-hidden="true">✕</span>
                        @else
                            <span class="relative flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-400" aria-hidden="true">{{ $l['nomor'] }}</span>
                        @endif
                        <div class="min-w-0 pt-0.5">
                            <p class="text-xs {{ match ($l['keadaan']) { 'selesai' => 'font-semibold text-green-700', 'aktif' => 'font-bold text-gray-900', 'batal' => 'font-bold text-red-700', default => 'font-medium text-gray-400' } }}">{{ $l['nama'] }}</p>
                            <p class="mt-1 break-words text-[11px] {{ $l['keadaan'] === 'belum' ? 'text-gray-400' : 'text-gray-500' }}">{{ $l['keterangan'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
            <p class="mt-4 border-t border-gray-100 pt-3 text-[11px] text-gray-400">Waktu dalam WITA.</p>
        </section>

        {{-- Produk --}}
        <section class="rounded-xl border border-green-200 bg-white p-4">
            <h2 class="mb-3 text-sm font-bold">Produk Dipesan</h2>
            <div class="space-y-4">
                @foreach ($o['items'] as $item)
                    <div class="flex items-center gap-3">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-green-50">
                            @if ($item['gambar'])
                                <img src="{{ $item['gambar'] }}" alt="{{ $item['nama'] }}" class="h-full w-full object-cover">
                            @else
                                <span class="text-2xl" aria-hidden="true">🥬</span>
                            @endif
                        </div>
                        <div class="min-w-0 text-xs">
                            <p class="break-words text-sm font-semibold">{{ $item['nama'] }}</p>
                            <p class="mt-1 text-gray-500">{{ $item['jumlah'] }} × {{ $rp($item['harga']) }}</p>
                            <p class="mt-1 font-semibold text-green-700">{{ $rp($item['subtotal']) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Pengiriman --}}
        <section class="rounded-xl border border-green-200 bg-white p-4 text-xs">
            <h2 class="text-sm font-bold">Pengiriman</h2>
            <p class="mt-2 font-medium">{{ $o['metodePengiriman'] ?? '-' }}</p>
            <p class="mt-2 font-medium">{{ $o['penerima'] }}@if ($o['telepon']) · {{ $o['telepon'] }}@endif</p>
            <p class="mt-1 text-gray-500">{{ $o['alamat'] }}</p>
            @if ($o['catatan'])
                <p class="mt-2 text-gray-500">Catatan: {{ $o['catatan'] }}</p>
            @endif
        </section>

        {{-- Pembayaran --}}
        <section class="rounded-xl border border-green-200 bg-white p-4 text-xs">
            <h2 class="text-sm font-bold">Metode Pembayaran</h2>
            <p class="mt-2 font-medium">{{ $o['metodeBayar'] ?? '-' }}</p>
            @if ($o['buktiBayar'])
                <p class="mt-2 font-semibold text-green-700">Bukti pembayaran sudah dikirim ✓</p>
            @endif
        </section>

        {{-- Rincian biaya --}}
        <section class="rounded-xl border border-green-200 bg-white p-4 text-xs">
            <h2 class="mb-3 text-sm font-bold">Rincian Biaya</h2>
            <dl class="space-y-3">
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Subtotal produk</dt><dd>{{ $rp($o['subtotalProduk']) }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Ongkos kirim</dt><dd>{{ $o['ongkir'] === 0 ? 'Gratis' : $rp($o['ongkir']) }}</dd></div>
                @if ($o['biayaLayanan'] > 0)
                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Biaya layanan</dt><dd>{{ $rp($o['biayaLayanan']) }}</dd></div>
                @endif
                <div class="flex justify-between gap-3 border-t border-gray-100 pt-3 text-sm font-bold text-green-700"><dt>Total</dt><dd>{{ $rp($o['total']) }}</dd></div>
            </dl>
        </section>

        {{-- Pembeli --}}
        <section class="rounded-xl border border-green-200 bg-white p-4">
            <h2 class="text-sm font-bold">Pembeli</h2>
            <p class="mt-2 text-sm font-medium">{{ $o['pembeli'] }}</p>
            <form method="POST" action="{{ route('petani.pesanan.hubungi', $o['id']) }}" class="mt-3">
                @csrf
                <button type="submit" class="block w-full rounded-xl border border-green-700 py-2.5 text-center text-sm font-semibold text-green-700 hover:bg-green-50">Hubungi Pembeli</button>
            </form>
        </section>

        <a href="{{ route('petani.pesanan') }}" class="block w-full rounded-xl border border-gray-300 bg-white py-3 text-center text-sm font-semibold text-gray-700 hover:bg-gray-50">Kembali ke Pesanan</a>
    </div>

    {{-- Modal tolak pesanan (alasan wajib) --}}
    @if ($status === 'menunggu_konfirmasi')
        <div x-show="tolak" x-cloak class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-4 sm:items-center" @click.self="tolak = false" role="dialog" aria-modal="true" aria-labelledby="judul-tolak">
            <form method="POST" action="{{ route('petani.pesanan.tolak', $o['id']) }}" class="w-full max-w-md rounded-2xl bg-white p-5 shadow-xl">
                @csrf
                <h2 id="judul-tolak" class="text-base font-bold">Tolak Pesanan #{{ $o['nomor'] }}</h2>
                <label for="alasan" class="mt-3 block text-xs font-semibold text-gray-700">Alasan penolakan <span class="text-red-600">*</span></label>
                <textarea id="alasan" name="alasan" x-ref="alasan" rows="3" maxlength="200" required minlength="3"
                    class="mt-1 w-full rounded-xl border {{ $errors->has('alasan') ? 'border-red-500' : 'border-gray-300' }} p-3 text-sm focus:border-green-600 focus:outline-none"
                    placeholder="Contoh: Stok habis karena gagal panen">{{ old('alasan') }}</textarea>
                @error('alasan')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-[11px] text-gray-500">Alasan ini dicatat di riwayat pesanan dan terlihat oleh pembeli.</p>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <button type="button" @click="tolak = false" class="rounded-xl border border-gray-300 py-2.5 text-sm font-semibold text-gray-700">Batal</button>
                    <button type="submit" class="rounded-xl bg-red-600 py-2.5 text-sm font-bold text-white hover:bg-red-700">Tolak Pesanan</button>
                </div>
            </form>
        </div>
    @endif

    <div x-show="pesan" x-cloak x-transition role="status" aria-live="polite"
        class="fixed bottom-20 left-1/2 z-50 w-max max-w-[90vw] -translate-x-1/2 rounded-xl bg-gray-900 px-4 py-3 text-sm text-white shadow-lg"
        x-text="pesan">
    </div>
</div>
@endsection
