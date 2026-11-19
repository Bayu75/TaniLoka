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
        'transfer_bank' => 'Transfer Bank',
        'gopay' => 'GoPay',
        'ovo' => 'OVO',
        'cod' => 'COD',
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
    <div class="relative left-1/2 -mt-4 w-screen -translate-x-1/2 border-b border-green-200 bg-white">
        <div class="mx-auto flex max-w-[760px] items-center gap-4 px-4 py-4">

            <a href="{{ route('pengusaha.pesanan') }}"
               class="inline-flex shrink-0 items-center gap-2 font-semibold text-green-800 hover:text-green-600">
                <span class="text-2xl">‹</span>
                Kembali
            </a>

            <div>
                <h1 class="text-lg font-bold text-gray-800">
                    Detail Pesanan
                </h1>
                <p class="text-sm text-gray-500">
                    #{{ $pesanan->kode }}
                </p>
            </div>

        </div>
    </div>

    {{-- Notifikasi Status --}}
    <div class="rounded-2xl border p-5 {{ $warnaBorder }}">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-2xl">
                {{ $info['ikon'] }}
            </div>

            <div>
                <h2 class="font-bold">
                    {{ $info['label'] }}
                </h2>
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
                sudahUpload: {{ $bukti ? 'true' : 'false' }},
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
                Silakan lakukan pembayaran dan unggah bukti pembayaran.
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

                            @if (
                                $statusAktif &&
                                in_array($statusSekarang, ['dikirim', 'siap_diambil', 'terkirim'])
                            )
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


    {{-- Bukti Pembayaran --}}
    @if ($status === 'menunggu_pembayaran' || $bukti)
        <div class="rounded-2xl border border-green-200 bg-white p-5">
            <h2 class="mb-3 text-lg font-bold text-gray-900">
                Bukti Pembayaran
            </h2>

            @if ($bukti)
                <div class="rounded-xl bg-green-50 p-4">
                    <p class="text-sm font-semibold text-green-800">
                        Bukti pembayaran berhasil diunggah.
                    </p>

                    <a
                        href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($bukti) }}"
                        target="_blank"
                        class="mt-2 inline-block text-sm font-semibold text-green-700 underline">
                        Lihat Bukti Pembayaran
                    </a>

                    @if ($pesanan->pembayaran?->status === 'menunggu_verifikasi')
                        <p class="mt-2 text-sm text-orange-700">
                            Menunggu verifikasi petani.
                        </p>
                    @endif
                </div>

            @elseif ($status === 'menunggu_pembayaran')
                <p class="mb-4 text-sm text-gray-600">
                    Silakan unggah bukti transfer atau pembayaran Anda.
                </p>

                <form
                    action="{{ route('pengusaha.pesanan.bukti.upload', $pesanan->id) }}"
                    method="POST"
                    enctype="multipart/form-data">

                    @csrf

                    <input
                        type="file"
                        name="bukti_pembayaran"
                        accept=".jpg,.jpeg,.png,.pdf"
                        required
                        class="block w-full rounded-xl border border-gray-300 p-3 text-sm">

                    @error('bukti_pembayaran')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <p class="mt-2 text-xs text-gray-500">
                        Format JPG, JPEG, PNG, atau PDF. Maksimal 5 MB.
                    </p>

                    <button
                        type="submit"
                        class="mt-4 w-full rounded-xl bg-green-700 px-4 py-3 font-bold text-white transition hover:bg-green-800">
                        Unggah Bukti Pembayaran
                    </button>
                </form>
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
                'jnt', 'j&t', 'j&t express' => 'J&T Express',
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

    @php
        $profilPetani = $pesanan->petani?->profilPetani;
        $nomorHpPetani = $pesanan->petani?->no_wa;

        $nomorPembayaran = match ($metode) {
            'gopay', 'ovo' => $nomorHpPetani,
            'transfer_bank' => $profilPetani?->no_rekening,
            default => null,
        };
    @endphp

    <div class="mt-4">
        <h2 class="mb-2 text-lg font-bold text-gray-900">
            Metode Pembayaran
        </h2>

        <p class="text-base text-gray-800">
            {{ $metodePembayaran[$metode] ?? $metode ?? 'Belum tersedia' }}
        </p>

        @if ($status === 'menunggu_pembayaran')

            @if (in_array($metode, ['gopay', 'ovo']))
                <div class="mt-3 rounded-xl bg-green-50 p-4">
                    <p class="mb-2 text-sm text-gray-600">
                        Nomor {{ strtoupper($metode) }} Petani
                    </p>

                    @if ($nomorPembayaran)
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-bold text-gray-900">
                                {{ $nomorPembayaran }}
                            </p>

                            <button
                                type="button"
                                x-data="{ disalin: false }"
                                @click="navigator.clipboard.writeText(@js($nomorPembayaran)).then(() => { disalin = true; setTimeout(() => disalin = false, 2000) })"
                                class="shrink-0 rounded-lg border border-green-700 px-3 py-2 text-sm font-semibold text-green-700 hover:bg-green-100">
                                <span x-text="disalin ? 'Tersalin!' : 'Salin'"></span>
                            </button>
                        </div>
                    @else
                        <p class="text-sm text-red-600">
                            Nomor HP petani belum tersedia.
                        </p>
                    @endif
                </div>

            @elseif ($metode === 'transfer_bank')
                <div class="mt-3 rounded-xl bg-green-50 p-4">
                    <p class="text-sm text-gray-600">
                        {{ $profilPetani?->nama_bank ?? 'Bank belum tersedia' }}
                    </p>

                    @if ($nomorPembayaran)
                        <div class="mt-2 flex items-center justify-between gap-3">
                            <p class="font-bold text-gray-900">
                                {{ $nomorPembayaran }}
                            </p>

                            <button
                                type="button"
                                x-data="{ disalin: false }"
                                @click="navigator.clipboard.writeText(@js($nomorPembayaran)).then(() => { disalin = true; setTimeout(() => disalin = false, 2000) })"
                                class="shrink-0 rounded-lg border border-green-700 px-3 py-2 text-sm font-semibold text-green-700 hover:bg-green-100">
                                <span x-text="disalin ? 'Tersalin!' : 'Salin'"></span>
                            </button>
                        </div>

                        <p class="mt-2 text-sm text-gray-600">
                            a.n. {{ $profilPetani?->atas_nama_rekening ?? '-' }}
                        </p>
                    @else
                        <p class="text-sm text-red-600">
                            Nomor rekening petani belum tersedia.
                        </p>
                    @endif
                </div>
            @endif

        @endif
    </div>
</div>

    {{-- Rincian Biaya --}}
    <div class="rounded-2xl border border-green-200 bg-white p-5">
        <h2 class="mb-3 text-lg font-bold text-gray-900">
            Rincian Biaya
        </h2>

        <div class="space-y-3 text-base">
            <div class="flex justify-between gap-4">
                <span class="text-green-800">Subtotal produk</span>
                <span class="text-gray-900">
                    Rp {{ number_format($pesanan->subtotal, 0, ',', '.') }}
                </span>
            </div>

            <div class="flex justify-between gap-4">
                <span class="text-green-800">Ongkos kirim</span>
                <span class="text-gray-900">
                    Rp {{ number_format($pesanan->ongkir, 0, ',', '.') }}
                </span>
            </div>

            <div class="flex justify-between border-t border-green-100 pt-3">
                <span class="font-bold text-gray-900">Total</span>
                <span class="font-extrabold text-green-700">
                    Rp {{ number_format($pesanan->total, 0, ',', '.') }}
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
