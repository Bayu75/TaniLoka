 @extends('layouts.pengusaha')

@section('title', 'Akun - TaniLoka')

@section('content')

@php
    $user    = auth()->user();
    $profil  = $user->profilPengusaha;
    $nama    = $profil?->nama_usaha ?: $user->name;
    $inisial = strtoupper(mb_substr($nama, 0, 1));

    $jenisList = [
        'restoran' => 'Restoran', 'kafe' => 'Kafe', 'hotel' => 'Hotel',
        'katering' => 'Katering', 'warung' => 'Warung Makan', 'lainnya' => 'Usaha Kuliner Lainnya',
    ];
    $jenisUsaha = $jenisList[$profil?->jenis_usaha] ?? 'Pengusaha Kuliner';
    $kota = $user->alamatUtama?->kabupaten ?? 'Bali';

    $jumlahKeranjang = \App\Models\KeranjangItem::where('user_id', $user->id)->count();
    $jumlahPesanan   = \App\Models\Pesanan::where('pengusaha_id', $user->id)->count();
@endphp


{{-- HEADER HALAMAN AKUN --}}
<div class="relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen border-b border-[#D4E8D0] bg-white">

    <div class="mx-auto max-w-3xl px-4 py-3">

        <h1 class="text-xl font-extrabold text-[#1A2A1A]">
            Akun
        </h1>

        <p class="mt-1 text-sm text-[#6B806B]">
            Kelola informasi dan aktivitas akun usaha Anda.
        </p>

    </div>

</div>

