@extends('layouts.petani')

@section('content')
{{-- Data dari Petani\AlamatController (tabel alamat). Form dikirim ke server; Alpine hanya untuk buka/tutup form. --}}
@php
    $formLama = old('_form');
    $nilaiLama = [
        'label' => old('label', ''),
        'nama_penerima' => old('nama_penerima', ''),
        'no_hp' => old('no_hp', ''),
        'alamat_lengkap' => old('alamat_lengkap', ''),
        'kabupaten' => old('kabupaten', ''),
        'kecamatan' => old('kecamatan', ''),
        'kode_pos' => old('kode_pos', ''),
    ];
@endphp
<div class="max-w-3xl mx-auto px-4 py-6 space-y-4 pb-24" x-data="{
    daftar: @js($daftarAlamat),
    bawaan: @js($bawaan),
    formTambah: {{ $formLama === 'tambah' ? 'true' : 'false' }},
    formUbah: {{ str_starts_with((string) $formLama, 'ubah:') ? 'true' : 'false' }},
    editId: {{ str_starts_with((string) $formLama, 'ubah:') ? (int) substr($formLama, 5) : 'null' }},
    pesan: @js(session('sukses') ?? session('gagal') ?? ($errors->any() ? 'Periksa kembali isian alamat.' : '')),
    urlUbah: @js(route('petani.alamat.update', ['alamat' => '__ID__'])),
    urlHapus: @js(route('petani.alamat.destroy', ['alamat' => '__ID__'])),
    urlUtama: @js(route('petani.alamat.utama', ['alamat' => '__ID__'])),
    baru: {{ $formLama === 'tambah' ? Js::from($nilaiLama) : 'null' }},
    edit: {{ str_starts_with((string) $formLama, 'ubah:') ? Js::from($nilaiLama) : 'null' }},
    init() {
        if (!this.baru) this.baru = this.kosong();
        if (!this.edit) this.edit = this.kosong();
        if (this.pesan) setTimeout(() => this.pesan = '', 3000);
    },
    url(pola, id) {
        return pola.replace('__ID__', id);
    },
    kosong() {
        return { label: '', nama_penerima: this.bawaan.penerima, no_hp: this.bawaan.telepon, alamat_lengkap: '', kabupaten: this.bawaan.kabupaten, kecamatan: '', kode_pos: '' };
    },
    bukaTambah() {
        this.baru = this.kosong();
        this.formTambah = true;
        this.formUbah = false;
    },
    bukaUbah(a) {
        this.editId = a.id;
        this.edit = {
            label: a.label,
            nama_penerima: a.penerima,
            no_hp: a.telepon,
            alamat_lengkap: a.jalan,
            kabupaten: a.kabupaten,
            kecamatan: a.kecamatan,
            kode_pos: a.kode_pos
        };
        this.formUbah = true;
        this.formTambah = false;
    },
    kirim(aksi, id, tanya) {
        if (tanya && !window.confirm(tanya)) return;
        const f = this.$refs.formAksi;
        f.action = this.url(aksi === 'hapus' ? this.urlHapus : this.urlUtama, id);
        this.$refs.metodeAksi.value = aksi === 'hapus' ? 'DELETE' : 'PATCH';
        f.submit();
    }
}">

    <header class="mb-4 grid grid-cols-[5rem_minmax(0,1fr)_5rem] items-center gap-2">
        <a href="{{ url('/petani/akun') }}" class="inline-flex items-center gap-1 py-2 text-sm font-semibold text-green-700 hover:text-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-700 justify-self-start">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                <span>Kembali</span>
            </a>
        <h1 class="min-w-0 break-words text-center text-lg font-bold">Alamat Saya</h1>
        <span aria-hidden="true"></span>
    </header>

    <div class="space-y-3">
        <template x-for="a in daftar" :key="a.id">
            <div class="bg-white border rounded-2xl p-4 space-y-3" :class="a.utama ? 'border-primary' : 'border-border'">
                <div class="flex items-center gap-2">
                    <p class="font-semibold text-gray-900" x-text="a.label"></p>
                    <span x-show="a.utama" class="text-xs bg-primary/10 text-primary px-2 py-0.5 rounded-full">Alamat Utama</span>
                </div>
                <p class="text-sm text-gray-900">
                    <span x-text="a.penerima"></span> · <span x-text="a.telepon"></span>
                </p>
                <p class="text-sm text-text-muted" x-text="a.jalan"></p>
                <p class="text-sm text-text-muted" x-text="'Kec. ' + a.kecamatan + ', ' + a.kabupaten + (a.kode_pos ? ' ' + a.kode_pos : '')"></p>
                <div class="flex gap-2 pt-1">
                    <button type="button" @click="bukaUbah(a)" class="flex-1 border border-primary text-primary rounded-xl py-2 text-sm font-medium">Ubah</button>
                    <button type="button" @click="kirim('hapus', a.id, 'Hapus alamat ' + a.label + '?')" class="flex-1 border border-red-300 text-red-600 rounded-xl py-2 text-sm font-medium">Hapus</button>
                </div>
                <button type="button" x-show="!a.utama" @click="kirim('utama', a.id)" class="text-sm text-primary font-medium">Jadikan utama</button>
            </div>
        </template>
    </div>

    <p x-show="daftar.length === 0" class="rounded-2xl border border-border bg-white p-6 text-center text-sm text-text-muted">Belum ada alamat tersimpan.</p>

    @if ($errors->any())
        <div class="rounded-xl bg-red-50 p-3 text-sm text-red-700" role="alert">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form x-ref="formAksi" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="_method" x-ref="metodeAksi" value="PATCH">
    </form>

    <button type="button" @click="bukaTambah()" class="w-full bg-yellow-400 text-gray-900 font-semibold rounded-xl py-3">
        + Tambah Alamat Baru
    </button>

    {{-- Form tambah: hanya muncul setelah tombol diklik --}}
    <form method="POST" action="{{ route('petani.alamat.store') }}" x-show="formTambah" x-cloak class="bg-white border border-border rounded-2xl p-4 space-y-3">
        @csrf
        <input type="hidden" name="_form" value="tambah">
        <div class="grid grid-cols-[5rem_minmax(0,1fr)_5rem] items-center gap-2">
            <button type="button" @click="formTambah = false" class="inline-flex items-center gap-1 py-2 text-sm font-semibold text-green-700 hover:text-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-700 justify-self-start">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                <span>Kembali</span>
            </button>
            <h2 class="text-center font-semibold text-gray-900">Tambah Alamat Baru</h2>
            <span aria-hidden="true"></span>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1" for="tambah-label">Label *</label>
            <input id="tambah-label" type="text" name="label" x-model="baru.label" class="w-full border border-border rounded-xl px-4 py-2" placeholder="Rumah" required maxlength="50">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1" for="tambah-nama_penerima">Nama Penerima *</label>
            <input id="tambah-nama_penerima" type="text" name="nama_penerima" x-model="baru.nama_penerima" class="w-full border border-border rounded-xl px-4 py-2" required>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1" for="tambah-no_hp">Nomor HP *</label>
            <input id="tambah-no_hp" type="text" name="no_hp" x-model="baru.no_hp" class="w-full border border-border rounded-xl px-4 py-2" required inputmode="tel">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1" for="tambah-alamat_lengkap">Alamat Lengkap *</label>
            <input id="tambah-alamat_lengkap" type="text" name="alamat_lengkap" x-model="baru.alamat_lengkap" class="w-full border border-border rounded-xl px-4 py-2" placeholder="Banjar Dinas Batan, Desa Tunjuk" required>
        </div>
        <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium mb-1" for="tambah-kabupaten">Kabupaten/Kota *</label>
            <input id="tambah-kabupaten" type="text" name="kabupaten" x-model="baru.kabupaten" class="w-full border border-border rounded-xl px-4 py-2" placeholder="Tabanan" required>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1" for="tambah-kecamatan">Kecamatan *</label>
            <input id="tambah-kecamatan" type="text" name="kecamatan" x-model="baru.kecamatan" class="w-full border border-border rounded-xl px-4 py-2" placeholder="Tabanan" required>
        </div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1" for="tambah-kode_pos">Kode Pos</label>
            <input id="tambah-kode_pos" type="text" name="kode_pos" x-model="baru.kode_pos" class="w-full border border-border rounded-xl px-4 py-2" inputmode="numeric" maxlength="5">
        </div>
        <div class="flex gap-2 pt-1">
            <button type="button" @click="formTambah = false" class="flex-1 border border-border rounded-xl py-2 text-sm">Batal</button>
            <button type="submit" class="flex-1 bg-primary text-white rounded-xl py-2 text-sm font-medium">Simpan</button>
        </div>
    </form>

    {{-- Form ubah --}}
    <form method="POST" :action="url(urlUbah, editId)" x-show="formUbah" x-cloak class="bg-white border border-border rounded-2xl p-4 space-y-3">
        @csrf
        @method('PUT')
        <input type="hidden" name="_form" :value="'ubah:' + editId">
        <div class="grid grid-cols-[5rem_minmax(0,1fr)_5rem] items-center gap-2">
            <button type="button" @click="formUbah = false" class="inline-flex items-center gap-1 py-2 text-sm font-semibold text-green-700 hover:text-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-700 justify-self-start">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                <span>Kembali</span>
            </button>
            <h2 class="text-center font-semibold text-gray-900">Ubah Alamat</h2>
            <span aria-hidden="true"></span>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1" for="ubah-label">Label *</label>
            <input id="ubah-label" type="text" name="label" x-model="edit.label" class="w-full border border-border rounded-xl px-4 py-2" placeholder="Rumah" required maxlength="50">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1" for="ubah-nama_penerima">Nama Penerima *</label>
            <input id="ubah-nama_penerima" type="text" name="nama_penerima" x-model="edit.nama_penerima" class="w-full border border-border rounded-xl px-4 py-2" required>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1" for="ubah-no_hp">Nomor HP *</label>
            <input id="ubah-no_hp" type="text" name="no_hp" x-model="edit.no_hp" class="w-full border border-border rounded-xl px-4 py-2" required inputmode="tel">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1" for="ubah-alamat_lengkap">Alamat Lengkap *</label>
            <input id="ubah-alamat_lengkap" type="text" name="alamat_lengkap" x-model="edit.alamat_lengkap" class="w-full border border-border rounded-xl px-4 py-2" placeholder="Banjar Dinas Batan, Desa Tunjuk" required>
        </div>
        <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium mb-1" for="ubah-kabupaten">Kabupaten/Kota *</label>
            <input id="ubah-kabupaten" type="text" name="kabupaten" x-model="edit.kabupaten" class="w-full border border-border rounded-xl px-4 py-2" placeholder="Tabanan" required>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1" for="ubah-kecamatan">Kecamatan *</label>
            <input id="ubah-kecamatan" type="text" name="kecamatan" x-model="edit.kecamatan" class="w-full border border-border rounded-xl px-4 py-2" placeholder="Tabanan" required>
        </div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1" for="ubah-kode_pos">Kode Pos</label>
            <input id="ubah-kode_pos" type="text" name="kode_pos" x-model="edit.kode_pos" class="w-full border border-border rounded-xl px-4 py-2" inputmode="numeric" maxlength="5">
        </div>
        <div class="flex gap-2 pt-1">
            <button type="button" @click="formUbah = false" class="flex-1 border border-border rounded-xl py-2 text-sm">Batal</button>
            <button type="submit" class="flex-1 bg-primary text-white rounded-xl py-2 text-sm font-medium">Simpan</button>
        </div>
    </form>

    <div x-show="pesan" x-cloak x-transition class="fixed bottom-20 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-sm px-4 py-2 rounded-xl z-50" x-text="pesan"></div>
</div>
@endsection