@extends('layouts.pengusaha')

@section('title', 'Detail Pesanan - TaniLoka')

@section('content')

<div class="mx-auto flex w-full max-w-[760px] flex-col gap-5">


{{-- Judul Detail Pesanan --}}
<div class="relative left-1/2 -mt-4 w-screen -translate-x-1/2 border-b border-green-200 bg-white">    <div class="mx-auto flex max-w-[760px] items-center gap-4 px-4 py-4">

        <a href="{{ route('pengusaha.pesanan') }}"
            class="inline-flex shrink-0 items-center gap-2 font-semibold text-green-800 transition hover:text-green-600">
            <span class="text-2xl leading-none">‹</span>
            <span>Kembali</span>
        </a>

        <div>
            <h1 class="text-lg font-bold leading-tight text-gray-800">
                Detail Pesanan
            </h1>
            <p class="text-sm text-gray-500">
                #{{ $pesanan['id'] }}
            </p>
        </div>

    </div>
</div>



    {{-- Status Pesanan --}}
    @if ($pesanan['status'] === 'menunggu_konfirmasi')

        <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-yellow-100 text-2xl">
                    ⏳
                </div>
                <div>
                    <h2 class="font-bold text-yellow-800">Menunggu Konfirmasi</h2>
                    <p class="mt-1 text-sm text-yellow-700">
                        Pesanan sedang menunggu konfirmasi dari penjual.
                    </p>
                </div>
            </div>
        </div>

        {{-- Batas Konfirmasi --}}
        <div class="rounded-2xl border border-yellow-200 bg-white p-4">
            <p class="mb-3 text-sm font-semibold text-yellow-800">
                Menunggu penjual mengonfirmasi pesanan Anda.
            </p>

            <div class="inline-flex items-center gap-2 rounded-lg bg-yellow-50 px-3 py-2 text-sm font-semibold text-yellow-800">
                <span>⏱️</span>
                <span>{{ $pesanan['notifikasi'] }}</span>
            </div>

            <p class="mt-2 text-sm text-gray-600">
                Jika pesanan tidak dikonfirmasi sebelum tenggat oleh penjual, maka pesanan otomatis dibatalkan.
            </p>
        </div>

    @elseif ($pesanan['status'] === 'menunggu_pembayaran')

        {{-- Status Menunggu Pembayaran --}}
        <div class="rounded-2xl border border-orange-200 bg-orange-100 p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-2xl">
                    💳
                </div>
                <div>
                    <h2 class="font-bold text-orange-800">Menunggu Pembayaran</h2>
                    <p class="mt-1 text-sm text-orange-700">
                        Pesanan menunggu pembayaran dari Anda.
                    </p>
                </div>
            </div>
        </div>

        {{-- Batas Pembayaran --}}
        <div class="rounded-2xl border border-orange-200 bg-white p-4">
            <p class="mb-3 text-sm font-semibold text-red-600">
                Segera lakukan pembayaran sebelum batas waktu berakhir.
            </p>

            <div class="inline-flex items-center gap-2 rounded-lg bg-red-50 px-3 py-2 text-sm font-semibold text-red-600">
                <span>⏱️</span>
                <span>{{ $pesanan['notifikasi'] }}</span>
            </div>

            <p class="mt-2 text-sm text-gray-600">
                Jika pembayaran tidak dilakukan sebelum tenggat, maka pesanan akan otomatis dibatalkan.
            </p>
        </div>

    @elseif ($pesanan['status'] === 'dikirim')

        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-2xl">
                    🚚
                </div>
                <div>
                    <h2 class="font-bold text-blue-800">Sedang Dikirim</h2>
                    <p class="mt-1 text-sm text-blue-700">
                        Pesanan sedang dalam perjalanan menuju alamat Anda.
                    </p>
                </div>
            </div>
        </div>

    @elseif ($pesanan['status'] === 'selesai')

        <div class="rounded-2xl border border-green-200 bg-green-100 p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-2xl">
                    🎉
                </div>
                <div>
                    <h2 class="font-bold text-green-800">Selesai</h2>
                    <p class="mt-1 text-sm text-green-700">
                        Pesanan Anda telah selesai.
                    </p>
                </div>
            </div>
        </div>

    @elseif ($pesanan['status'] === 'dibatalkan')

        <div class="rounded-2xl border border-red-200 bg-red-50 p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-2xl text-red-600">
                    ❌
                </div>

                <div>
                    <h2 class="font-bold text-red-800">Dibatalkan</h2>
                    <p class="mt-1 text-sm text-red-700">
                        Pesanan telah dibatalkan.
                    </p>
                </div>
            </div>
        </div>

    @endif


    @if ($pesanan['status'] === 'selesai')
        <div
            x-data="{ bukaUlasan: false, rating: 0, ulasan: '', terkirim: false }"
            class="space-y-4">

            {{-- Tombol Beri Penilaian --}}
            <button
                type="button"
                @click="bukaUlasan = !bukaUlasan"
                class="w-full rounded-xl bg-yellow-400 px-4 py-3 text-sm font-bold text-gray-900 transition hover:bg-yellow-500">
                Beri Penilaian
            </button>

            {{-- Form Penilaian --}}
            <div
                x-show="bukaUlasan"
                x-transition
                class="rounded-2xl border border-green-200 bg-white p-5">

                <h2 class="mb-2 font-bold text-gray-800">
                    Berikan Penilaian
                </h2>

                <p class="mb-3 text-sm text-gray-600">
                    Bagaimana pengalaman Anda dengan produk ini?
                </p>

                {{-- Rating Bintang --}}
                <div class="mb-4 flex gap-2">
                    <template x-for="bintang in [1, 2, 3, 4, 5]" :key="bintang">
                        <button
                            type="button"
                            @click="rating = bintang"
                            class="text-3xl transition"
                            :class="rating >= bintang ? 'text-yellow-400' : 'text-gray-300'">
                            ★
                        </button>
                    </template>
                </div>

                {{-- Kolom Ulasan --}}
                <label class="mb-2 block text-sm font-semibold text-gray-800">
                    Tulis Ulasan
                </label>

                <textarea
                    x-model="ulasan"
                    rows="4"
                    placeholder="Ceritakan pengalaman Anda..."
                    class="w-full rounded-xl border border-gray-300 p-3 text-sm focus:border-green-600 focus:outline-none">
                </textarea>

                {{-- Tombol Kirim --}}
                <button
                    type="button"
                    @click="if (rating > 0 && ulasan.trim() !== '') { terkirim = true; bukaUlasan = false; } else { alert('Pilih rating dan tulis ulasan terlebih dahulu.'); }"
                    class="mt-3 w-full rounded-xl bg-green-700 px-4 py-3 text-sm font-bold text-white transition hover:bg-green-800">
                    Kirim Penilaian
                </button>

                <p
                    x-show="terkirim"
                    class="mt-3 text-center text-sm font-semibold text-green-700">
                    Terima kasih atas penilaian Anda!
                </p>
            </div>
        </div>
    @endif



    {{-- Tombol dan Konfirmasi Pembatalan Pesanan --}}
    @if (in_array($pesanan['status'], ['menunggu_konfirmasi', 'menunggu_pembayaran']))
        <div x-data="{ konfirmasiBatal: false }">

            {{-- Tombol Batalkan Pesanan --}}
            <button
                type="button"
                x-show="!konfirmasiBatal"
                @click="konfirmasiBatal = true"
                class="w-full rounded-xl border border-red-300 bg-white px-4 py-3 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                Batalkan Pesanan
            </button>

            {{-- Kotak Konfirmasi --}}
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
                        class="flex-1 rounded-xl border border-red-400 bg-white px-4 py-3 text-sm font-bold text-red-600 transition hover:bg-red-50">
                        Tidak
                    </button>

                    <form
                        action="{{ route('pengusaha.pesanan.cancel', $pesanan['id']) }}"
                        method="POST"
                        class="flex-1">

                        @csrf

                        <button
                            type="submit"
                            class="w-full rounded-xl bg-red-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-red-700">
                            Ya, Batalkan
                        </button>
                    </form>
                </div>
            </div>

        </div>
    @endif


    {{-- Riwayat Pesanan --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5">
        <h2 class="mb-5 text-lg font-bold text-gray-800">
            Riwayat Pesanan
        </h2>

            <x-order-timeline
                :status="$pesanan['status']"
                :riwayat-status="$riwayatStatus"
                :tanggal-dibatalkan="$tanggalDibatalkan"
                :alasan-dibatalkan="$alasanDibatalkan"
            />
    </div>


    {{-- Produk yang Dipesan --}}
    <div class="rounded-2xl border border-green-200 bg-white p-5">
        <h2 class="mb-4 text-lg font-bold text-gray-800">
            Produk Dipesan
        </h2>

        @foreach ($pesanan['items'] as $item)
            <div class="flex items-center gap-3 {{ !$loop->last ? 'mb-4 border-b border-gray-100 pb-4' : '' }}">
                <img
                    src="{{ $item->produk?->fotoUtama?->path ? asset($item->produk->fotoUtama->path) : asset('images/no-image.png') }}"
                    alt="{{ $item->nama_produk }}"
                    class="h-16 w-16 rounded-xl object-cover"
                >

                <div class="flex-1">
                    <h3 class="text-sm font-bold text-gray-800">
                        {{ $item->nama_produk }}
                    </h3>

                    <p class="mt-1 text-sm text-green-800">
                        {{ rtrim(rtrim(number_format((float) $item->jumlah_kg, 2, ',', '.'), '0'), ',') }} kg
                    </p>

                    <p class="mt-1 font-bold text-green-700">
                        Rp{{ number_format($item->subtotal, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>

        {{-- Instruksi Pembayaran --}}
        @if ($pesanan['status'] === 'menunggu_pembayaran')
            @php
                $metode = $pesanan['metode_pembayaran'] ?? '';

                $instruksiPembayaran = match ($metode) {
                    'transfer_bank' => [
                        'judul' => 'Transfer ke rekening berikut:',
                        'jenis' => 'Bank',
                        'nama' => 'Bank Central Asia (BCA)',
                        'label_nomor' => 'No. rekening:',
                        'nomor' => '883012345678',
                        'nomor_tampil' => '8830 1234 5678',
                    ],
                    'ovo' => [
                        'judul' => 'Lakukan pembayaran melalui OVO:',
                        'jenis' => null,
                        'nama' => 'OVO',
                        'label_nomor' => 'Nomor akun OVO:',
                        'nomor' => $pesanan['no_wa_petani'] ?? null,
                        'nomor_tampil' => $pesanan['no_wa_petani'] ?? 'Nomor Petani belum tersedia',
                    ],

                    'gopay' => [
                        'judul' => 'Lakukan pembayaran melalui GoPay:',
                        'jenis' => null,
                        'nama' => 'GoPay',
                        'label_nomor' => 'Nomor akun GoPay:',
                        'nomor' => $pesanan['no_wa_petani'] ?? null,
                        'nomor_tampil' => $pesanan['no_wa_petani'] ?? 'Nomor Petani belum tersedia',
                    ],
                    default => null,
                };
            @endphp

            <div class="rounded-2xl border border-green-200 bg-white p-5">
                <h2 class="mb-3 font-bold text-gray-800">
                    Instruksi Pembayaran
                </h2>

                @if ($instruksiPembayaran)
                    <p class="mb-2 text-sm text-gray-600">
                        {{ $instruksiPembayaran['judul'] }}
                    </p>

                    <div class="space-y-2 text-sm">
                        @if ($instruksiPembayaran['jenis'])
                            <p>
                                {{ $instruksiPembayaran['jenis'] }}:
                                <span class="font-bold text-gray-800">
                                    {{ $instruksiPembayaran['nama'] }}
                                </span>
                            </p>
                        @endif

                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-gray-500">
                                    {{ $instruksiPembayaran['label_nomor'] }}
                                </p>

                                <p class="font-bold text-gray-800">
                                    {{ $instruksiPembayaran['nomor_tampil'] }}
                                </p>
                            </div>

                            @if ($instruksiPembayaran['nomor'])
                                <button
                                    type="button"
                                    onclick="navigator.clipboard.writeText(@js($instruksiPembayaran['nomor']))
                                        .then(() => alert('Nomor berhasil disalin!'))
                                        .catch(() => alert('Gagal menyalin nomor.'))"
                                    class="rounded-lg border border-green-700 px-4 py-2 text-sm font-bold text-green-700 hover:bg-green-50">
                                    Salin
                                </button>
                            @endif
                        </div>

                        <p>
                            Atas nama:
                            <span class="font-bold text-gray-800">
                                    {{ $pesanan['nama_petani'] }}
                            </span>
                        </p>

                        <p>
                            Jumlah:
                            <span class="font-bold text-green-700">
                                Rp{{ number_format($pesanan['jumlah_pembayaran'], 0, ',', '.') }}
                            </span>
                        </p>
                    </div>
                @else
                    <p class="text-sm text-red-600">
                        Metode pembayaran belum tersedia atau belum dikenali.
                    </p>
                @endif

                @if (!empty($pesanan['bukti_path']))
                    <div class="mt-3 inline-flex items-center gap-2 rounded-lg bg-yellow-50 px-3 py-2 text-xs font-semibold text-yellow-700">
                        ⏳ Menunggu konfirmasi Petani
                    </div>
                @else
                    <div class="mt-3 inline-flex items-center gap-2 rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600">
                        ⏱️ Menunggu pembayaran
                    </div>
                @endif
            </div>
        @endif

    {{-- Unggah Bukti Pembayaran --}}
    @if ($pesanan['status'] === 'menunggu_pembayaran')

        @if (empty($pesanan['bukti_path']))
            <div
                class="rounded-2xl border border-green-200 bg-white p-5"
                x-data="{ buktiDipilih: false }">

                <h2 class="mb-3 font-bold text-gray-800">
                    Unggah Bukti Pembayaran
                </h2>

                <form
                    action="{{ route('pengusaha.pesanan.bukti.upload', $pesanan['id']) }}"
                    method="POST"
                    enctype="multipart/form-data">

                    @csrf

                    <label
                        for="bukti-transfer"
                        class="flex cursor-pointer items-center justify-center rounded-xl border-2 border-dashed border-green-600 bg-white px-4 py-4 text-center text-sm font-semibold text-green-700 transition hover:bg-green-50">

                        <span x-text="buktiDipilih ? 'Foto bukti berhasil dipilih' : 'Pilih Foto Bukti Transfer'">
                            Pilih Foto Bukti Transfer
                        </span>
                    </label>

                    <input
                        type="file"
                        id="bukti-transfer"
                        name="bukti_transfer"
                        accept="image/jpeg,image/png,image/webp"
                        class="hidden"
                        required
                        @change="buktiDipilih = $event.target.files.length > 0">

                    @error('bukti_transfer')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <button
                        type="submit"
                        :disabled="!buktiDipilih"
                        class="mt-2 w-full rounded-xl bg-green-700 px-4 py-3 text-sm font-bold text-white transition hover:bg-green-800 disabled:cursor-not-allowed disabled:bg-green-200 disabled:text-green-700">
                        Kirim Bukti Pembayaran
                    </button>
                </form>

                <p class="mt-2 text-xs text-gray-500">
                    Format JPG, PNG, atau WEBP. Maksimal 5 MB.
                </p>
            </div>

        @else
            <div class="rounded-2xl border border-green-200 bg-green-50 p-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-green-100 text-green-700">
                        ✓
                    </div>

                    <div>
                        <h2 class="font-bold text-green-800">
                            Bukti Pembayaran Berhasil Dikirim
                        </h2>

                        <p class="mt-1 text-sm text-green-700">
                            Petani akan memeriksa bukti pembayaran Anda.
                        </p>
                    </div>
                </div>

            </div>
        @endif

    @endif


    {{-- Pengiriman dan Metode Pembayaran --}}
    <div class="rounded-2xl border border-green-200 bg-white p-5">


        {{-- Informasi Pengiriman --}}
        <div>
            <h2 class="mb-1 font-bold text-gray-800">
                Informasi Penerima
            </h2>

            <p class="text-sm font-semibold text-gray-800">
                {{ $pesanan['nama_penerima'] ?? '-' }}
            </p>

            <p class="mt-1 text-sm text-gray-800">
                {{ $pesanan['no_hp_penerima'] ?? '-' }}
            </p>

            <p class="mt-1 text-sm text-green-800">
                {{ $pesanan['alamat_pengiriman'] ?? '-' }}
            </p>

            <h2 class="mt-3 mb-1 font-bold text-gray-800">
                Pengiriman
            </h2>

            <p class="text-sm text-gray-800">
                {{ match($pesanan['metode_pengiriman'] ?? '') {
                    'kendaraan' => 'Kendaraan Petani',
                    'jnt' => 'J&T Express',
                    'ambil' => 'Ambil Sendiri',
                    default => '-',
                } }}
            </p>
        </div>


        {{-- Metode Pembayaran --}}
        <div class="mt-4">
            <h2 class="mb-1 font-bold text-gray-800">
                Metode Pembayaran
            </h2>

            <p class="text-sm text-gray-800">
                {{ $pesanan['label_pembayaran'] ?? $pesanan['metode_pembayaran'] ?? '-' }}
            </p>
        </div>

    </div>

    {{-- Rincian Biaya --}}
    <div class="rounded-2xl border border-green-200 bg-white p-5">
        <h2 class="mb-3 font-bold text-gray-800">
            Rincian Biaya
        </h2>

        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-green-800">Subtotal produk</span>
                <span class="text-gray-800">
                    Rp{{ number_format($pesanan['subtotal'], 0, ',', '.') }}
                </span>
            </div>

            <div class="flex justify-between">
                <span class="text-green-800">Ongkos kirim</span>
                <span class="text-gray-800">
                    Rp{{ number_format($pesanan['ongkir'], 0, ',', '.') }}
                </span>
            </div>

            <div class="flex justify-between">
                <span class="text-green-800">Biaya layanan</span>
                <span class="text-gray-800">
                    Rp{{ number_format($pesanan['biaya_layanan'], 0, ',', '.') }}
                </span>
            </div>

            <div class="mt-3 flex justify-between border-t border-green-100 pt-3">
                <span class="font-bold text-gray-800">Total</span>
                <span class="font-extrabold text-green-700">
                    Rp{{ number_format($pesanan['total'], 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Informasi Penjual --}}
    <div class="rounded-2xl border border-green-200 bg-white p-5">
        <h2 class="mb-3 font-bold text-gray-800">
            Penjual
        </h2>

        <p class="mb-3 text-sm font-semibold text-gray-800">
            {{ $pesanan['nama_petani'] }}
        </p>

        <div class="flex gap-3">
            <a
                 href="{{ route('pengusaha.toko', $pesanan['petani_id']) }}"
                class="flex-1 rounded-xl border border-green-700 bg-white px-4 py-3 text-center text-sm font-bold text-green-700 transition hover:bg-green-50"
            >
                Lihat Toko
            </a>

            <a
                href="{{ route('pengusaha.pesan') }}"
                class="flex-1 rounded-xl border border-green-700 bg-white px-4 py-3 text-center text-sm font-bold text-green-700 transition hover:bg-green-50"
            >
                Hubungi Penjual
            </a>

        </div>
    </div>

    {{-- Tombol Kembali ke Pesanan --}}
    <div>
        <a
            href="{{ route('pengusaha.pesanan') }}"
            class="block w-full rounded-xl border border-green-200 bg-white px-4 py-3 text-center text-sm font-bold text-green-800 transition hover:bg-green-50">
            Kembali ke Pesanan
        </a>
    </div>

</div>

@if (session('success'))
    <x-toast :message="session('success')" />
@endif

@endsection
