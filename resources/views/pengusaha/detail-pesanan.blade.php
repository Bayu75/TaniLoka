@extends('layouts.pengusaha')

@section('title', 'Detail Pesanan - TaniLoka')

@section('content')

@php
    $status = $pesanan->status;

    $statusInfo = [
        'menunggu_konfirmasi' => [
            'label' => 'Menunggu Konfirmasi',
            'deskripsi' => 'Pesanan sedang menunggu konfirmasi dari petani.',
            'warna' => 'yellow',
            'ikon' => '⏳',
        ],
        'menunggu_pembayaran' => [
            'label' => 'Menunggu Pembayaran',
            'deskripsi' => 'Petani telah menerima pesanan. Silakan lakukan pembayaran.',
            'warna' => 'orange',
            'ikon' => '💳',
        ],
        'diproses' => [
            'label' => 'Diproses',
            'deskripsi' => 'Pembayaran telah diterima dan pesanan sedang disiapkan.',
            'warna' => 'blue',
            'ikon' => '📦',
        ],
        'dikirim' => [
            'label' => 'Sedang Dikirim',
            'deskripsi' => 'Pesanan sedang dalam perjalanan menuju alamat Anda.',
            'warna' => 'blue',
            'ikon' => '🚚',
        ],
        'siap_diambil' => [
            'label' => 'Siap Diambil',
            'deskripsi' => 'Pesanan sudah siap untuk diambil.',
            'warna' => 'green',
            'ikon' => '🛍️',
        ],
        'terkirim' => [
            'label' => 'Terkirim',
            'deskripsi' => 'Pesanan telah sampai. Silakan konfirmasi penerimaan.',
            'warna' => 'green',
            'ikon' => '📍',
        ],
        'selesai' => [
            'label' => 'Selesai',
            'deskripsi' => 'Transaksi pesanan telah selesai.',
            'warna' => 'green',
            'ikon' => '🎉',
        ],
        'dibatalkan' => [
            'label' => 'Dibatalkan',
            'deskripsi' => 'Pesanan telah dibatalkan.',
            'warna' => 'red',
            'ikon' => '❌',
        ],
        'ditolak' => [
            'label' => 'Ditolak',
            'deskripsi' => 'Pesanan ditolak oleh petani.',
            'warna' => 'red',
            'ikon' => '❌',
        ],
    ];

    $info = $statusInfo[$status] ?? $statusInfo['menunggu_konfirmasi'];
        
    $warnaBorder = [
        'yellow' => 'border-yellow-200 bg-yellow-50 text-yellow-800',
        'orange' => 'border-orange-200 bg-orange-50 text-orange-800',
        'blue' => 'border-blue-200 bg-blue-50 text-blue-800',
        'green' => 'border-green-200 bg-green-50 text-green-800',
        'red' => 'border-red-200 bg-red-50 text-red-800',
    ][$info['warna']];

    $metodePembayaran = [
        'bank_transfer' => 'Transfer Bank (Virtual Account)',
        'echannel' => 'Mandiri Bill',
        'gopay' => 'GoPay',
        'shopeepay' => 'ShopeePay',
        'qris' => 'QRIS',
        'credit_card' => 'Kartu Kredit/Debit',
        'cstore' => 'Minimarket',
        'akulaku' => 'Akulaku',
        'kredivo' => 'Kredivo',
        // nilai lama sebelum Midtrans
        'transfer_bank' => 'Transfer Bank',
        'ovo' => 'OVO',
    ];

    $metode = $pesanan->pembayaran?->metode;

    $namaPetani = $pesanan->petani?->profilPetani?->nama_usaha_tani
        ?: ($pesanan->petani?->name ?? 'Petani');

    $riwayat = $pesanan->riwayat
        ->sortBy('created_at');

    $bukti = $pesanan->pembayaran?->bukti_path;

    $deadlinePembayaran = $pesanan->created_at?->copy()->addHours(24);

    $sisaWaktuPembayaran = $deadlinePembayaran
        ? max(0, (int) floor(now()->diffInSeconds($deadlinePembayaran, false)))
        : 0;

    $pembayaranKadaluarsa = $status === 'menunggu_pembayaran'
        && $sisaWaktuPembayaran <= 0;
@endphp

