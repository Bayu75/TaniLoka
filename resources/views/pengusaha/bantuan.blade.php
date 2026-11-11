
@extends('layouts.pengusaha')

@section('title', 'Bantuan & Informasi - TaniLoka')

@section('content')

<div class="min-h-screen bg-[#F8FAF5]">

{{-- HEADER HALAMAN --}}
<div class="relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen border-b border-[#D4E8D0] bg-white">

    <div class="relative mx-auto max-w-2xl px-4 py-3">

        {{-- TOMBOL KEMBALI --}}
        <a href="{{ route('pengusaha.akun') }}"
            class="mb-2 inline-flex items-center gap-1 text-sm font-extrabold text-[#087C3E] hover:text-green-800
                   md:absolute md:right-full md:top-1/2 md:mb-0 md:mr-4 md:-translate-y-1/2 md:whitespace-nowrap">

            <span class="text-2xl font-black leading-none">&lt;</span>
            <span>Kembali</span>
        </a>

        {{-- JUDUL DAN DESKRIPSI --}}
        <div>
            <h1 class="text-xl font-extrabold text-[#1A2A1A]">
                Bantuan & Informasi
            </h1>

            <p class="mt-1 text-sm text-[#6B806B]">
                Temukan jawaban atas pertanyaan seputar penggunaan TaniLoka.
            </p>
        </div>

    </div>

