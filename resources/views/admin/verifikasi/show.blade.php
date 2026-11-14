@extends('layouts.admin')
@section('title', 'Tinjau Verifikasi')

@section('content')
@php
    $u = $verifikasi->user;
    $profil = $u->isPetani() ? $u->profilPetani : $u->profilPengusaha;
@endphp

<a href="{{ route('admin.verifikasi') }}"
    class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 transition hover:text-green-700">
    <span class="text-xl leading-none">‹</span>
    <span>Kembali</span>
</a>

<div class="grid md:grid-cols-3 gap-6 mt-4">
    <div class="bg-white rounded-xl border p-6 space-y-2 text-sm">
        <h2 class="text-lg font-semibold mb-2">{{ $u->name }}</h2>
        <p><span class="text-gray-500">Peran:</span> <span class="capitalize">{{ $u->peran }}</span></p>
        <p><span class="text-gray-500">Email:</span> {{ $u->email ?? '-' }}</p>
        <p><span class="text-gray-500">No. WA:</span> {{ $u->no_wa ?? '-' }}</p>
        @if ($u->isPetani())
            <p><span class="text-gray-500">Usaha tani:</span> {{ $profil?->nama_usaha_tani ?? '-' }}</p>
            <p><span class="text-gray-500">Lokasi:</span> {{ collect([$profil?->desa, $profil?->kecamatan, $profil?->kabupaten])->filter()->join(', ') ?: '-' }}</p>
        @else
            <p><span class="text-gray-500">Nama usaha:</span> {{ $profil?->nama_usaha ?? '-' }}</p>
            <p><span class="text-gray-500">Jenis usaha:</span> {{ $profil?->jenis_usaha ?? '-' }}</p>
        @endif
        <p><span class="text-gray-500">Diajukan:</span> {{ $verifikasi->created_at->translatedFormat('d M Y, H:i') }}</p>
    </div>

    <div class="md:col-span-2 bg-white rounded-xl border p-6">
        <h3 class="font-semibold mb-4">Dokumen</h3>
        <div class="grid sm:grid-cols-2 gap-4">
            @forelse ($verifikasi->bukti as $b)
                <a href="{{ asset('storage/'.$b->path) }}" target="_blank" class="block border rounded-lg overflow-hidden hover:shadow">
                    @if (\Illuminate\Support\Str::endsWith(strtolower($b->path), '.pdf'))
                        <div class="h-40 flex items-center justify-center bg-gray-100 text-gray-500">PDF, klik untuk membuka</div>
                    @else
                        <img src="{{ asset('storage/'.$b->path) }}" alt="{{ $b->jenis }}" class="h-40 w-full object-cover">
                    @endif
                    <p class="px-3 py-2 text-sm capitalize">{{ str_replace('_', ' ', $b->jenis) }}</p>
                </a>
            @empty
                <p class="text-sm text-gray-500">Tidak ada dokumen.</p>
            @endforelse
        </div>
    </div>
</div>

@if ($verifikasi->status === 'menunggu')
    <form method="POST" action="{{ route('admin.verifikasi.putuskan', $verifikasi) }}"
          x-data="{ keputusan: '{{ old('keputusan', 'terverifikasi') }}' }"
          class="bg-white rounded-xl border p-6 mt-6 space-y-4">
        @csrf @method('PATCH')
        <h3 class="font-semibold">Keputusan</h3>

        <div class="flex flex-wrap gap-3">
            @foreach (['terverifikasi' => 'Setujui', 'perlu_perbaikan' => 'Minta Perbaikan', 'ditolak' => 'Tolak'] as $val => $label)
                <label class="flex items-center gap-2 border rounded-lg px-4 py-2 cursor-pointer"
                       :class="keputusan === '{{ $val }}' && 'border-primary bg-primary/5'">
                    <input type="radio" name="keputusan" value="{{ $val }}" x-model="keputusan"> {{ $label }}
                </label>
            @endforeach
        </div>

        <div x-show="keputusan !== 'terverifikasi'" x-cloak>
            <label class="block text-sm mb-1">Catatan untuk pengguna</label>
            <textarea name="catatan_admin" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm"
                      placeholder="Contoh: Foto KTP buram, mohon unggah ulang.">{{ old('catatan_admin') }}</textarea>
            @error('catatan_admin') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <button class="px-5 py-2 rounded-lg text-white"
                :class="keputusan === 'ditolak' ? 'bg-red-600' : 'bg-primary'"
                x-text="keputusan === 'terverifikasi' ? 'Setujui Akun' : (keputusan === 'ditolak' ? 'Tolak Pengajuan' : 'Kirim Permintaan Perbaikan')"></button>
    </form>
@else
    <div class="bg-white rounded-xl border p-6 mt-6 text-sm">
        <p>Sudah ditinjau oleh <b>{{ $verifikasi->peninjau?->name ?? '-' }}</b> pada {{ $verifikasi->ditinjau_pada?->translatedFormat('d M Y, H:i') }}.</p>
        @if ($verifikasi->catatan_admin) <p class="mt-2 text-gray-600">Catatan: {{ $verifikasi->catatan_admin }}</p> @endif
    </div>
@endif
@endsection