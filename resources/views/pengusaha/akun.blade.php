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
<x-page-header judul="Akun" :kembali="false" lebar="max-w-3xl" />

{{-- KONTEN HALAMAN AKUN --}}
<div class="mx-auto max-w-3xl px-4 py-5 md:py-6">


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

                               <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#E8F5E9] text-[#2E7D32]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l1.5-5h15L21 9" />
                        <path d="M3 9a3 3 0 0 0 6 0a3 3 0 0 0 6 0a3 3 0 0 0 6 0" />
                        <path d="M5 11.5V20h14v-8.5" />
                        <path d="M10 20v-4h4v4" />
                    </svg>
                </span>

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

                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#E8F5E9] text-[#2E7D32]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z" />
                        <circle cx="12" cy="9.5" r="2.5" />
                    </svg>
                </span>

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

                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#E8F5E9] text-[#2E7D32]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="4" width="14" height="17" rx="2" />
                        <path d="M9 4V3h6v1" />
                        <path d="M9 10h6M9 14h6M9 18h3" />
                    </svg>
                </span>

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

                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#E8F5E9] text-[#2E7D32]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 5h16v11H9l-5 4z" />
                        <path d="M8 9h8M8 12h5" />
                    </svg>
                </span>

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

                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#E8F5E9] text-[#2E7D32]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 4h2l2.4 11h11L21 8H6.2" />
                        <circle cx="9" cy="19" r="1.5" />
                        <circle cx="17" cy="19" r="1.5" />
                    </svg>
                </span>

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

                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#E8F5E9] text-[#2E7D32]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .8-1 1.5v.7" />
                        <path d="M12 17h.01" />
                    </svg>
                </span>
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