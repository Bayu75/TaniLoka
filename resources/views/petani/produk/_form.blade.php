{{-- Dipakai oleh create.blade.php dan edit.blade.php --}}
@php
  $edit      = $produk->exists;
  $label     = 'block text-sm font-semibold text-gray-900';
  $input     = 'mt-2 block w-full rounded-xl border border-border bg-white px-4 py-3 text-sm focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20';
  $kartu     = 'space-y-4 rounded-2xl border border-border bg-white p-5';
  $fotoLama  = $produk->fotoUtama?->url ?? '';
  $tglPanen  = $produk->tanggal_panen?->toDateString() ?? '';
  $tglSampai = $produk->tersedia_sampai?->toDateString() ?? '';
  $shortcut  = [3 => '3 hari', 7 => '1 minggu', 14 => '2 minggu'];
  $angka     = fn ($n, $bawaan) => $n === null ? $bawaan : rtrim(rtrim((string) $n, '0'), '.');
@endphp

@if ($errors->any())
  <div class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700" role="alert">
    Ada isian yang belum benar. Periksa kolom yang ditandai merah.
  </div>
@endif

{{-- 1. Foto & Nama --}}
<section class="{{ $kartu }}">
  <h2 class="text-lg font-bold">Foto & Nama</h2>

  <div x-data="{ preview: @js($fotoLama) }">
    <label for="foto" class="{{ $label }}">
      Foto Produk <span class="font-normal text-text-muted">(boleh dikosongkan)</span>
    </label>
    <input id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/webp,image/gif"
           @change="const f = $event.target.files[0]; if (f) preview = URL.createObjectURL(f)"
           class="mt-2 block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-green-50 file:px-4 file:py-2 file:font-semibold file:text-green-700">
    <p class="mt-1 text-xs text-text-muted">JPG, PNG, WebP, atau GIF. Maksimal 5 MB.</p>
    @error('foto') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    <template x-if="preview">
      <img :src="preview" alt="Pratinjau foto produk" class="mt-3 h-40 w-full rounded-xl object-cover">
    </template>
  </div>

  <div>
    <label for="nama" class="{{ $label }}">Nama Produk *</label>
    <input id="nama" name="nama" type="text" required maxlength="100"
           value="{{ old('nama', $produk->nama) }}" placeholder="Contoh: Tomat Cherry Segar" class="{{ $input }}">
    @error('nama') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
  </div>
</section>

