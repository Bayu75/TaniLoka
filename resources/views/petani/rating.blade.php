@extends('layouts.petani')

@section('content')

<x-page-header judul="Rating Saya" :kembali="route('petani.akun')" lebar="max-w-3xl"/>

<div class="max-w-3xl mx-auto px-4 py-6 space-y-5 pb-24">

    {{-- Ringkasan --}}
    <section class="bg-white border border-border rounded-2xl p-5 flex gap-6 items-center">
        <div class="text-center shrink-0 w-28">
            <p class="text-4xl font-bold text-gray-900">{{ number_format($rataRata, 1) }}</p>
            <div class="flex justify-center gap-0.5 my-1">
                @for ($i = 1; $i <= 5; $i++)
                    <span class="{{ $i <= round($rataRata) ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                @endfor
            </div>
            <p class="text-xs text-text-muted">{{ $jumlah }} ulasan</p>
        </div>

        <div class="flex-1 space-y-1.5">
            @foreach ($sebaran as $bintang => $s)
                <div class="flex items-center gap-2 text-sm">
                    <span class="w-6 text-text-muted">{{ $bintang }}★</span>
                    <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-yellow-400 rounded-full" style="width: {{ $s['persen'] }}%"></div>
                    </div>
                    <span class="w-6 text-right text-text-muted">{{ $s['total'] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Riwayat Ulasan --}}
    <section>
        <h2 class="font-semibold text-gray-900 mb-3">Riwayat Ulasan</h2>

        @forelse ($ulasan as $u)
            <div class="bg-white border border-border rounded-2xl p-4 mb-3">
                <div class="flex justify-between items-start gap-3">
                    <div>
                        <p class="font-medium text-gray-900">{{ $u->pengusaha->name ?? 'Pembeli' }}</p>
                        <p class="text-xs text-text-muted">
                            Pesanan #{{ $u->pesanan_id }} · {{ $u->created_at->translatedFormat('d M Y') }}
                        </p>
                    </div>
                    <div class="flex gap-0.5 shrink-0">
                        @for ($i = 1; $i <= 5; $i++)
                            <span class="{{ $i <= $u->rating ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                        @endfor
                    </div>
                </div>
                @if ($u->komentar)
                    <p class="text-sm text-gray-700 mt-2">{{ $u->komentar }}</p>
                @endif
            </div>
        @empty
            <div class="bg-white border border-border rounded-2xl p-8 text-center">
                <p class="text-3xl mb-2">⭐</p>
                <p class="font-medium text-gray-900">Belum ada ulasan</p>
                <p class="text-sm text-text-muted">Ulasan muncul setelah pembeli menerima pesanan dan memberi rating.</p>
            </div>
        @endforelse

        {{ $ulasan->links() }}
    </section>
</div>

@endsection