{{-- KONTEN HALAMAN AKUN --}}
<div class="mx-auto max-w-3xl py-5 md:py-6">


    {{-- PROFIL PENGUSAHA --}}
    <div class="mb-5 flex items-center gap-4 rounded-2xl bg-[#2E7D32] p-4 text-white shadow-sm">

        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-[#F5C400] text-2xl font-extrabold text-[#2E7D32]">
            {{ $inisial }}
        </div>

        <div class="min-w-0 flex-1 ">
            <h2 class="truncate text-lg font-bold">
                {{ $nama }}
            </h2>

                        <p class="text-sm text-green-100">
                {{ $jenisUsaha }}
            </p>

            <p class="mt-1 text-xs text-green-100">
                {{ $user->name }} · {{ $kota }}
            </p>
        </div>

        <span class="shrink-0 rounded-full bg-white/20 px-3 py-1 text-xs font-semibold">
            Akun Pengusaha
        </span>
    </div>

        {{-- VERIFIKASI AKUN --}}
    <a href="{{ route('pengusaha.verifikasi') }}"
       class="mb-5 flex items-center justify-between rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
        <div>
            <p class="font-semibold text-gray-800">Verifikasi Akun</p>
            <p class="text-xs text-gray-500">Lengkapi data dan dokumen agar bisa checkout</p>
        </div>
                @if ($user->terverifikasi())
            <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-[#2E7D32]">Terverifikasi</span>
        @elseif ($user->status_verifikasi === 'menunggu')
            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Sedang Ditinjau</span>
        @elseif ($user->status_verifikasi === 'perlu_perbaikan')
            <span class="rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-700">Perlu Perbaikan</span>
        @elseif ($user->status_verifikasi === 'ditolak')
            <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700">Ditolak</span>
        @else
            <span class="rounded-full bg-[#F5C400]/20 px-3 py-1 text-xs font-semibold text-yellow-700">Ajukan</span>
        @endif
    </a>

    {{-- STATISTIK --}}
    <div class="mb-6 grid grid-cols-2 gap-3">

        <a href="{{ route('pengusaha.pesanan') }}"
           class="rounded-2xl border border-[#DCE8D8] bg-white p-5 text-center transition hover:border-[#2E7D32]">

            <div class="mb-2 text-2xl">📋</div>

            <h3 class="text-2xl font-extrabold text-[#1A2A1A]">
                {{ $jumlahPesanan }}
            </h3>

            <p class="mt-1 text-sm text-[#6B806B]">
                Pesanan
            </p>
        </a>

        <a href="{{ route('pengusaha.keranjang') }}"
           class="rounded-2xl border border-[#DCE8D8] bg-white p-5 text-center transition hover:border-[#2E7D32]">

            <div class="mb-2 text-2xl">🛒</div>

            <h3 class="text-2xl font-extrabold text-[#1A2A1A]">
                {{ $jumlahKeranjang }}
            </h3>

            <p class="mt-1 text-sm text-[#6B806B]">
                Produk di Keranjang
            </p>
        </a>

    </div>

    {{-- AKUN SAYA --}}
    <section class="mb-6">

        <h3 class="mb-3 text-xs font-bold uppercase tracking-wide text-[#6B806B]">
            Akun Saya
        </h3>

        <div class="overflow-hidden rounded-2xl border border-[#DCE8D8] bg-white">

            <a href="{{ route('pengusaha.profil') }}"
               class="flex items-center gap-4 border-b border-[#E5EEE2] p-4 transition hover:bg-[#F5F9F2]">

                <span class="text-2xl">👤</span>

                <div class="min-w-0 flex-1">
                    <h4 class="font-bold text-[#1A2A1A]">
                        Profil Usaha
                    </h4>
                    <p class="text-sm text-[#829182]">
                        Informasi dan identitas usaha
                    </p>
                </div>

                <span class="text-lg text-[#829182]">›</span>
            </a>

            <a href="{{ route('pengusaha.alamat') }}"
               class="flex items-center gap-4 p-4 transition hover:bg-[#F5F9F2]">

                <span class="text-2xl">📍</span>

                <div class="min-w-0 flex-1">
                    <h4 class="font-bold text-[#1A2A1A]">
                        Alamat Usaha
                    </h4>
                    <p class="text-sm text-[#829182]">
                        Kelola alamat pengiriman
                    </p>
                </div>

                <span class="text-lg text-[#829182]">›</span>
            </a>

        </div>
    </section>

    {{-- AKTIVITAS --}}
    <section class="mb-6">

        <h3 class="mb-3 text-xs font-bold uppercase tracking-wide text-[#6B806B]">
            Aktivitas
        </h3>

        <div class="overflow-hidden rounded-2xl border border-[#DCE8D8] bg-white">

            <a href="{{ route('pengusaha.pesanan') }}"
               class="flex items-center gap-4 border-b border-[#E5EEE2] p-4 transition hover:bg-[#F5F9F2]">

                <span class="text-2xl">📋</span>

                <div class="min-w-0 flex-1">
                    <h4 class="font-bold text-[#1A2A1A]">
                        Pesanan Saya
                    </h4>
                    <p class="text-sm text-[#829182]">
                        Riwayat dan status pesanan
                    </p>
                </div>

                <span class="text-lg text-[#829182]">›</span>
            </a>

            <a href="{{ route('pengusaha.pesan') }}"
               class="flex items-center gap-4 border-b border-[#E5EEE2] p-4 transition hover:bg-[#F5F9F2]">

                <span class="text-2xl">💬</span>

                <div class="min-w-0 flex-1">
                    <h4 class="font-bold text-[#1A2A1A]">
                        Pesan
                    </h4>
                    <p class="text-sm text-[#829182]">
                        Negosiasi harga dan percakapan
                    </p>
                </div>

                <span class="text-lg text-[#829182]">›</span>
            </a>

            <a href="{{ route('pengusaha.keranjang') }}"
               class="flex items-center gap-4 p-4 transition hover:bg-[#F5F9F2]">

                <span class="text-2xl">🛒</span>

                <div class="min-w-0 flex-1">
                    <h4 class="font-bold text-[#1A2A1A]">
                        Keranjang Belanja
                    </h4>
                    <p class="text-sm text-[#829182]">
                        Produk yang ingin dibeli
                    </p>
                </div>

                <span class="text-lg text-[#829182]">›</span>
            </a>

        </div>
    </section>

    {{-- BANTUAN --}}
    <section class="mb-6">

        <h3 class="mb-3 text-xs font-bold uppercase tracking-wide text-[#6B806B]">
            Bantuan & Lainnya
        </h3>

        <div class="overflow-hidden rounded-2xl border border-[#DCE8D8] bg-white">

            <a href="{{ route('pengusaha.bantuan') }}"
               class="flex items-center gap-4 p-4 transition hover:bg-[#F5F9F2]">

                <span class="text-2xl">❓</span>

                <div class="min-w-0 flex-1">
                    <h4 class="font-bold text-[#1A2A1A]">
                        Bantuan & Informasi
                    </h4>
                    <p class="text-sm text-[#829182]">
                        WhatsApp, pertanyaan umum, kontak
                    </p>
                </div>

                <span class="text-lg text-[#829182]">›</span>
            </a>

        </div>
    </section>

    {{-- KELUAR --}}
    <form action="{{ route('keluar') }}" method="POST">
        @csrf

        <button type="submit"
                class="w-full rounded-xl border border-red-300 bg-transparent px-5 py-3 font-semibold text-red-600 transition hover:bg-red-50">
            Keluar
        </button>
    </form>

    {{-- FOOTER --}}
    <p class="mt-5 text-center text-xs text-[#829182]">
        TaniLoka v1.0.0 MVP · Dibuat di Bali
    </p>

</div>

@endsection