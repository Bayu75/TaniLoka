@extends('layouts.admin')

@section('title', 'Tinjau Verifikasi - TaniLoka')

@section('content')
@php
    $u = $verifikasi->user;
    $isPetani = $u->peran === 'petani';
    $profil = $isPetani ? $u->profilPetani : $u->profilPengusaha;
    $alamatUtama = $u->alamat()->where('utama', true)->first();
    $namaTampil = $isPetani ? $u->name : ($profil?->nama_usaha ?? $u->name);
    $labelJenis = [
        'ktp' => 'Foto KTP', 'foto_lahan' => 'Foto Lahan',
        'nib' => 'NIB / Surat Izin Usaha', 'foto_tempat_usaha' => 'Foto Tempat Usaha',
    ];
    $badge = [
        'menunggu'        => ['Menunggu', 'bg-yellow-100 text-yellow-700'],
        'perlu_perbaikan' => ['Perlu Perbaikan', 'bg-orange-100 text-orange-700'],
        'terverifikasi'   => ['Terverifikasi', 'bg-green-100 text-green-700'],
        'ditolak'         => ['Ditolak', 'bg-red-100 text-red-600'],
    ][$verifikasi->status] ?? [$verifikasi->status, 'bg-gray-100 text-gray-600'];
@endphp

{{-- HEADER HALAMAN --}}
<div class="relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] -mt-4 w-screen border-b border-[#D4E8D0] bg-white">
    <div class="relative mx-auto max-w-5xl px-4 py-3">
        <a href="{{ route('admin.verifikasi') }}"
           class="mb-2 inline-flex items-center gap-1 text-sm font-extrabold text-[#087C3E] hover:text-green-800
                  md:absolute md:right-full md:top-1/2 md:mb-0 md:mr-4 md:-translate-y-1/2 md:whitespace-nowrap">
            <span class="text-2xl font-black leading-none">&lt;</span>
            <span>Kembali</span>
        </a>
        <div>
            <p class="text-sm font-semibold text-[#087C3E]">Verifikasi Akun</p>
            <h1 class="mt-1 text-2xl font-extrabold text-[#1A2A1A]">Tinjau Pengajuan</h1>
            <p class="mt-1 text-sm text-[#6B806B]">Periksa data, alamat, dan dokumen sebelum memberi keputusan.</p>
        </div>
    </div>
</div>