<div class="mx-auto flex w-full max-w-[760px] flex-col gap-5">

    {{-- Judul --}}
    <x-page-header
    judul="Detail Pesanan"
    :kembali="route('pengusaha.pesanan')"
    lebar="max-w-[760px]"
/>

    {{-- Notifikasi Status --}}
    <div class="rounded-2xl border p-5 {{ $warnaBorder }}">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-2xl">
                {{ $info['ikon'] }}
            </div>

            <div>
                {{-- Judul Status --}}
                <h2 class="font-bold">
                    {{ $info['label'] }}
                </h2>

                {{-- Kode Pesanan --}}
                <p class="mt-1 text-sm font-medium text-gray-500">
                    #{{ $pesanan->kode }}
                </p>

                {{-- Deskripsi Status --}}
                <p class="mt-1 text-sm">
                    {{ $info['deskripsi'] }}
                </p>
            </div>
        </div>
    </div>

    {{-- Petunjuk Tindakan --}}
    @if ($status === 'menunggu_konfirmasi')
        <div class="rounded-2xl border border-yellow-200 bg-white p-4">
            <p class="text-sm font-semibold text-yellow-800">
                Menunggu petani mengonfirmasi pesanan Anda.
            </p>
            <p class="mt-2 text-sm text-gray-600">
                Pesanan akan diproses setelah petani menerima pesanan.
            </p>
        </div>

    @elseif ($status === 'menunggu_pembayaran')
        <div
            class="rounded-2xl border border-orange-200 bg-white p-4"
            x-data="{
                sisa: {{ $sisaWaktuPembayaran }},
                sudahUpload: {{ $pesanan->pembayaran?->status === 'lunas' ? 'true' : 'false' }},
                timer: null,

                mulai() {
                    if (this.sudahUpload) return;

                    this.timer = setInterval(() => {
                        if (this.sisa > 0) {
                            this.sisa--;
                        } else {
                            clearInterval(this.timer);
                        }
                    }, 1000);
                },

                formatWaktu() {
                    const jam = Math.floor(this.sisa / 3600);
                    const menit = Math.floor((this.sisa % 3600) / 60);
                    const detik = Math.floor(this.sisa % 60);

                    return [jam, menit, detik]
                        .map(n => String(n).padStart(2, '0'))
                        .join(':');
                }
            }"
            x-init="mulai()"
            x-cloak
        >
            <p class="text-sm font-semibold text-orange-800">
                Silakan lakukan pembayaran lewat tombol Bayar Sekarang di bawah.
            </p>

            <div class="mt-4 rounded-xl bg-orange-50 p-4">
                <p class="text-xs font-medium text-orange-700">
                    Batas waktu pembayaran
                </p>

                <p
                    class="mt-1 text-2xl font-extrabold tabular-nums text-orange-700"
                    x-text="formatWaktu()"
                ></p>

                <p
                    class="mt-2 text-xs text-orange-700"
                    x-show="sisa > 0"
                >
                    Segera selesaikan pembayaran sebelum waktu habis.
                </p>

                <p
                    class="mt-2 text-xs font-semibold text-red-600"
                    x-show="sisa <= 0"
                >
                    Batas waktu pembayaran telah berakhir.
                </p>
            </div>
        </div>

    @elseif ($status === 'terkirim')
        <div class="rounded-2xl border border-green-200 bg-white p-4">
            <p class="text-sm font-semibold text-green-800">
                Pesanan sudah sampai. Silakan konfirmasi penerimaan.
            </p>
        </div>
    @endif

    {{-- Pembatalan --}}
    @if (in_array($status, ['menunggu_konfirmasi', 'menunggu_pembayaran']))
        <div x-data="{ konfirmasiBatal: false }">

            <button
                type="button"
                x-show="!konfirmasiBatal"
                @click="konfirmasiBatal = true"
                class="w-full rounded-xl border border-red-300 bg-white px-4 py-3 text-sm font-semibold text-red-600 hover:bg-red-50">
                Batalkan Pesanan
            </button>

            <div
                x-show="konfirmasiBatal"
                x-transition
                class="rounded-2xl border border-red-200 bg-red-50 p-4">

                <p class="mb-3 font-semibold text-red-700">
                    Yakin ingin membatalkan pesanan ini?
                </p>

                <div class="flex gap-3">

                    <button
                        type="button"
                        @click="konfirmasiBatal = false"
                        class="flex-1 rounded-xl border border-red-400 bg-white px-4 py-3 text-sm font-bold text-red-600">
                        Tidak
                    </button>

                    <form
                        action="{{ route('pengusaha.pesanan.cancel', $pesanan->id) }}"
                        method="POST"
                        class="flex-1">
                        @csrf

                        <button
                            type="submit"
                            class="w-full rounded-xl bg-red-600 px-4 py-3 text-sm font-bold text-white">
                            Ya, Batalkan
                        </button>
                    </form>

                </div>
            </div>
        </div>
    @endif

    {{-- Timeline Perjalanan Pesanan --}}
    @php
        $statusSekarang = $pesanan->status;

        // Menentukan label pengiriman
        $metodePengiriman = strtolower($pesanan->metode_pengiriman ?? '');

        $labelPengiriman = str_contains($metodePengiriman, 'ambil')
            ? 'Siap Diambil'
            : 'Dikirim';

        // Seluruh tahapan pesanan
        $tahapan = [
            [
                'status' => 'menunggu_konfirmasi',
                'label' => 'Menunggu Konfirmasi',
            ],
            [
                'status' => 'menunggu_pembayaran',
                'label' => 'Menunggu Pembayaran',
            ],
            [
                'status' => 'diproses',
                'label' => 'Diproses',
            ],
            [
                'status' => ['dikirim', 'siap_diambil'],
                'label' => $labelPengiriman,
            ],
            [
                'status' => 'selesai',
                'label' => 'Selesai',
            ],
        ];

        $urutanStatus = [
            'menunggu_konfirmasi' => 0,
            'menunggu_pembayaran' => 1,
            'diproses' => 2,
            'dikirim' => 3,
            'siap_diambil' => 3,
            'terkirim' => 3,
            'selesai' => 4,
        ];

        $posisiSekarang = $urutanStatus[$statusSekarang] ?? -1;

        // Mengambil riwayat dari database
        $riwayatTimeline = $pesanan->riwayat->sortBy('created_at');
    @endphp

    <div class="rounded-2xl border border-green-200 bg-white p-5">
        <h2 class="mb-5 text-lg font-bold text-gray-800">
            Perjalanan Pesanan
        </h2>

        @if ($posisiSekarang >= 0)

            <div class="space-y-0">

                @foreach ($tahapan as $index => $tahap)

                    @php
                        // Menentukan tahapan yang sudah selesai dan sedang aktif
                        $statusSelesai = $index < $posisiSekarang
                            || ($statusSekarang === 'selesai' && $index === $posisiSekarang);

                        $statusAktif = $index === $posisiSekarang
                            && $statusSekarang !== 'selesai';

                        // Mencari riwayat berdasarkan tahapan
                        $statusTahap = $tahap['status'];

                        $riwayatItem = $riwayatTimeline->first(
                            function ($item) use ($statusTahap) {
                                return in_array(
                                    $item->status,
                                    (array) $statusTahap
                                );
                            }
                        );
                    @endphp

                    <div class="flex gap-3">

                        {{-- NOMOR DAN GARIS TIMELINE --}}
                        <div class="flex flex-col items-center">

                            <div @class([
                                'z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold',

                                'bg-green-500 text-white ring-4 ring-green-100'
                                    => $statusSelesai,

                                'bg-yellow-400 text-gray-900 ring-4 ring-yellow-100'
                                    => $statusAktif,

                                'bg-gray-200 text-gray-500'
                                    => !$statusSelesai && !$statusAktif,
                            ])>

                                @if ($statusSelesai)
                                    ✓
                                @else
                                    {{ $index + 1 }}
                                @endif

                            </div>

                            @if (!$loop->last)
                                <div @class([
                                    'min-h-[55px] w-0.5 flex-1',

                                    'bg-green-400' => $statusSelesai,
                                    'bg-gray-200' => !$statusSelesai,
                                ])></div>
                            @endif

                        </div>

                        {{-- INFORMASI TAHAPAN --}}
                        <div class="{{ !$loop->last ? 'pb-7' : 'pb-1' }} min-w-0 flex-1">

                            <p @class([
                                'font-bold',

                                'text-gray-900' => $statusSelesai || $statusAktif,

                                'text-gray-500' => !$statusSelesai && !$statusAktif,
                            ])>
                                {{ $tahap['label'] }}
                            </p>

                            @if ($statusSelesai || $statusAktif)

                                <p class="mt-1 text-sm text-green-700">
                                    {{ $riwayatItem?->created_at?->format('d M, H.i') ?? 'Dalam proses' }}
                                </p>

                            @else

                                <p class="mt-1 text-sm text-gray-500">
                                    Belum
                                </p>

                            @endif

                            @if ($statusAktif && $riwayatItem?->keterangan)

                                <p class="mt-1 text-sm text-gray-600">
                                    {{ $riwayatItem->keterangan }}
                                </p>

                            @endif

                                                       @if ($statusAktif && $statusSekarang === 'terkirim')
                                <form
                                    action="{{ route('pengusaha.pesanan.selesai', $pesanan->id) }}"
                                    method="POST"
                                    class="mt-4"
                                    onsubmit="return confirm('Apakah barang sudah kamu terima dan ingin menyelesaikan pesanan ini?')"
                                >
                                    @csrf
                                    <button
                                        type="submit"
                                        class="rounded-xl bg-green-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-green-800"
                                    >
                                        Tandai Pesanan Selesai
                                    </button>
                                </form>
                            @endif


                        </div>

                    </div>

                @endforeach

            </div>

        @else

            <div class="rounded-xl bg-red-50 p-4 text-sm text-red-700">
                @if ($statusSekarang === 'dibatalkan')
                    Pesanan telah dibatalkan.
                @else
                    Pesanan telah ditolak oleh petani.
                @endif
            </div>

        @endif
    </div>

    {{-- Penilaian dan Ulasan --}}
    @if ($status === 'selesai')
        <div class="rounded-2xl border border-green-200 bg-white p-5">
            <h2 class="mb-3 text-lg font-bold text-gray-900">
                Penilaian dan Ulasan
            </h2>

            @if ($pesanan->ulasan)
                <div class="rounded-xl bg-green-50 p-4">
                    <p class="font-semibold text-green-800">
                        Terima kasih atas penilaianmu!
                    </p>

                    <p class="mt-2 text-yellow-500 text-xl">
                        {{ str_repeat('★', $pesanan->ulasan->rating) }}{{ str_repeat('☆', 5 - $pesanan->ulasan->rating) }}
                    </p>

                    @if ($pesanan->ulasan->komentar)
                        <p class="mt-2 text-sm text-gray-700">
                            {{ $pesanan->ulasan->komentar }}
                        </p>
                    @endif
                </div>
            @else
                <p class="mb-4 text-sm text-gray-600">
                    Bagaimana pengalamanmu berbelanja dengan petani ini?
                </p>

                <form
                    action="{{ route('pengusaha.pesanan.ulasan', $pesanan->id) }}"
                    method="POST"
                    class="space-y-4"
                >
                    @csrf

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-800">
                            Berikan Rating
                        </label>

                        <div
                            x-data="{
                                rating: {{ old('rating', 0) }},
                                hoverRating: 0
                            }"
                            class="flex items-center gap-2"
                        >
                            <template x-for="bintang in [1, 2, 3, 4, 5]" :key="bintang">
                                <button
                                    type="button"
                                    @click="rating = bintang"
                                    @mouseenter="hoverRating = bintang"
                                    @mouseleave="hoverRating = 0"
                                    class="text-4xl transition-colors duration-150"
                                    :class="bintang <= (hoverRating || rating)
                                        ? 'text-yellow-400'
                                        : 'text-gray-300'"
                                >★</button>
                            </template>

                            <input type="hidden" name="rating" :value="rating">

                            <span
                                class="ml-2 text-sm font-semibold text-gray-600"
                                x-text="rating ? rating + '/5' : ''"
                            ></span>
                        </div>
                    </div>  

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-800">
                            Ulasan (Opsional)
                        </label>

                        <textarea
                            name="komentar"
                            rows="4"
                            maxlength="1000"
                            placeholder="Ceritakan pengalamanmu..."
                            class="w-full rounded-xl border border-gray-300 p-3 text-sm focus:border-green-600 focus:outline-none"
                        >{{ old('komentar') }}</textarea>

                        @error('komentar')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-green-700 px-4 py-3 font-bold text-white transition hover:bg-green-800"
                    >
                        Kirim Penilaian
                    </button>
                </form>
            @endif
        </div>
    @endif


    {{-- Produk Dipesan --}}
    <div class="rounded-2xl border border-green-200 bg-white p-5">
        <h2 class="mb-4 text-lg font-bold text-gray-900">
            Produk Dipesan
        </h2>

        <div class="space-y-4">
            @foreach ($pesanan->item as $produk)
                <div class="flex items-center gap-3">
                    @php
                        $gambar = $produk->produk?->fotoUtama?->url;
                    @endphp

                    @if ($gambar)
                        <img
                            src="{{ $gambar }}"
                            alt="{{ $produk->nama_produk }}"
                            class="h-16 w-16 shrink-0 rounded-xl object-cover"
                        >
                    @else
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl bg-gray-100">
                            <span class="text-xs text-gray-400">Foto</span>
                        </div>
                    @endif

                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-bold text-gray-900">
                            {{ $produk->nama_produk }}
                        </h3>

                        <p class="mt-1 text-base text-gray-600">
                            {{ number_format((float) $produk->jumlah_kg, 0, ',', '.') }} kg
                            × Rp{{ number_format($produk->harga_per_kg, 0, ',', '.') }}
                        </p>

                        <p class="mt-1 font-bold text-green-700">
                            Rp{{ number_format($produk->subtotal, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>


    {{-- Pembayaran (Midtrans Snap) --}}
    @php
        $statusBayar = $pesanan->pembayaran?->status;
    @endphp

    @if ($status === 'menunggu_pembayaran' || in_array($statusBayar, ['lunas', 'perlu_refund', 'dikembalikan'], true) || $bukti)
        <div class="rounded-2xl border border-green-200 bg-white p-5">
            <h2 class="mb-3 text-lg font-bold text-gray-900">
                Pembayaran
            </h2>

            @if ($statusBayar === 'lunas')
                <div class="rounded-xl bg-green-50 p-4">
                    <p class="text-sm font-semibold text-green-800">
                        Pembayaran diterima ✓
                        @if ($pesanan->pembayaran?->dibayar_pada)
                            pada {{ $pesanan->pembayaran->dibayar_pada->format('d M Y, H.i') }}
                        @endif
                    </p>
                </div>

            @elseif ($statusBayar === 'perlu_refund')
                <div class="rounded-xl bg-orange-50 p-4">
                    <p class="text-sm font-semibold text-orange-800">
                        Pesanan dibatalkan setelah pembayaran. Dana kamu akan dikembalikan oleh admin TaniLoka.
                    </p>
                </div>

            @elseif ($statusBayar === 'dikembalikan')
                <div class="rounded-xl bg-green-50 p-4">
                    <p class="text-sm font-semibold text-green-800">
                        Dana pembayaran sudah dikembalikan.
                    </p>
                </div>

            @elseif ($bukti)
                {{-- Pesanan lama (sebelum Midtrans) yang pernah upload bukti --}}
                @php
                    $buktiUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($bukti);
                @endphp
                <div class="rounded-xl bg-green-50 p-4">
                    <p class="text-sm font-semibold text-green-800">
                        Bukti pembayaran berhasil diunggah ✓
                    </p>
                    <a href="{{ $buktiUrl }}" target="_blank"
                       class="mt-2 inline-block text-sm font-semibold text-green-700 underline">
                        Lihat Bukti Pembayaran
                    </a>
                </div>

            @elseif ($status === 'menunggu_pembayaran')
                <p class="mb-4 text-sm text-gray-600">
                    Bayar dengan transfer bank (VA), QRIS, GoPay, kartu, atau metode lain.
                    Status pesanan otomatis berubah setelah pembayaran diterima.
                </p>

                @if (! \App\Services\Pembayaran\Midtrans::clientKey())
                    <p class="rounded-xl bg-red-50 p-3 text-sm text-red-700">
                        Pembayaran belum dikonfigurasi (MIDTRANS_CLIENT_KEY kosong). Hubungi admin.
                    </p>
                @elseif ($pembayaranKadaluarsa)
                    <p class="rounded-xl bg-red-50 p-3 text-sm text-red-700">
                        Batas waktu pembayaran sudah lewat. Pesanan akan dibatalkan otomatis.
                    </p>
                @else
                    <script
                        src="{{ \App\Services\Pembayaran\Midtrans::urlSnapJs() }}"
                        data-client-key="{{ \App\Services\Pembayaran\Midtrans::clientKey() }}"></script>

                    <div
                        x-data="{
                            memuat: false,
                            pesan: '',
                            csrf: @js(csrf_token()),

                            async kirim(url, body = {}) {
                                const respons = await fetch(url, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': this.csrf,
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify(body),
                                });
                                const data = await respons.json().catch(() => ({}));
                                return { ok: respons.ok, data };
                            },

                            // Webhook tetap sumber kebenaran. Ini hanya mempercepat tampilan.
                            async segarkan() {
                                try { await this.kirim(@js(route('pengusaha.pesanan.bayar.cek', $pesanan->id))); } catch (e) {}
                                window.location.reload();
                            },

                            async bayar(ganti = false) {
                                if (this.memuat) return;
                                this.memuat = true;
                                this.pesan = '';

                                let hasil;
                                try {
                                    hasil = await this.kirim(@js(route('pengusaha.pesanan.bayar', $pesanan->id)), ganti ? { ganti: true } : {});
                                } catch (e) {
                                    this.pesan = 'Koneksi bermasalah. Coba lagi.';
                                    this.memuat = false;
                                    return;
                                }

                                if (!hasil.ok) {
                                    const error = hasil.data.errors ? Object.values(hasil.data.errors).flat()[0] : null;
                                    this.pesan = error || hasil.data.pesan || 'Pembayaran belum bisa dimulai.';
                                    this.memuat = false;
                                    return;
                                }

                                window.snap.pay(hasil.data.token, {
                                    onSuccess: () => this.segarkan(),
                                    onPending: () => this.segarkan(),
                                    onError: () => { this.pesan = 'Pembayaran gagal. Silakan coba lagi.'; this.memuat = false; },
                                    onClose: () => { this.memuat = false; },
                                });
                            },

                            gantiMetode() {
                                if (confirm('Ganti metode pembayaran? Instruksi pembayaran sebelumnya (nomor VA/QR) akan dibatalkan dan tidak bisa dipakai lagi.')) {
                                    this.bayar(true);
                                }
                            },
                        }"
                    >
                        <p class="mb-3 text-sm font-semibold text-red-600" x-show="pesan" x-text="pesan" x-cloak></p>

                        <button
                            type="button"
                            @click="bayar()"
                            :disabled="memuat"
                            class="w-full rounded-xl bg-green-700 px-4 py-3 font-bold text-white transition hover:bg-green-800 disabled:cursor-not-allowed disabled:opacity-60">
                            <span x-show="!memuat">Bayar Sekarang · Rp {{ number_format($pesanan->total, 0, ',', '.') }}</span>
                            @if ($pesanan->pembayaran?->midtrans_order_id)
                            <button
                                type="button"
                                @click="gantiMetode()"
                                :disabled="memuat"
                                class="mt-3 w-full rounded-xl border border-green-700 bg-white px-4 py-3 text-sm font-bold text-green-700 transition hover:bg-green-50 disabled:cursor-not-allowed disabled:opacity-60">
                                Ganti Metode Pembayaran
                            </button>
                        @endif
                            <span x-show="memuat" x-cloak>Memuat pembayaran...</span>
                        </button>
                    </div>

                    @if ($pesanan->pembayaran?->midtrans_order_id)
                        <form
                            action="{{ route('pengusaha.pesanan.bayar.cek', $pesanan->id) }}"
                            method="POST"
                            class="mt-3 text-center">
                            @csrf
                            <p class="text-xs text-gray-500">Sudah bayar tapi status belum berubah?</p>
                            <button type="submit" class="mt-1 text-sm font-semibold text-green-700 underline">
                                Cek Status Pembayaran
                            </button>
                        </form>
                    @endif
                @endif
            @endif
        </div>
    @endif


    {{-- Pengiriman dan Metode Pembayaran --}}
    <div class="rounded-2xl border border-green-200 bg-white p-5">
        <div>
        <h2 class="mb-2 text-lg font-bold text-gray-900">
            Pengiriman
        </h2>

        @php
            $metodePengirimanRaw = strtolower(trim($pesanan->metode_pengiriman ?? ''));

            $labelMetodePengiriman = match ($metodePengirimanRaw) {
                'kendaraan', 'kendaraan sendiri', 'kendaraan petani' => 'Kendaraan Petani',
                'ambil', 'ambil sendiri' => 'Ambil Sendiri',
                default => $pesanan->metode_pengiriman ?? 'Belum ditentukan',
            };
        @endphp

        <p class="text-base text-gray-900">
            {{ $labelMetodePengiriman }}
        </p>

        <p class="mt-1 text-base leading-relaxed text-green-800">
            {{ $pesanan->nama_penerima }},
            {{ $pesanan->alamat_pengiriman }}
        </p>
    </div>

    <div class="mt-4">
        <h2 class="mb-2 text-lg font-bold text-gray-900">
            Metode Pembayaran
        </h2>

        <p class="text-base text-gray-800">
            @if ($metode)
                {{ $metodePembayaran[$metode] ?? $metode }}
            @else
                Pembayaran Online (Midtrans)
            @endif
        </p>
    </div>
</div>

    {{-- Rincian Biaya --}}
    @php
        $ppn = $pesanan->ppn;
        $totalDenganPPN = $pesanan->total;
    @endphp

    <div class="rounded-2xl border border-green-200 bg-white p-5">
        <h2 class="mb-3 text-lg font-bold text-gray-900">
            Rincian Biaya
        </h2>

        <div class="space-y-3 text-base">

            {{-- Subtotal Produk --}}
            <div class="flex justify-between gap-4">
                <span class="text-green-800">Subtotal produk</span>
                <span class="text-gray-900">
                    Rp {{ number_format($pesanan->subtotal, 0, ',', '.') }}
                </span>
            </div>

            {{-- Ongkos Kirim --}}
            <div class="flex justify-between gap-4">
                <span class="text-green-800">Ongkos kirim</span>
                <span class="text-gray-900">
                    Rp {{ number_format($pesanan->ongkir, 0, ',', '.') }}
                </span>
            </div>

            {{-- PPN 10% --}}
            <div class="flex justify-between gap-4">
                <span class="text-green-800">PPN (10%)</span>
                <span class="text-gray-900">
                    Rp {{ number_format($ppn, 0, ',', '.') }}
                </span>
            </div>

            {{-- Total --}}
            <div class="flex justify-between border-t border-green-100 pt-3">
                <span class="font-bold text-gray-900">Total</span>
                <span class="font-extrabold text-green-700">
                    Rp {{ number_format($totalDenganPPN, 0, ',', '.') }}
                </span>
            </div>

        </div>
    </div>


    {{-- Informasi Penjual --}}
    @php
        $petaniId = $pesanan->petani_id;
        $produkChat = $pesanan->item->first()?->produk;
    @endphp

    <div class="rounded-2xl border border-green-200 bg-white p-5">
        <h2 class="mb-3 font-bold text-gray-800">
            Penjual
        </h2>

        <p class="mb-4 text-sm font-semibold text-gray-800">
            {{ $namaPetani }}
        </p>

        <div class="grid grid-cols-2 gap-3">
            {{-- Lihat Toko --}}
            <a
                href="{{ route('pengusaha.toko', $petaniId) }}"
                class="flex items-center justify-center rounded-xl border-2 border-green-700 bg-white px-3 py-3 text-sm font-bold text-green-700 transition hover:bg-green-50"
            >
                Lihat Toko
            </a>

            {{-- Hubungi Penjual --}}
            @if ($produkChat?->slug)
                <a
                    href="{{ route('pengusaha.pesan', ['produk' => $produkChat->slug]) }}"
                    class="flex items-center justify-center rounded-xl bg-green-700 px-3 py-3 text-sm font-bold text-white transition hover:bg-green-800"
                >
                    Hubungi Penjual
                </a>
            @else
                <a
                    href="{{ route('pengusaha.pesan') }}"
                    class="flex items-center justify-center rounded-xl bg-green-700 px-3 py-3 text-sm font-bold text-white transition hover:bg-green-800"
                >
                    Hubungi Penjual
                </a>
            @endif
        </div>
    </div>

    {{-- Kembali --}}
    <a
        href="{{ route('pengusaha.pesanan') }}"
        class="block w-full rounded-xl border border-green-200 bg-white px-4 py-3 text-center text-sm font-bold text-green-800 hover:bg-green-50">
        Kembali ke Pesanan
    </a>

</div>

@if (session('success'))
    <x-toast :message="session('success')" />
@endif

@if (session('error'))
    <x-toast :message="session('error')" />
@endif

@endsection
