@extends('layouts.petani')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6 space-y-4 pb-24" x-data="{
    daftar: [
        { id: 1, label: 'Kebun', penerima: 'Pak Made Sukartha', telepon: '0813-5555-0101', jalan: 'Banjar Dinas Batan, Desa Tunjuk, Tabanan', utama: true }
    ],
    formTambah: false,
    formUbah: false,
    editId: null,
    pesan: '',
    baru: { label: '', penerima: '', telepon: '', jalan: '' },
    edit: { label: '', penerima: '', telepon: '', jalan: '' },
    kosong() {
        return { label: '', penerima: '{{ addslashes(session('nama', 'Pak Made Sukartha')) }}', telepon: '0813-5555-0101', jalan: '' };
    },
    bukaTambah() {
        this.baru = this.kosong();
        this.formTambah = true;
        this.formUbah = false;
    },
    simpanTambah() {
        this.daftar.push({
            id: Date.now(),
            label: this.baru.label || 'Alamat',
            penerima: this.baru.penerima || '{{ addslashes(session('nama', 'Pak Made Sukartha')) }}',
            telepon: this.baru.telepon || '0813-5555-0101',
            jalan: this.baru.jalan || '-',
            utama: this.daftar.length === 0
        });
        this.formTambah = false;
        this.pesan = 'Alamat ditambahkan';
        setTimeout(() => this.pesan = '', 2000);
    },
    bukaUbah(a) {
        this.editId = a.id;
        this.edit = {
            label: a.label,
            penerima: a.penerima,
            telepon: a.telepon,
            jalan: a.jalan
        };
        this.formUbah = true;
        this.formTambah = false;
    },
    simpanUbah() {
        const a = this.daftar.find(x => x.id === this.editId);
        if (!a) return;
        a.label = this.edit.label;
        a.penerima = this.edit.penerima;
        a.telepon = this.edit.telepon;
        a.jalan = this.edit.jalan;
        this.formUbah = false;
        this.pesan = 'Alamat diperbarui';
        setTimeout(() => this.pesan = '', 2000);
    },
    hapus(id) {
        this.daftar = this.daftar.filter(a => a.id !== id);
        if (this.daftar.length && !this.daftar.some(a => a.utama)) this.daftar[0].utama = true;
        this.pesan = 'Alamat dihapus';
        setTimeout(() => this.pesan = '', 2000);
    },
    jadikanUtama(id) {
        this.daftar.forEach(a => { a.utama = a.id === id; });
        this.pesan = 'Alamat utama diperbarui';
        setTimeout(() => this.pesan = '', 2000);
    }
}">

    <x-page-header judul="Alamat Saya" />

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
                <div class="flex gap-2 pt-1">
                    <button type="button" @click="bukaUbah(a)" class="flex-1 border border-primary text-primary rounded-xl py-2 text-sm font-medium">Ubah</button>
                    <button type="button" @click="hapus(a.id)" class="flex-1 border border-red-300 text-red-600 rounded-xl py-2 text-sm font-medium">Hapus</button>
                </div>
                <button type="button" x-show="!a.utama" @click="jadikanUtama(a.id)" class="text-sm text-primary font-medium">Jadikan utama</button>
            </div>
        </template>
    </div>

    <button type="button" @click="bukaTambah()" class="w-full bg-yellow-400 text-gray-900 font-semibold rounded-xl py-3">
        + Tambah Alamat Baru
    </button>

    {{-- Form tambah: hanya muncul setelah tombol diklik --}}
    <div x-show="formTambah" x-cloak class="bg-white border border-border rounded-2xl p-4 space-y-3">
        <h2 class="font-semibold text-gray-900">Tambah Alamat Baru</h2>
        <div>
            <label class="block text-sm font-medium mb-1">Label</label>
            <input type="text" x-model="baru.label" class="w-full border border-border rounded-xl px-4 py-2" placeholder="Rumah">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Nama Penerima</label>
            <input type="text" x-model="baru.penerima" class="w-full border border-border rounded-xl px-4 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Nomor HP</label>
            <input type="text" x-model="baru.telepon" class="w-full border border-border rounded-xl px-4 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Alamat Lengkap</label>
            <input type="text" x-model="baru.jalan" class="w-full border border-border rounded-xl px-4 py-2" placeholder="Banjar Dinas Batan, Desa Tunjuk, Tabanan">
        </div>
        <div class="flex gap-2 pt-1">
            <button type="button" @click="formTambah = false" class="flex-1 border border-border rounded-xl py-2 text-sm">Batal</button>
            <button type="button" @click="simpanTambah()" class="flex-1 bg-primary text-white rounded-xl py-2 text-sm font-medium">Simpan</button>
        </div>
    </div>

    {{-- Form ubah --}}
    <div x-show="formUbah" x-cloak class="bg-white border border-border rounded-2xl p-4 space-y-3">
        <h2 class="font-semibold text-gray-900">Ubah Alamat</h2>
        <div>
            <label class="block text-sm font-medium mb-1">Label</label>
            <input type="text" x-model="edit.label" class="w-full border border-border rounded-xl px-4 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Nama Penerima</label>
            <input type="text" x-model="edit.penerima" class="w-full border border-border rounded-xl px-4 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Nomor HP</label>
            <input type="text" x-model="edit.telepon" class="w-full border border-border rounded-xl px-4 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Alamat Lengkap</label>
            <input type="text" x-model="edit.jalan" class="w-full border border-border rounded-xl px-4 py-2">
        </div>
        <div class="flex gap-2 pt-1">
            <button type="button" @click="formUbah = false" class="flex-1 border border-border rounded-xl py-2 text-sm">Batal</button>
            <button type="button" @click="simpanUbah()" class="flex-1 bg-primary text-white rounded-xl py-2 text-sm font-medium">Simpan</button>
        </div>
    </div>

    <div x-show="pesan" x-cloak x-transition class="fixed bottom-20 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-sm px-4 py-2 rounded-xl z-50" x-text="pesan"></div>
</div>
@endsection