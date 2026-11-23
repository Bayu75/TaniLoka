@extends('layouts.admin')

@section('title', 'Pencairan - TaniLoka')

@section('content')
@php
    $tab = [
        'siap_dicairkan'  => 'Siap Dicairkan',
        'tertahan'        => 'Tertahan',
        'sudah_dicairkan' => 'Sudah Dicairkan',
        'dibatalkan'      => 'Dibatalkan / Refund',
        'semua'           => 'Semua',
    ];
    $badge = [
        'tertahan'        => ['Tertahan', 'bg-yellow-100 text-yellow-700'],
        'siap_dicairkan'  => ['Siap Dicairkan', 'bg-orange-100 text-orange-700'],
        'sudah_dicairkan' => ['Sudah Dicairkan', 'bg-green-100 text-green-700'],
        'dibatalkan'      => ['Dibatalkan', 'bg-red-100 text-red-600'],
    ];
    $aktif = request('status', 'siap_dicairkan');
    $rp = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');
@endphp

{{-- HEADER HALAMAN --}}
<div class="relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] -mt-4 w-screen border-b border-[#D4E8D0] bg-white">
    <div class="mx-auto max-w-5xl px-4 py-3">
        <p class="text-sm font-semibold text-[#087C3E]">Admin</p>
        <h1 class="mt-1 text-2xl font-extrabold text-[#1A2A1A]">Pencairan Petani</h1>
        <p class="mt-1 text-sm text-[#6B806B]">Bagian petani dari pesanan yang dibayar lewat Midtrans. Transfer manual, lalu tandai di sini.</p>
    </div>
</div>

<div class="mx-auto max-w-5xl space-y-6 px-4 py-6">

    {{-- RINGKASAN NOMINAL --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-2xl border border-orange-100 bg-white p-4">
            <p class="text-xs font-semibold text-orange-700">Siap dicairkan</p>
            <p class="mt-1 text-xl font-extrabold text-gray-900">{{ $rp($nominal['siap_dicairkan'] ?? 0) }}</p>
        </div>
        <div class="rounded-2xl border border-yellow-100 bg-white p-4">
            <p class="text-xs font-semibold text-yellow-700">Tertahan (pesanan belum selesai)</p>
            <p class="mt-1 text-xl font-extrabold text-gray-900">{{ $rp($nominal['tertahan'] ?? 0) }}</p>
        </div>
        <div class="rounded-2xl border border-green-100 bg-white p-4">
            <p class="text-xs font-semibold text-green-700">Sudah dicairkan</p>
            <p class="mt-1 text-xl font-extrabold text-gray-900">{{ $rp($nominal['sudah_dicairkan'] ?? 0) }}</p>
        </div>
    </div>

    {{-- TAB STATUS --}}
    <div class="flex gap-2 overflow-x-auto pb-1">
        @foreach ($tab as $nilai => $label)
            @php $jml = $nilai === 'semua' ? $jumlah->sum() : ($jumlah[$nilai] ?? 0); @endphp
            <a href="{{ route('admin.pencairan', ['status' => $nilai]) }}"
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

    {{-- DAFTAR PENCAIRAN --}}
    @if ($daftar->isEmpty())
        <div class="rounded-2xl border border-green-100 bg-white p-10 text-center shadow-sm">
            <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-2xl bg-green-100 text-3xl">✓</div>
            <h2 class="font-bold text-gray-900">Tidak ada data</h2>
            <p class="mt-1 text-sm text-gray-500">Belum ada pencairan dengan status "{{ $tab[$aktif] ?? $aktif }}".</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($daftar as $c)
                @php
                    $petani = $c->petani;
                    $profil = $petani?->profilPetani;
                    $nama = $profil?->nama_usaha_tani ?: ($petani?->name ?? 'Petani');
                    [$teksBadge, $warnaBadge] = $badge[$c->status] ?? [$c->status, 'bg-gray-100 text-gray-600'];
                    $statusBayar = $c->pesanan?->pembayaran?->status;
                @endphp

                <div class="rounded-2xl border border-green-100 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <p class="font-bold text-gray-900">{{ $nama }}</p>
                            <p class="text-sm text-gray-500">
                                Pesanan #{{ $c->pesanan?->kode ?? $c->pesanan_id }}
                                @if ($c->siap_pada) · siap {{ $c->siap_pada->format('d M Y') }} @endif
                            </p>
                            <p class="mt-2 text-sm text-gray-600">
                                {{ $profil?->nama_bank ?? 'Bank belum diisi' }}
                                · {{ $profil?->no_rekening ?? '-' }}
                                · a.n. {{ $profil?->atas_nama_rekening ?? '-' }}
                            </p>
                        </div>

                        <div class="flex flex-col items-start gap-2 sm:items-end">
                            <span class="rounded-full px-4 py-1.5 text-xs font-bold {{ $warnaBadge }}">{{ $teksBadge }}</span>
                            <p class="text-xl font-extrabold text-green-700">{{ $rp($c->bagian_petani) }}</p>
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-2 border-t border-green-50 pt-3 text-sm text-gray-600 sm:grid-cols-4">
                        <p>Subtotal produk<br><span class="font-semibold text-gray-900">{{ $rp($c->subtotal) }}</span></p>
                        <p>Komisi ({{ rtrim(rtrim(number_format($c->komisi_persen, 2, ',', ''), '0'), ',') }}%)<br><span class="font-semibold text-gray-900">- {{ $rp($c->komisi) }}</span></p>
                        <p>Ongkir<br><span class="font-semibold text-gray-900">+ {{ $rp($c->ongkir) }}</span></p>
                        <p>Bagian petani<br><span class="font-semibold text-gray-900">{{ $rp($c->bagian_petani) }}</span></p>
                    </div>

                    @if ($c->catatan)
                        <p class="mt-3 rounded-xl bg-gray-50 p-3 text-xs text-gray-600">{{ $c->catatan }}</p>
                    @endif

                    @if ($c->status === 'siap_dicairkan')
                        <form method="POST" action="{{ route('admin.pencairan.transfer', $c) }}"
                              class="mt-3 flex flex-col gap-2 sm:flex-row"
                              onsubmit="return confirm('Tandai sudah ditransfer {{ $rp($c->bagian_petani) }} ke {{ e($nama) }}?')">
                            @csrf
                            @method('PATCH')
                            <input type="text" name="catatan" maxlength="200" placeholder="Catatan (opsional), mis. no. referensi transfer"
                                   class="min-h-11 flex-1 rounded-xl border border-gray-300 px-3 text-sm">
                            <button type="submit"
                                    class="min-h-11 rounded-xl bg-green-700 px-4 text-sm font-bold text-white transition hover:bg-green-800">
                                Tandai sudah ditransfer
                            </button>
                        </form>
                    @elseif ($c->status === 'sudah_dicairkan')
                        <p class="mt-3 text-xs text-gray-500">
                            Ditransfer {{ $c->dicairkan_pada?->format('d M Y, H.i') }}
                            @if ($c->pencair) oleh {{ $c->pencair->name }} @endif
                        </p>
                    @elseif ($c->status === 'dibatalkan' && $statusBayar === 'perlu_refund')
                        <form method="POST" action="{{ route('admin.pencairan.refund', $c) }}"
                              class="mt-3"
                              onsubmit="return confirm('Yakin dana sudah dikembalikan ke pembeli?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="min-h-11 rounded-xl border-2 border-red-300 px-4 text-sm font-bold text-red-600 transition hover:bg-red-50">
                                Tandai refund ke pembeli selesai
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>

        <div>{{ $daftar->withQueryString()->links() }}</div>
    @endif
</div>
@endsection