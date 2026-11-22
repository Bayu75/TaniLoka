@extends('layouts.petani')

@section('content')
<div class="mx-auto max-w-4xl space-y-5 px-4 pb-6">
  <div>
    <x-page-header judul="Kelola Produk" :kembali="false" lebar="max-w-4xl" />
    <p class=" flex justify-center mt-1 text-sm text-text-muted pt-4">Atur produk yang Anda jual</p>
  </div>

  @if (session('sukses'))
    <div class="rounded-xl border border-green-200 bg-green-50 p-3 text-sm text-green-800" role="status">
      {{ session('sukses') }}
    </div>
  @endif
  @if (session('gagal'))
    <div class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700" role="alert">
      {{ session('gagal') }}
    </div>
  @endif

  @unless ($terverifikasi)
    <div class="rounded-2xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900" role="status">
      <p class="font-semibold">Akun Anda belum terverifikasi</p>
      <p class="mt-1">
        Produk baru bisa ditambah atau diubah setelah admin menyetujui verifikasi akun
        (status saat ini: {{ str_replace('_', ' ', $statusVerifikasi) }}).
      </p>
    </div>
  @endunless

  {{-- Angka ringkasan --}}
  <div class="grid grid-cols-3 gap-3">
    @foreach ([['Jumlah produk', $stat['jumlah']], ['Aktif', $stat['aktif']], ['Stok habis', $stat['habis']]] as [$label, $nilai])
      <div class="rounded-2xl border border-border bg-white p-4 text-center">
        <p class="text-2xl font-semibold text-gray-900">{{ $nilai }}</p>
        <p class="text-sm text-text-muted">{{ $label }}</p>
      </div>
    @endforeach
  </div>

  {{-- Tombol tambah --}}
  @if ($terverifikasi)
    <a href="{{ route('petani.produk.create') }}"
       class="block w-full rounded-xl bg-yellow-400 py-3 text-center font-semibold text-gray-900 hover:bg-yellow-500">
      + Tambah Produk Baru
    </a>
  @else
    <button type="button" disabled
            class="w-full cursor-not-allowed rounded-xl bg-yellow-400 py-3 font-semibold text-gray-900 opacity-50">
      + Tambah Produk Baru
    </button>
  @endif

  {{-- Daftar produk --}}
  @if ($produk->isEmpty())
    <p class="rounded-2xl border border-border bg-white p-6 text-center text-sm text-text-muted">
      Belum ada produk. Tekan "Tambah Produk Baru" untuk mulai berjualan.
    </p>
  @else
    <div class="grid gap-4 md:grid-cols-2">
      @foreach ($produk as $p)
        @php
          $kg = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, ',', '.'), '0'), ',') . ' kg';
          $sampai = $p->tersedia_sampai?->copy()->startOfDay();
          $sisa = $sampai ? (int) today()->diffInDays($sampai, false) : null;
          $peringatan = match (true) {
              $sisa === null || $sisa > 3 => null,
              $sisa < 0 => 'Sudah kedaluwarsa',
              $sisa === 0 => 'Kedaluwarsa hari ini',
              default => "Mendekati kedaluwarsa: {$sisa} hari lagi",
          };
          $baris = array_filter([
              'Nama Produk'        => $p->nama,
              'Kategori'           => $p->kategori?->nama ?? '-',
              'Harga (per kg)'     => 'Rp ' . number_format($p->harga_per_kg, 0, ',', '.') . ' / kg',
              'Stok (kg)'          => $kg($p->stok_kg),
              'Minimal pesan (kg)' => $kg($p->min_order_kg),
              'Kabupaten'          => $kabupatenPetani ?: 'Belum diatur di Akun',
              'Tanggal Panen'      => $p->tanggal_panen?->translatedFormat('j F Y'),
              'Grade'              => $p->grade,
              'Kedaluwarsa'        => $sampai ? $sampai->translatedFormat('j F Y') : 'Belum diatur',
          ], fn ($v) => $v !== null && $v !== '');
        @endphp

        <article class="overflow-hidden rounded-2xl border border-border bg-white">
          @if ($p->fotoUtama)
            <img src="{{ $p->fotoUtama->url }}" alt="{{ $p->nama }}" class="h-40 w-full object-cover">
          @endif

          <div class="space-y-2 p-4 text-sm">
            @foreach ($baris as $label => $nilai)
              <div class="flex justify-between gap-2">
                <span class="text-text-muted">{{ $label }}</span>
                <span class="max-w-[60%] text-right font-medium text-gray-900">{{ $nilai }}</span>
              </div>
            @endforeach

            @if ($p->deskripsi)
              <p class="rounded-lg bg-gray-50 px-3 py-2 text-text-muted">{{ $p->deskripsi }}</p>
            @endif

            @if ($peringatan)
              <p @class([
                    'rounded-lg border px-3 py-2',
                    'border-red-200 bg-red-50 text-red-700' => $sisa < 0,
                    'border-amber-200 bg-amber-50 text-amber-800' => $sisa >= 0,
                 ]) role="status">
                {{ $peringatan }}
              </p>
            @endif

            {{-- Sakelar tersedia --}}
            <form method="POST" action="{{ route('petani.produk.tersedia', $p) }}"
                  class="flex items-center justify-between pt-2">
              @csrf
              @method('PATCH')
              <input type="hidden" name="aktif" value="{{ $p->aktif ? 0 : 1 }}">
              <span class="{{ $p->aktif ? 'text-primary' : 'text-text-muted' }}">
                {{ $p->aktif ? 'Tersedia' : 'Tidak tersedia' }}
              </span>
              <button type="submit" role="switch" aria-checked="{{ $p->aktif ? 'true' : 'false' }}"
                      aria-label="Ketersediaan produk" @disabled(! $terverifikasi)
                      class="relative h-6 w-11 rounded-full disabled:cursor-not-allowed disabled:opacity-50 {{ $p->aktif ? 'bg-primary' : 'bg-gray-300' }}">
                <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition {{ $p->aktif ? 'translate-x-5' : '' }}"></span>
              </button>
            </form>

            @if ($terverifikasi)
              <a href="{{ route('petani.produk.edit', $p) }}"
                 class="mt-2 block w-full rounded-xl border border-green-700 py-2 text-center font-medium text-green-700 hover:bg-green-50">
                Ubah Produk
              </a>
            @endif

            <form method="POST" action="{{ route('petani.produk.destroy', $p) }}"
                  onsubmit="return confirm(@js('Hapus produk ' . $p->nama . '?'))">
              @csrf
              @method('DELETE')
              <button type="submit" class="mt-2 w-full py-2 font-medium text-red-600">Hapus Produk</button>
            </form>
          </div>
        </article>
      @endforeach
    </div>
  @endif
</div>
@endsection