{{-- 2. Harga & Stok --}}
<section class="{{ $kartu }}">
  <h2 class="text-lg font-bold">Harga & Stok</h2>

  <div>
    <label for="kategori_id" class="{{ $label }}">Kategori *</label>
    <select id="kategori_id" name="kategori_id" required class="{{ $input }}">
      @foreach ($kategori as $k)
        <option value="{{ $k->id }}" @selected(old('kategori_id', $produk->kategori_id) == $k->id)>{{ $k->nama }}</option>
      @endforeach
    </select>
    @error('kategori_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
  </div>

  <div>
    <label for="grade" class="{{ $label }}">Grade <span class="font-normal text-text-muted">(opsional)</span></label>
    <select id="grade" name="grade" class="{{ $input }}">
      <option value="">Tanpa grade</option>
      @foreach (['A', 'B', 'C'] as $g)
        <option value="{{ $g }}" @selected(old('grade', $produk->grade) === $g)>{{ $g }}</option>
      @endforeach
    </select>
    @error('grade') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
  </div>

  <div class="grid gap-4 sm:grid-cols-3">
    @foreach ([
        'harga_per_kg' => ['Harga per kg (Rp) *', $produk->harga_per_kg ?? 10000, '1', '1'],
        'stok_kg'      => ['Stok (kg) *', $angka($produk->stok_kg, 50), '0.01', '0.01'],
        'min_order_kg' => ['Minimal pesan (kg) *', $angka($produk->min_order_kg, 5), '0.01', '0.01'],
    ] as $nama => [$teks, $nilai, $step, $min])
      <div>
        <label for="{{ $nama }}" class="{{ $label }}">{{ $teks }}</label>
        <input id="{{ $nama }}" name="{{ $nama }}" type="number" min="{{ $min }}" step="{{ $step }}"
               inputmode="decimal" required value="{{ old($nama, $nilai) }}" class="{{ $input }}">
        @error($nama) <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>
    @endforeach
  </div>
</section>

{{-- 3. Lokasi & Panen --}}
<section class="{{ $kartu }}">
  <h2 class="text-lg font-bold">Lokasi & Panen</h2>

  <div>
    <p class="{{ $label }}">Kabupaten / Kota</p>
    <p class="{{ $input }} bg-gray-50 text-text-muted">{{ $kabupatenPetani ?: 'Belum diatur' }}</p>
    <p class="mt-1 text-xs text-text-muted">
      Lokasi mengikuti alamat utama Anda.
      <a href="{{ route('petani.alamat') }}" class="font-semibold text-green-700 underline">Ubah alamat</a>
    </p>
  </div>

  <div>
    <label for="tanggal_panen" class="{{ $label }}">Tanggal Panen <span class="font-normal text-text-muted">(opsional)</span></label>
    <input id="tanggal_panen" name="tanggal_panen" type="date"
           value="{{ old('tanggal_panen', $tglPanen) }}" class="{{ $input }}">
    @error('tanggal_panen') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
  </div>

  <div>
    <label for="deskripsi" class="{{ $label }}">Deskripsi <span class="font-normal text-text-muted">(opsional)</span></label>
    <textarea id="deskripsi" name="deskripsi" rows="3" maxlength="1000"
              placeholder="Contoh: Panen tiap 2 minggu, tanpa pestisida"
              class="{{ $input }}">{{ old('deskripsi', $produk->deskripsi) }}</textarea>
    @error('deskripsi') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
  </div>
</section>

{{-- 4. Tersedia sampai --}}
<section class="{{ $kartu }}" x-data="{ sampai: @js(old('tersedia_sampai', $tglSampai)) }">
  <h2 class="text-lg font-bold">Tersedia sampai {{ $edit ? '' : '*' }}</h2>

  <div class="flex gap-2">
    @foreach ($shortcut as $hari => $teks)
      @php $tgl = today()->addDays($hari)->toDateString(); @endphp
      <button type="button" @click="sampai = '{{ $tgl }}'"
              :class="sampai === '{{ $tgl }}' ? 'border-green-700 bg-green-700 text-white' : 'border-border bg-white'"
              class="flex-1 rounded-xl border py-3 text-sm font-medium">
        {{ $teks }}
      </button>
    @endforeach
  </div>

  <div>
    <label for="tersedia_sampai" class="text-sm text-text-muted">Atau pilih tanggal di kalender</label>
    <input id="tersedia_sampai" name="tersedia_sampai" type="date" x-model="sampai"
           @unless ($edit) min="{{ today()->toDateString() }}" required @endunless
           class="{{ $input }}">
    @if ($edit)
      <p class="mt-1 text-xs text-text-muted">Kosongkan jika belum diatur.</p>
    @endif
    @error('tersedia_sampai') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
  </div>
</section>

{{-- 5. Status (hanya saat ubah) --}}
@if ($edit)
  <section class="{{ $kartu }}">
    <label class="flex items-center justify-between gap-3">
      <span class="text-sm font-semibold text-gray-900">Tampilkan di katalog (tersedia)</span>
      <input type="hidden" name="aktif" value="0">
      <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $produk->aktif))
             class="h-5 w-5 rounded border-border text-green-700 focus:ring-green-700">
    </label>
  </section>
@endif

{{-- Tombol --}}
<div class="flex gap-3">
  <a href="{{ route('petani.produk.index') }}"
     class="flex-1 rounded-xl border border-border bg-white py-3 text-center font-semibold">Batal</a>
  <button type="submit" class="flex-1 rounded-xl bg-yellow-400 py-3 font-semibold text-gray-900 hover:bg-yellow-500">
    {{ $tombol }}
  </button>
</div>