@extends('layouts.petani')

@section('content')
{{-- Data dari Petani\AkunController (tabel users + profil_petani). --}}
<div class="max-w-3xl mx-auto px-4 py-6 space-y-5 pb-24"
    x-data="{ formProfil: {{ $errors->any() ? 'true' : 'false' }}, pratinjau: null, pesan: @js(session('sukses') ?? session('gagal') ?? '') }"
    x-init="if (pesan) setTimeout(() => pesan = '', 3000)">
    <header class="mb-4">
        <h1 class="text-2xl font-extrabold">Akun</h1>
    </header>

    <div class="bg-primary text-white rounded-2xl p-4 flex items-center gap-4">
        @if ($akun['foto'])
            <img src="{{ $akun['foto'] }}" alt="Foto profil" class="w-14 h-14 rounded-xl object-cover bg-yellow-400">
        @else
            <div class="w-14 h-14 rounded-xl bg-yellow-400 text-primary flex items-center justify-center text-2xl font-bold">
                {{ mb_strtoupper(mb_substr($akun['nama'] ?: 'P', 0, 1)) }}
            </div>
        @endif
        <div class="flex-1">
            <p class="font-semibold text-lg">{{ $akun['nama'] }}</p>
            <p class="text-sm text-white/90">Petani{{ $profil['kabupaten'] ? ' · ' . $profil['kabupaten'] . ', Bali' : '' }}</p>
        </div>
        <span class="text-xs bg-white/20 px-2.5 py-1 rounded-full flex items-center gap-1">{{ $akun['status_verifikasi'] === 'terverifikasi' ? '✓ ' : '' }}{{ $akun['label_verifikasi'] }}</span>
    </div>

    @if ($akun['status_verifikasi'] !== 'terverifikasi')
        <div class="rounded-2xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900" role="status">
            Akun belum terverifikasi. Anda belum bisa menambah atau mengubah produk sampai admin menyetujui verifikasi.
        </div>
    @endif

    <div>
        <button type="button" @click="formProfil = !formProfil" :aria-expanded="formProfil"
            class="w-full bg-white border border-border rounded-2xl p-4 flex items-center gap-3 text-left">
            <span class="text-xl">👤</span>
            <div class="flex-1">
                <p class="font-semibold text-gray-900">Ubah Profil &amp; Usaha Tani</p>
                <p class="text-sm text-text-muted">Nama, WhatsApp, foto, lokasi kebun, dan rekening</p>
            </div>
            <span class="text-text-muted" x-text="formProfil ? '˄' : '›'"></span>
        </button>

        <form x-show="formProfil" x-cloak method="POST" action="{{ route('petani.akun.simpan') }}" enctype="multipart/form-data"
            class="mt-3 bg-white border border-border rounded-2xl p-4 space-y-3">
            @csrf
            <input type="hidden" name="aksi" value="profil">

            @if ($errors->any())
                <div class="rounded-xl bg-red-50 p-3 text-sm text-red-700" role="alert">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium mb-1" for="akun-foto">Foto Profil</label>
                <input id="akun-foto" type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="w-full text-sm"
                    @change="const f = $event.target.files[0]; pratinjau = f ? URL.createObjectURL(f) : null">
                <img x-show="pratinjau" :src="pratinjau" alt="Pratinjau foto profil" class="mt-2 h-20 w-20 rounded-xl object-cover">
                <p class="text-xs text-text-muted mt-1">JPG, PNG, atau WebP. Maksimal 2 MB.</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="akun-nama">Nama *</label>
                <input id="akun-nama" type="text" name="name" value="{{ old('name', $akun['nama']) }}" required class="w-full border border-border rounded-xl px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="akun-wa">Nomor WhatsApp</label>
                <input id="akun-wa" type="tel" name="no_wa" value="{{ old('no_wa', $akun['no_wa']) }}" placeholder="081234567890" class="w-full border border-border rounded-xl px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="akun-usaha">Nama Usaha Tani</label>
                <input id="akun-usaha" type="text" name="nama_usaha_tani" value="{{ old('nama_usaha_tani', $profil['nama_usaha_tani']) }}" class="w-full border border-border rounded-xl px-4 py-2">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-sm font-medium mb-1" for="akun-kab">Kabupaten</label>
                    <input id="akun-kab" type="text" name="kabupaten" value="{{ old('kabupaten', $profil['kabupaten']) }}" placeholder="Tabanan" class="w-full border border-border rounded-xl px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" for="akun-kec">Kecamatan</label>
                    <input id="akun-kec" type="text" name="kecamatan" value="{{ old('kecamatan', $profil['kecamatan']) }}" class="w-full border border-border rounded-xl px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" for="akun-desa">Desa</label>
                    <input id="akun-desa" type="text" name="desa" value="{{ old('desa', $profil['desa']) }}" class="w-full border border-border rounded-xl px-4 py-2">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="akun-deskripsi">Deskripsi Usaha</label>
                <textarea id="akun-deskripsi" name="deskripsi" rows="3" class="w-full border border-border rounded-xl px-4 py-2">{{ old('deskripsi', $profil['deskripsi']) }}</textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-sm font-medium mb-1" for="akun-bank">Nama Bank</label>
                    <input id="akun-bank" type="text" name="nama_bank" value="{{ old('nama_bank', $profil['nama_bank']) }}" placeholder="BPD Bali" class="w-full border border-border rounded-xl px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" for="akun-rek">No. Rekening</label>
                    <input id="akun-rek" type="text" name="no_rekening" inputmode="numeric" value="{{ old('no_rekening', $profil['no_rekening']) }}" class="w-full border border-border rounded-xl px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" for="akun-an">Atas Nama</label>
                    <input id="akun-an" type="text" name="atas_nama_rekening" value="{{ old('atas_nama_rekening', $profil['atas_nama_rekening']) }}" class="w-full border border-border rounded-xl px-4 py-2">
                </div>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" @click="formProfil = false" class="flex-1 border border-border rounded-xl py-2 text-sm">Batal</button>
                <button type="submit" class="flex-1 bg-primary text-white rounded-xl py-2 text-sm font-medium">Simpan</button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <a href="{{ url('/petani/pesanan') }}" class="bg-white border border-border rounded-2xl p-4 text-center">
            <p class="text-2xl">📋</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $jumlah['pesanan'] }}</p>
            <p class="text-sm text-text-muted">Pesanan Masuk</p>
        </a>
        <a href="{{ url('/petani/pesanan') }}#selesai" class="bg-white border border-border rounded-2xl p-4 text-center">
            <p class="text-2xl">☑️</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $jumlah['selesai'] }}</p>
            <p class="text-sm text-text-muted">Selesai</p>
        </a>
    </div>

    <div>
        <p class="text-xs font-semibold text-text-muted tracking-wide mb-2">AKUN SAYA</p>
        <a href="{{ url('/petani/alamat') }}" class="bg-white border border-border rounded-2xl p-4 flex items-center gap-3">
            <span class="text-xl">📍</span>
            <div class="flex-1">
                <p class="font-semibold text-gray-900">Alamat Saya</p>
                <p class="text-sm text-text-muted">{{ $jumlah['alamat'] ? $jumlah['alamat'] . ' alamat tersimpan' : 'Belum ada alamat' }}</p>
            </div>
            <span class="text-text-muted">›</span>
        </a>
    </div>

    <div>
        <p class="text-xs font-semibold text-text-muted tracking-wide mb-2">AKTIVITAS</p>
        <div class="bg-white border border-border rounded-2xl divide-y divide-border overflow-hidden">
            <a href="{{ url('/petani/pesanan') }}" class="flex items-center gap-3 p-4">
                <span class="text-xl">📋</span>
                <div class="flex-1">
                    <p class="font-semibold text-gray-900">Pesanan</p>
                    <p class="text-sm text-text-muted">{{ $jumlah['pesanan'] }} pesanan</p>
                </div>
                <span class="text-text-muted">›</span>
            </a>
            <a href="{{ url('/petani/pesan') }}" class="flex items-center gap-3 p-4">
                <span class="text-xl">💬</span>
                <div class="flex-1">
                    <p class="font-semibold text-gray-900">Pesan</p>
                    <p class="text-sm text-text-muted">Negosiasi harga & percakapan</p>
                </div>
                <span class="text-text-muted">›</span>
            </a>
            <a href="{{ url('/petani/produk') }}" class="flex items-center gap-3 p-4">
                <span class="text-xl">🛒</span>
                <div class="flex-1">
                    <p class="font-semibold text-gray-900">Kelola Produk</p>
                    <p class="text-sm text-text-muted">Produk, stok, dan harga</p>
                </div>
                <span class="text-text-muted">›</span>
            </a>
        </div>
    </div>

    <div>
        <p class="text-xs font-semibold text-text-muted tracking-wide mb-2">BANTUAN & LAINNYA</p>
        <a href="{{ url('/petani/bantuan') }}" class="bg-white border border-border rounded-2xl p-4 flex items-center gap-3">
            <span class="text-xl text-red-500">❓</span>
            <div class="flex-1">
                <p class="font-semibold text-gray-900">Bantuan & Informasi</p>
                <p class="text-sm text-text-muted">WhatsApp, pertanyaan umum, kontak</p>
            </div>
            <span class="text-text-muted">›</span>
        </a>
    </div>

    {{-- Keluar lewat route POST "keluar" milik AuthController (GET /masuk hanya untuk tamu, jadi link lama tidak mengeluarkan akun). --}}
    <form method="POST" action="{{ route('keluar') }}">
        @csrf
        <button type="submit" class="block w-full text-center border border-red-300 text-red-600 rounded-xl py-3 font-medium">Keluar</button>
    </form>
    <p class="text-center text-xs text-text-muted">TaniLoka v1.0.0 MVP · Dibuat di Bali</p>

    <div x-show="pesan" x-cloak x-transition role="status" class="fixed bottom-20 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-sm px-4 py-2 rounded-xl z-50" x-text="pesan"></div>
</div>
@endsection