</div>


   <div class="mx-auto max-w-2xl px-4 pt-2 pb-4">

        {{-- HUBUNGI WHATSAPP --}}
        <div class="mb-5">
            @if(config('services.taniloka.whatsapp'))

                <a href="https://wa.me/{{ config('services.taniloka.whatsapp') }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex w-full items-center justify-center gap-3 rounded-2xl bg-[#07813F] px-5 py-5 font-bold text-white shadow-sm transition hover:bg-green-800">

                    <span class="text-xl">▣</span>
                    Hubungi via WhatsApp

                </a>

            @else

                <div class="rounded-2xl bg-[#07813F] px-5 py-5 text-center font-bold text-white">
                    Hubungi via WhatsApp
                    <p class="mt-1 text-xs font-normal text-green-100">
                        Kontak bantuan akan segera tersedia.
                    </p>
                </div>

            @endif
        </div>

        {{-- PERTANYAAN UMUM --}}
        <div class="overflow-hidden rounded-2xl border border-[#D4E8D0] bg-white">

            <div class="bg-[#E8F5E9] px-5 py-4">
                <h2 class="text-lg font-extrabold text-[#1A2A1A]">
                    Pertanyaan Umum
                </h2>

                <p class="mt-1 text-xs text-[#6B806B]">
                    Klik pertanyaan untuk melihat jawabannya.
                </p>
            </div>

            <div x-data="{ terbuka: 0 }">

                {{-- PERTANYAAN 1 --}}
                <div class="border-b border-[#E5EEE2]">

                    <button
                        type="button"
                        @click="terbuka = terbuka === 1 ? 0 : 1"
                        class="flex w-full items-center justify-between gap-3 px-5 py-5 text-left">

                        <span class="text-sm font-bold text-[#1A2A1A]">
                            Bagaimana cara memesan?
                        </span>

                        <span class="shrink-0 text-sm font-semibold text-[#087C3E]"
                            x-text="terbuka === 1 ? 'Tutup' : 'Buka'">
                        </span>

                    </button>

                    <div x-show="terbuka === 1" x-collapse
                        class="px-5 pb-5 text-sm leading-7 text-[#648064]">

                        Buka halaman Katalog, pilih produk yang ingin dibeli,
                        tentukan jumlah pembelian sesuai minimal pesanan,
                        kemudian masukkan produk ke keranjang.

                        Selanjutnya, pilih produk yang ingin dibeli dan
                        lanjutkan ke proses pemesanan.

                    </div>

                </div>

                {{-- PERTANYAAN 2 --}}
                <div class="border-b border-[#E5EEE2]">

                    <button
                        type="button"
                        @click="terbuka = terbuka === 2 ? 0 : 2"
                        class="flex w-full items-center justify-between gap-3 px-5 py-5 text-left">

                        <span class="text-sm font-bold text-[#1A2A1A]">
                            Apa arti status pesanan dan tenggatnya?
                        </span>

                        <span class="shrink-0 text-sm font-semibold text-[#087C3E]"
                            x-text="terbuka === 2 ? 'Tutup' : 'Buka'">
                        </span>

                    </button>

                    <div x-show="terbuka === 2" x-collapse
                        class="px-5 pb-5 text-sm leading-7 text-[#648064]">

                        Status pesanan menunjukkan perkembangan pesanan
                        yang kamu lakukan.

                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            <li>Menunggu Konfirmasi: pesanan menunggu respons petani.</li>
                            <li>Diproses: pesanan sedang disiapkan.</li>
                            <li>Dikirim: produk sedang dalam perjalanan.</li>
                            <li>Selesai: pesanan telah diterima.</li>
                            <li>Dibatalkan: pesanan tidak dilanjutkan.</li>
                        </ul>

                        Perhatikan tenggat pembayaran atau konfirmasi
                        yang tercantum pada detail pesanan.

                    </div>

                </div>

                {{-- PERTANYAAN 3 --}}
                <div class="border-b border-[#E5EEE2]">

                    <button
                        type="button"
                        @click="terbuka = terbuka === 3 ? 0 : 3"
                        class="flex w-full items-center justify-between gap-3 px-5 py-5 text-left">

                        <span class="text-sm font-bold text-[#1A2A1A]">
                            Bagaimana pembayaran dan unggah bukti?
                        </span>

                        <span class="shrink-0 text-sm font-semibold text-[#087C3E]"
                            x-text="terbuka === 3 ? 'Tutup' : 'Buka'">
                        </span>

                    </button>

                    <div x-show="terbuka === 3" x-collapse
                        class="px-5 pb-5 text-sm leading-7 text-[#648064]">

                        Ikuti petunjuk pembayaran yang tersedia pada
                        detail pesanan.

                        Jika sistem meminta bukti pembayaran, unggah
                        gambar atau dokumen bukti transfer sesuai
                        format yang ditentukan.

                    </div>

                </div>

                {{-- PERTANYAAN 4 --}}
                <div class="border-b border-[#E5EEE2]">

                    <button
                        type="button"
                        @click="terbuka = terbuka === 4 ? 0 : 4"
                        class="flex w-full items-center justify-between gap-3 px-5 py-5 text-left">

                        <span class="text-sm font-bold text-[#1A2A1A]">
                            Bagaimana cara mengubah profil usaha?
                        </span>

                        <span class="shrink-0 text-sm font-semibold text-[#087C3E]"
                            x-text="terbuka === 4 ? 'Tutup' : 'Buka'">
                        </span>

                    </button>

                    <div x-show="terbuka === 4" x-collapse
                        class="px-5 pb-5 text-sm leading-7 text-[#648064]">

                        Buka menu Akun, kemudian pilih Profil Usaha.
                        Kamu dapat mengubah nama pemilik, nama usaha,
                        email, nomor WhatsApp, jenis usaha, dan
                        deskripsi usaha.

                        Setelah selesai, tekan tombol Simpan Perubahan.

                    </div>

                </div>

                {{-- PERTANYAAN 5 --}}
                <div class="border-b border-[#E5EEE2]">

                    <button
                        type="button"
                        @click="terbuka = terbuka === 5 ? 0 : 5"
                        class="flex w-full items-center justify-between gap-3 px-5 py-5 text-left">

                        <span class="text-sm font-bold text-[#1A2A1A]">
                            Bagaimana cara mengubah alamat usaha?
                        </span>

                        <span class="shrink-0 text-sm font-semibold text-[#087C3E]"
                            x-text="terbuka === 5 ? 'Tutup' : 'Buka'">
                        </span>

                    </button>

                    <div x-show="terbuka === 5" x-collapse
                        class="px-5 pb-5 text-sm leading-7 text-[#648064]">

                        Masuk ke menu Akun, lalu pilih Alamat Saya.
                        Kamu dapat memperbarui alamat usaha dan
                        menentukan lokasi melalui peta apabila
                        fitur tersebut tersedia.

                    </div>

                </div>

                {{-- PERTANYAAN 6 --}}
                <div>

                    <button
                        type="button"
                        @click="terbuka = terbuka === 6 ? 0 : 6"
                        class="flex w-full items-center justify-between gap-3 px-5 py-5 text-left">

                        <span class="text-sm font-bold text-[#1A2A1A]">
                            Bagaimana jika produk yang dipesan bermasalah?
                        </span>

                        <span class="shrink-0 text-sm font-semibold text-[#087C3E]"
                            x-text="terbuka === 6 ? 'Tutup' : 'Buka'">
                        </span>

                    </button>

                    <div x-show="terbuka === 6" x-collapse
                        class="px-5 pb-5 text-sm leading-7 text-[#648064]">

                        Jika produk tidak sesuai pesanan atau mengalami
                        kerusakan, segera hubungi petani melalui fitur
                        Pesan atau hubungi admin TaniLoka untuk
                        mendapatkan bantuan.

                    </div>

                </div>

            </div>
        </div>

        {{-- INFORMASI TAMBAHAN --}}
        <div class="mt-5 rounded-2xl border border-[#D4E8D0] bg-white p-5">

            <h3 class="font-bold text-[#1A2A1A]">
                Masih membutuhkan bantuan?
            </h3>

            <p class="mt-2 text-sm leading-6 text-[#6B806B]">
                Jika pertanyaanmu belum terjawab, silakan hubungi
                admin TaniLoka untuk mendapatkan informasi lebih lanjut.
            </p>

        </div>

        <p class="mt-6 text-center text-xs text-[#8A9A8A]">
            © {{ date('Y') }} TaniLoka - Pasar Tani Bali
        </p>

    </div>
</div>

@endsection
