@extends('layouts.admin')

@section('title', 'Verifikasi Akun - TaniLoka')

@section('content')
@php
    $tab = [
        'menunggu'        => 'Menunggu',
        'perlu_perbaikan' => 'Perlu Perbaikan',
        'terverifikasi'   => 'Terverifikasi',
        'ditolak'         => 'Ditolak',
        'semua'           => 'Semua',
    ];
    $badge = [
        'menunggu'        => ['Menunggu', 'bg-yellow-100 text-yellow-700'],
        'perlu_perbaikan' => ['Perlu Perbaikan', 'bg-orange-100 text-orange-700'],
        'terverifikasi'   => ['Terverifikasi', 'bg-green-100 text-green-700'],
        'ditolak'         => ['Ditolak', 'bg-red-100 text-red-600'],
    ];
    $aktif = request('status', 'menunggu');
@endphp

{{-- HEADER HALAMAN --}}
<div class="relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] -mt-4 w-screen border-b border-[#D4E8D0] bg-white">
    <div class="mx-auto max-w-5xl px-4 py-3">
        <p class="text-sm font-semibold text-[#087C3E]">Admin</p>
        <h1 class="mt-1 text-2xl font-extrabold text-[#1A2A1A]">Verifikasi Akun</h1>
        <p class="mt-1 text-sm text-[#6B806B]">Tinjau pengajuan verifikasi petani dan pengusaha kuliner.</p>
    </div>
</div>

<div class="mx-auto max-w-5xl space-y-6 px-4 py-6">

    {{-- TAB STATUS --}}
    <div class="flex gap-2 overflow-x-auto pb-1">
        @foreach ($tab as $nilai => $label)
            @php $jml = $nilai === 'semua' ? $jumlah->sum() : ($jumlah[$nilai] ?? 0); @endphp
            <a href="{{ route('admin.verifikasi', ['status' => $nilai]) }}"
               @class([
                   'inline-flex shrink-0 items-center gap-2 rounded-xl border-2 px-4 py-2.5 text-sm font-semibold transition',
                   'border-green-700 bg-green-50 text-green-700' => $aktif === $nilai,
                   'border-gray-200 bg-white text-gray-600 hover:bg-gray-50' => $aktif !== $nilai,
               ])>
                {{ $label }}
                <span @class([
                    'rounded-full px-2 py-0.5 text-xs font-bold',
                    'bg-green-700 text-white' => $aktif === $nilai,
                    'bg-gray-100 text-gray-500' => $aktif !== $nilai,
                ])>{{ $jml }}</span>
            </a>
        @endforeach
    </div>

    {{-- DAFTAR PENGAJUAN --}}
    @if ($daftar->isEmpty())
        <div class="rounded-2xl border border-green-100 bg-white p-10 text-center shadow-sm">
            <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-2xl bg-green-100 text-3xl">✓</div>
            <h2 class="font-bold text-gray-900">Tidak ada pengajuan</h2>
            <p class="mt-1 text-sm text-gray-500">Belum ada pengajuan dengan status "{{ $tab[$aktif] ?? $aktif }}".</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($daftar as $v)
                @php
                    $u = $v->user;
                    $isPetani = $u->peran === 'petani';
                    $nama = $isPetani ? $u->name : ($u->profilPengusaha?->nama_usaha ?? $u->name);
                    [$teksBadge, $warnaBadge] = $badge[$v->status] ?? [$v->status, 'bg-gray-100 text-gray-600'];
                @endphp

                <a href="{{ route('admin.verifikasi.show', $v) }}"
                   class="flex flex-col gap-4 rounded-2xl border border-green-100 bg-white p-5 shadow-sm transition hover:border-green-700 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-green-100 text-lg font-bold text-green-700">
                            {{ strtoupper(substr($nama, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="truncate font-bold text-gray-900">{{ $nama }}</p>
                            <p class="truncate text-sm text-gray-500">{{ $u->email ?? $u->no_wa }}</p>
                            <p class="mt-0.5 text-xs text-gray-400">
                                {{ $isPetani ? 'Petani' : 'Pengusaha Kuliner' }} · diajukan {{ $v->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 sm:justify-end">
                        <span class="rounded-full px-4 py-1.5 text-xs font-bold {{ $warnaBadge }}">{{ $teksBadge }}</span>
                        <span class="rounded-xl border-2 border-green-700 px-4 py-2 text-sm font-semibold text-green-700">
                            {{ $v->status === 'menunggu' ? 'Tinjau' : 'Lihat' }}
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

        <div>{{ $daftar->withQueryString()->links() }}</div>
    @endif
</div>
@endsection