@extends('layouts.admin')
@section('title', 'Verifikasi Akun')

@section('content')
@php
    $tab = ['menunggu' => 'Menunggu', 'perlu_perbaikan' => 'Perlu Perbaikan', 'terverifikasi' => 'Terverifikasi', 'ditolak' => 'Ditolak', 'semua' => 'Semua'];
    $badge = ['menunggu' => 'bg-yellow-100 text-yellow-700', 'perlu_perbaikan' => 'bg-orange-100 text-orange-700', 'terverifikasi' => 'bg-green-100 text-green-700', 'ditolak' => 'bg-red-100 text-red-700'];
@endphp

<h1 class="text-2xl font-semibold mb-6">Verifikasi Akun</h1>

<div class="flex flex-wrap gap-2 mb-6">
    @foreach ($tab as $key => $label)
        <a href="{{ route('admin.verifikasi', ['status' => $key]) }}"
           class="px-4 py-2 rounded-full text-sm {{ $status === $key ? 'bg-primary text-white' : 'bg-white border hover:bg-gray-100' }}">
            {{ $label }}
            @if ($key !== 'semua') <span class="ml-1 opacity-75">({{ $jumlah[$key] ?? 0 }})</span> @endif
        </a>
    @endforeach
</div>

<div class="bg-white rounded-xl border overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-gray-500">
            <tr>
                <th class="px-4 py-3">Nama</th>
                <th class="px-4 py-3">Peran</th>
                <th class="px-4 py-3">Diajukan</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse ($daftar as $v)
                <tr>
                    <td class="px-4 py-3">
                        <p class="font-medium">{{ $v->user->name }}</p>
                        <p class="text-gray-500">{{ $v->user->email ?? $v->user->no_wa }}</p>
                    </td>
                    <td class="px-4 py-3 capitalize">{{ $v->user->peran }}</td>
                    <td class="px-4 py-3">{{ $v->created_at->translatedFormat('d M Y, H:i') }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs {{ $badge[$v->status] ?? '' }}">{{ $tab[$v->status] ?? $v->status }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.verifikasi.show', $v) }}" class="text-primary font-medium hover:underline">
                            {{ $v->status === 'menunggu' ? 'Tinjau' : 'Lihat' }}
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-10 text-center text-gray-500">Belum ada pengajuan di kategori ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $daftar->links() }}</div>
@endsection