<div class="mx-auto max-w-5xl space-y-6 px-4 py-6">

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-600">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    {{-- IDENTITAS --}}
    <div class="rounded-2xl border border-green-100 bg-white p-5 shadow-sm md:p-7">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-green-100 text-2xl font-bold text-green-700">
                    {{ strtoupper(substr($namaTampil, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">{{ $namaTampil }}</h2>
                    <p class="text-sm text-gray-500">{{ $isPetani ? 'Petani' : 'Pengusaha Kuliner' }} TaniLoka · diajukan {{ $verifikasi->created_at->diffForHumans() }}</p>
                </div>
            </div>
            <span class="rounded-full px-4 py-1.5 text-xs font-bold {{ $badge[1] }}">{{ $badge[0] }}</span>
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2">

        {{-- PROFIL --}}
        <div class="rounded-2xl border border-green-100 bg-white p-5 shadow-sm md:p-7">
            <h3 class="mb-4 font-bold text-gray-900">{{ $isPetani ? 'Data Petani' : 'Informasi Usaha' }}</h3>
            <dl class="space-y-3 text-sm">
                <div><dt class="font-semibold text-gray-500">Nama Pemilik</dt><dd class="text-gray-900">{{ $u->name }}</dd></div>
                @unless ($isPetani)
                    <div><dt class="font-semibold text-gray-500">Nama Usaha</dt><dd class="text-gray-900">{{ $profil?->nama_usaha ?? '-' }}</dd></div>
                    <div><dt class="font-semibold text-gray-500">Jenis Usaha</dt><dd class="text-gray-900">{{ $profil?->jenis_usaha ?? '-' }}</dd></div>
                @endunless
                <div><dt class="font-semibold text-gray-500">Email</dt><dd class="text-gray-900">{{ $u->email ?? '-' }}</dd></div>
                <div><dt class="font-semibold text-gray-500">Nomor WhatsApp</dt><dd class="text-gray-900">{{ $u->no_wa ?? '-' }}</dd></div>
                @unless ($isPetani)
                    <div><dt class="font-semibold text-gray-500">Deskripsi Usaha</dt><dd class="leading-relaxed text-gray-600">{{ $profil?->deskripsi ?: '-' }}</dd></div>
                @endunless
            </dl>
        </div>

        {{-- ALAMAT --}}
        <div class="rounded-2xl border-2 {{ $alamatUtama ? 'border-green-700' : 'border-red-200' }} bg-white p-5 shadow-sm md:p-7">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h3 class="font-bold text-gray-900">Alamat {{ $isPetani ? 'Lahan' : 'Tempat Usaha' }}</h3>
                @if ($alamatUtama)
                    <span class="rounded-full bg-green-100 px-4 py-1.5 text-xs font-bold text-green-700">{{ $alamatUtama->label }}</span>
                @endif
            </div>

            @if ($alamatUtama)
                <div class="space-y-2">
                    <p class="font-semibold text-gray-900">{{ $alamatUtama->nama_penerima }} · {{ $alamatUtama->no_hp }}</p>
                    <p class="text-sm leading-relaxed text-gray-600">{{ $alamatUtama->alamat_lengkap }}</p>
                    <p class="text-sm text-gray-600">{{ $alamatUtama->kabupaten }}</p>
                    @if ($alamatUtama->catatan)
                        <p class="mt-3 text-sm text-gray-500">Catatan: {{ $alamatUtama->catatan }}</p>
                    @endif
                </div>
                @if ($alamatUtama->latitude)
                    <div class="mt-5 border-t border-gray-100 pt-5">
                        <a href="https://www.google.com/maps?q={{ $alamatUtama->latitude }},{{ $alamatUtama->longitude }}"
                           target="_blank" rel="noopener"
                           class="inline-flex rounded-xl border-2 border-green-700 px-5 py-2.5 text-sm font-semibold text-green-700 hover:bg-green-50">
                            📍 Lihat di Google Maps
                        </a>
                    </div>
                @endif
            @else
                <p class="text-sm text-red-600">Pengguna belum mengisi alamat.</p>
            @endif
        </div>
    </div>

    {{-- DOKUMEN --}}
    <div class="rounded-2xl border border-green-100 bg-white p-5 shadow-sm md:p-7">
        <h3 class="mb-4 font-bold text-gray-900">Dokumen & Foto</h3>
        <div class="grid gap-4 sm:grid-cols-2">
            @forelse ($verifikasi->bukti as $b)
                @php $url = asset('storage/' . $b->path); @endphp
                <div class="overflow-hidden rounded-xl border border-gray-200">
                    @if (str_ends_with(strtolower($b->path), '.pdf'))
                        <a href="{{ $url }}" target="_blank" class="flex h-48 items-center justify-center bg-[#F8FAF5] text-sm font-semibold text-green-700 hover:bg-green-50">📄 Buka PDF</a>
                    @else
                        <a href="{{ $url }}" target="_blank"><img src="{{ $url }}" alt="{{ $labelJenis[$b->jenis] ?? $b->jenis }}" class="h-48 w-full object-cover"></a>
                    @endif
                    <p class="border-t border-gray-100 px-4 py-3 text-sm font-semibold text-gray-700">{{ $labelJenis[$b->jenis] ?? $b->jenis }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-500">Belum ada dokumen yang diunggah.</p>
            @endforelse
        </div>
    </div>

    {{-- KEPUTUSAN --}}
    <div class="rounded-2xl border border-green-100 bg-white p-5 shadow-sm md:p-7">
        @if ($verifikasi->status === 'menunggu')
            <form action="{{ route('admin.verifikasi.putuskan', $verifikasi) }}" method="POST"
                  x-data="{ keputusan: '{{ old('keputusan', 'terverifikasi') }}' }" class="space-y-5">
                @csrf
                @method('PATCH')

                <h3 class="font-bold text-gray-900">Keputusan</h3>

                <div class="grid gap-3 sm:grid-cols-3">
                    @foreach (['terverifikasi' => 'Setujui', 'perlu_perbaikan' => 'Minta Perbaikan', 'ditolak' => 'Tolak'] as $nilai => $label)
                        <label class="cursor-pointer rounded-xl border-2 px-4 py-3 text-center text-sm font-semibold transition"
                               :class="keputusan === '{{ $nilai }}'
                                   ? '{{ $nilai === 'ditolak' ? 'border-red-500 bg-red-50 text-red-600' : 'border-green-700 bg-green-50 text-green-700' }}'
                                   : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                            <input type="radio" name="keputusan" value="{{ $nilai }}" x-model="keputusan" class="sr-only">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>

                <div x-show="keputusan !== 'terverifikasi'" x-cloak>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Catatan untuk pengguna *</label>
                    <textarea name="catatan_admin" rows="3"
                              placeholder="Contoh: Foto tempat usaha buram, mohon unggah ulang."
                              class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100">{{ old('catatan_admin') }}</textarea>
                </div>

                <div class="flex justify-end border-t border-gray-100 pt-5">
                    <button type="submit"
                            class="rounded-xl px-6 py-3 text-sm font-semibold text-white transition"
                            :class="keputusan === 'ditolak' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-700 hover:bg-green-800'"
                            x-text="{ terverifikasi: 'Setujui Akun', perlu_perbaikan: 'Kirim Permintaan Perbaikan', ditolak: 'Tolak Pengajuan' }[keputusan]">
                        Setujui Akun
                    </button>
                </div>
            </form>
        @else
            <h3 class="mb-2 font-bold text-gray-900">Keputusan</h3>
            <p class="text-sm text-gray-600">
                Ditinjau oleh <b>{{ $verifikasi->peninjau?->name ?? '-' }}</b>
                pada {{ $verifikasi->ditinjau_pada?->translatedFormat('d F Y, H:i') ?? '-' }}.
            </p>
            @if ($verifikasi->catatan_admin)
                <p class="mt-2 text-sm text-gray-500">Catatan: {{ $verifikasi->catatan_admin }}</p>
            @endif
        @endif
    </div>
</div>
@endsection