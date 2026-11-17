@extends('layouts.petani')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6 space-y-4 pb-24">
    @if (! $percakapanAktif)
        <div id="pesan-awal">
            <header class="mb-4">
                <h1 class="text-2xl font-extrabold">Pesan</h1>
            </header>
            <p class="text-sm text-text-muted -mt-2">Percakapan dan negosiasi harga dengan pembeli.</p>
        </div>

        @if (session('sukses'))
            <p class="text-sm bg-green-50 text-green-800 border border-green-200 rounded-xl px-4 py-2">{{ session('sukses') }}</p>
        @endif

        @if ($percakapan->isEmpty())
            <p class="text-sm text-text-muted bg-white border border-border rounded-2xl p-6 text-center">Belum ada percakapan dengan pembeli.</p>
        @else
            <div id="daftar-kontak" class="space-y-3">
                @foreach ($percakapan as $p)
                    <a href="{{ route('petani.pesan.show', $p['id']) }}" class="pesan-kontak block bg-white {{ $p['baru'] ? 'border-2 border-yellow-400' : 'border border-border' }} rounded-2xl p-4 hover:border-primary">
                        <div class="flex gap-3">
                            @if ($p['foto'])
                                <div class="w-14 h-14 rounded-xl bg-cover bg-center shrink-0 bg-green-100" style="background-image:url('{{ $p['foto'] }}')"></div>
                            @else
                                <div class="w-14 h-14 rounded-xl bg-green-700 text-white flex items-center justify-center font-bold shrink-0">{{ mb_substr($p['nama'], 0, 1) }}</div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between gap-2 items-start">
                                    <p class="font-semibold text-gray-900">{{ $p['nama'] }}</p>
                                    <div class="flex items-center gap-2 shrink-0">
                                        @if ($p['baru'])
                                            <span class="text-xs bg-yellow-400 text-gray-900 font-semibold px-2 py-0.5 rounded-full">Baru</span>
                                        @endif
                                        @if ($p['waktu'])
                                            <span class="text-xs text-text-muted whitespace-nowrap">{{ $p['waktu'] }}</span>
                                        @endif
                                    </div>
                                </div>
                                @if ($p['produk'])
                                    <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $p['produk'] }}</p>
                                @endif
                                <p class="text-sm text-text-muted mt-0.5 truncate">{{ $p['cuplikan'] }}</p>
                                <span class="inline-block mt-2 text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full">{{ $p['status'] }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    @else
        <div id="chat-{{ $percakapanAktif['id'] }}" class="pesan-detail scroll-mt-24 space-y-4">
            <div class="grid grid-cols-[5rem_minmax(0,1fr)_5rem] items-center gap-2 border-b border-border pb-3">
                <a href="{{ route('petani.pesan') }}" class="inline-flex items-center gap-1 py-2 text-sm font-semibold text-green-700 hover:text-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-700 pesan-kembali justify-self-start">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                    <span>Kembali</span>
                </a>
                <div class="min-w-0 break-words text-center">
                    <p class="font-semibold text-gray-900">{{ $percakapanAktif['nama'] }}</p>
                    @if ($percakapanAktif['produk'])
                        <p class="text-sm text-text-muted">{{ $percakapanAktif['produk'] }}</p>
                    @endif
                </div>
                <span aria-hidden="true"></span>
            </div>

            @if (session('sukses'))
                <p class="text-sm bg-green-50 text-green-800 border border-green-200 rounded-xl px-4 py-2">{{ session('sukses') }}</p>
            @endif

            <div id="thread-{{ $percakapanAktif['id'] }}" class="space-y-4">
                @forelse ($pesan as $m)
                    <div class="max-w-[85%] pesan-bubble {{ $m['milikSaya'] ? 'ml-auto' : '' }}">
                        <div class="{{ $m['milikSaya'] ? 'bg-primary text-white' : 'bg-white border border-border' }} rounded-2xl p-4 space-y-2">
                            <p class="text-sm {{ $m['milikSaya'] ? '' : 'text-gray-800' }} whitespace-pre-wrap break-words">{{ $m['isi'] }}</p>
                        </div>
                        <p class="text-xs text-text-muted mt-1 {{ $m['milikSaya'] ? 'text-right' : '' }}">{{ $m['pengirim'] }} · {{ $m['waktu'] }}</p>
                    </div>
                @empty
                    <p class="text-sm text-text-muted text-center py-6">Belum ada pesan. Tulis balasan pertama di bawah.</p>
                @endforelse
            </div>

            <form method="POST" action="{{ route('petani.pesan.kirim', $percakapanAktif['id']) }}" class="pesan-kirim flex gap-2">
                @csrf
                <input type="text" name="isi" value="{{ old('isi') }}" placeholder="Tulis pesan..." class="flex-1 border border-border rounded-xl px-4 py-2 bg-white" required maxlength="2000">
                <button type="submit" class="bg-primary text-white rounded-xl px-4 py-2 text-sm font-medium">Kirim</button>
            </form>
            @error('isi')
                <p class="text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>
    @endif
</div>
@endsection
