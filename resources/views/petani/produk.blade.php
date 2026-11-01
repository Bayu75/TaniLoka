@extends('layouts.petani')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6 space-y-5" x-data="{
    produk: [
        {
            id: 1,
            nama: 'Bayam Hijau Organik',
            kategori: 'Sayuran',
            harga: 8500,
            stok: 120,
            stokAsli: 120,
            min: 5,
            kabupaten: 'Tabanan',
            periode: 'Tersedia sepanjang tahun, panen tiap 3 minggu',
            tersedia: true,
            gambar: 'https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?w=600&h=400&fit=crop'
        },
        {
            id: 2,
            nama: 'Wortel Organik',
            kategori: 'Umbi-umbian',
            harga: 14000,
            stok: 90,
            stokAsli: 90,
            min: 5,
            kabupaten: 'Tabanan',
            periode: 'Panen setiap 2 bulan',
            tersedia: true,
            gambar: 'https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?w=600&h=400&fit=crop'
        }
    ],
    formTambah: false,
    formUbah: false,
    editId: null,
    pesan: '',
    kosong() {
        return { nama: '', kategori: 'Sayuran', harga: '', stok: '', min: '', kabupaten: 'Tabanan', periode: '', tersedia: true, gambar: '' };
    },
    baru: null,
    edit: null,
    setTersedia(p, on) {
        if (on) {
            p.tersedia = true;
            p.stok = p.stokAsli > 0 ? p.stokAsli : (p.stok > 0 ? p.stok : 1);
            p.stokAsli = p.stok;
        } else {
            p.tersedia = false;
            if (p.stok > 0) p.stokAsli = p.stok;
            p.stok = 0;
        }
    },
    bukaTambah() {
        this.baru = this.kosong();
        this.formTambah = true;
    },
    onFotoBaru(e) {
        const f = e.target.files && e.target.files[0];
        if (!f || !this.baru) return;
        const r = new FileReader();
        r.onload = () => { this.baru.gambar = r.result; };
        r.readAsDataURL(f);
    },
    simpanTambah() {
        const n = parseInt(this.baru.harga, 10);
        const s = parseInt(this.baru.stok, 10);
        const m = parseInt(this.baru.min, 10);
        const stok = isNaN(s) ? 0 : s;
        this.produk.push({
            id: Date.now(),
            nama: this.baru.nama || 'Produk baru',
            kategori: this.baru.kategori || 'Sayuran',
            harga: isNaN(n) ? 0 : n,
            stok: stok,
            stokAsli: stok > 0 ? stok : 1,
            min: isNaN(m) ? 1 : m,
            kabupaten: this.baru.kabupaten || 'Tabanan',
            periode: this.baru.periode || '-',
            tersedia: !!this.baru.tersedia && stok > 0,
            gambar: this.baru.gambar || 'https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?w=600&h=400&fit=crop'
        });
        this.formTambah = false;
        this.pesan = 'Produk ditambahkan';
        setTimeout(() => this.pesan = '', 2000);
    },
    bukaUbah(p) {
        this.editId = p.id;
        this.edit = {
            nama: p.nama,
            kategori: p.kategori,
            harga: p.harga,
            stok: p.tersedia ? (p.stokAsli || p.stok) : p.stok,
            min: p.min,
            kabupaten: p.kabupaten,
            periode: p.periode,
            tersedia: p.tersedia,
            gambar: p.gambar
        };
        this.formUbah = true;
    },
    onFotoUbah(e) {
        const f = e.target.files && e.target.files[0];
        if (!f || !this.edit) return;
        const r = new FileReader();
        r.onload = () => { this.edit.gambar = r.result; };
        r.readAsDataURL(f);
    },
    simpanUbah() {
        const p = this.produk.find(x => x.id === this.editId);
        if (!p) return;
        p.nama = this.edit.nama;
        p.kategori = this.edit.kategori;
        p.harga = parseInt(this.edit.harga, 10) || 0;
        p.min = parseInt(this.edit.min, 10) || 1;
        p.kabupaten = this.edit.kabupaten;
        p.periode = this.edit.periode;
        p.gambar = this.edit.gambar;
        const s = parseInt(this.edit.stok, 10);
        const stokBaru = isNaN(s) ? 0 : s;
        if (this.edit.tersedia) {
            p.tersedia = true;
            p.stok = stokBaru > 0 ? stokBaru : 1;
            p.stokAsli = p.stok;
        } else {
            p.tersedia = false;
            p.stokAsli = stokBaru > 0 ? stokBaru : (p.stokAsli || 1);
            p.stok = 0;
        }
        this.formUbah = false;
        this.pesan = 'Produk diperbarui';
        setTimeout(() => this.pesan = '', 2000);
    },
    hapus(id) {
        this.produk = this.produk.filter(p => p.id !== id);
        this.pesan = 'Produk dihapus';
        setTimeout(() => this.pesan = '', 2000);
    },
    jumlah() { return this.produk.length; },
    aktif() { return this.produk.filter(p => p.tersedia && p.stok > 0).length; },
    habis() { return this.produk.filter(p => !p.tersedia || p.stok === 0).length; }
}">

    <x-page-header judul="Kelola Produk" />
    <p class="text-sm text-text-muted -mt-3">Atur produk yang Anda jual</p>

    <div class="grid grid-cols-3 gap-3">
        <div class="bg-white border border-border rounded-2xl p-4 text-center">
            <p class="text-2xl font-semibold text-gray-900" x-text="jumlah()"></p>
            <p class="text-sm text-text-muted">Jumlah produk</p>
        </div>
        <div class="bg-white border border-border rounded-2xl p-4 text-center">
            <p class="text-2xl font-semibold text-gray-900" x-text="aktif()"></p>
            <p class="text-sm text-text-muted">Aktif</p>
        </div>
        <div class="bg-white border border-border rounded-2xl p-4 text-center">
            <p class="text-2xl font-semibold text-gray-900" x-text="habis()"></p>
            <p class="text-sm text-text-muted">Stok habis</p>
        </div>
    </div>

    <button type="button" @click="bukaTambah()" class="w-full bg-yellow-400 text-gray-900 font-semibold rounded-xl py-3">
        + Tambah Produk Baru
    </button>

    <div class="grid md:grid-cols-2 gap-4">
        <template x-for="p in produk" :key="p.id">
            <div class="bg-white border border-border rounded-2xl overflow-hidden">
                <div class="h-40 bg-cover bg-center" :style="`background-image:url('${p.gambar}')`"></div>
                <div class="p-4 space-y-2 text-sm">
                    <div class="flex justify-between gap-2">
                        <span class="text-text-muted">Nama Produk</span>
                        <span class="font-medium text-gray-900 text-right">
                            <span x-text="p.nama"></span>
                            <button type="button" @click="bukaUbah(p)" class="text-primary ml-1">✎ Ubah</button>
                        </span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-text-muted">Kategori</span>
                        <span class="font-medium text-gray-900 text-right">
                            <span x-text="p.kategori"></span>
                            <button type="button" @click="bukaUbah(p)" class="text-primary ml-1">✎ Ubah</button>
                        </span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-text-muted">Harga (per kg)</span>
                        <span class="font-medium text-gray-900 text-right">
                            <span x-text="'Rp ' + p.harga.toLocaleString('id-ID') + ' / kg'"></span>
                            <button type="button" @click="bukaUbah(p)" class="text-primary ml-1">✎ Ubah</button>
                        </span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-text-muted">Stok (kg)</span>
                        <span class="font-medium text-gray-900 text-right">
                            <span x-text="p.stok + ' kg'"></span>
                            <button type="button" @click="bukaUbah(p)" class="text-primary ml-1">✎ Ubah</button>
                        </span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-text-muted">Minimal pesan (kg)</span>
                        <span class="font-medium text-gray-900 text-right">
                            <span x-text="p.min + ' kg'"></span>
                            <button type="button" @click="bukaUbah(p)" class="text-primary ml-1">✎ Ubah</button>
                        </span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-text-muted">Kabupaten</span>
                        <span class="font-medium text-gray-900 text-right">
                            <span x-text="p.kabupaten"></span>
                            <button type="button" @click="bukaUbah(p)" class="text-primary ml-1">✎ Ubah</button>
                        </span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-text-muted">Periode Panen</span>
                        <span class="font-medium text-gray-900 text-right max-w-[60%]">
                            <span x-text="p.periode"></span>
                            <button type="button" @click="bukaUbah(p)" class="text-primary ml-1">✎ Ubah</button>
                        </span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-sm font-medium" :class="p.tersedia ? 'text-primary' : 'text-text-muted'" x-text="p.tersedia ? 'Tersedia' : 'Tidak tersedia'"></span>
                        <button type="button"
                            @click="setTersedia(p, !p.tersedia)"
                            :class="p.tersedia ? 'bg-primary' : 'bg-gray-300'"
                            class="relative w-11 h-6 rounded-full transition-colors" aria-label="Toggle tersedia">
                            <span :class="p.tersedia ? 'translate-x-5' : 'translate-x-0'"
                                class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform"></span>
                        </button>
                    </div>
                    <button type="button" @click="hapus(p.id)" class="w-full mt-2 text-red-600 font-medium py-2">Hapus Produk</button>
                </div>
            </div>
        </template>
    </div>

    <div x-show="formTambah" x-cloak class="fixed inset-0 bg-black/40 z-50 flex items-end sm:items-center justify-center p-4">
        <div class="bg-white w-full sm:max-w-md rounded-2xl p-5 space-y-3 max-h-[90vh] overflow-y-auto" @click.outside="formTambah = false">
            <h2 class="text-lg font-semibold">Tambah Produk Baru</h2>
            <template x-if="baru">
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium mb-1">Foto Produk</label>
                        <input type="file" accept="image/*" @change="onFotoBaru($event)" class="w-full text-sm">
                        <div x-show="baru.gambar" class="mt-2 h-32 rounded-xl bg-cover bg-center border border-border" :style="`background-image:url('${baru.gambar}')`"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama Produk</label>
                        <input type="text" x-model="baru.nama" class="w-full border border-border rounded-xl px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Kategori</label>
                        <select x-model="baru.kategori" class="w-full border border-border rounded-xl px-4 py-2">
                            <option>Sayuran</option>
                            <option>Umbi-umbian</option>
                            <option>Bumbu & Rempah</option>
                            <option>Buah-buahan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Harga per kg (Rp)</label>
                        <input type="number" x-model="baru.harga" class="w-full border border-border rounded-xl px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Stok (kg)</label>
                        <input type="number" x-model="baru.stok" class="w-full border border-border rounded-xl px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Minimal pesan (kg)</label>
                        <input type="number" x-model="baru.min" class="w-full border border-border rounded-xl px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Kabupaten</label>
                        <input type="text" x-model="baru.kabupaten" class="w-full border border-border rounded-xl px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Periode Panen</label>
                        <input type="text" x-model="baru.periode" class="w-full border border-border rounded-xl px-4 py-2">
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm" x-text="baru.tersedia ? 'Tersedia' : 'Tidak tersedia'"></span>
                        <button type="button" @click="baru.tersedia = !baru.tersedia" :class="baru.tersedia ? 'bg-primary' : 'bg-gray-300'" class="relative w-11 h-6 rounded-full">
                            <span :class="baru.tersedia ? 'translate-x-5' : 'translate-x-0'" class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform"></span>
                        </button>
                    </div>
                    <div class="flex gap-2 pt-2">
                        <button type="button" @click="formTambah = false" class="flex-1 border border-border rounded-xl py-2">Batal</button>
                        <button type="button" @click="simpanTambah()" class="flex-1 bg-primary text-white rounded-xl py-2">Simpan</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div x-show="formUbah" x-cloak class="fixed inset-0 bg-black/40 z-50 flex items-end sm:items-center justify-center p-4">
        <div class="bg-white w-full sm:max-w-md rounded-2xl p-5 space-y-3 max-h-[90vh] overflow-y-auto" @click.outside="formUbah = false">
            <h2 class="text-lg font-semibold">Ubah Produk</h2>
            <template x-if="edit">
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium mb-1">Foto Produk</label>
                        <input type="file" accept="image/*" @change="onFotoUbah($event)" class="w-full text-sm">
                        <div x-show="edit.gambar" class="mt-2 h-32 rounded-xl bg-cover bg-center border border-border" :style="`background-image:url('${edit.gambar}')`"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama Produk</label>
                        <input type="text" x-model="edit.nama" class="w-full border border-border rounded-xl px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Kategori</label>
                        <select x-model="edit.kategori" class="w-full border border-border rounded-xl px-4 py-2">
                            <option>Sayuran</option>
                            <option>Umbi-umbian</option>
                            <option>Bumbu & Rempah</option>
                            <option>Buah-buahan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Harga per kg (Rp)</label>
                        <input type="number" x-model="edit.harga" class="w-full border border-border rounded-xl px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Stok (kg)</label>
                        <input type="number" x-model="edit.stok" class="w-full border border-border rounded-xl px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Minimal pesan (kg)</label>
                        <input type="number" x-model="edit.min" class="w-full border border-border rounded-xl px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Kabupaten</label>
                        <input type="text" x-model="edit.kabupaten" class="w-full border border-border rounded-xl px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Periode Panen</label>
                        <input type="text" x-model="edit.periode" class="w-full border border-border rounded-xl px-4 py-2">
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm" x-text="edit.tersedia ? 'Tersedia' : 'Tidak tersedia'"></span>
                        <button type="button" @click="edit.tersedia = !edit.tersedia" :class="edit.tersedia ? 'bg-primary' : 'bg-gray-300'" class="relative w-11 h-6 rounded-full">
                            <span :class="edit.tersedia ? 'translate-x-5' : 'translate-x-0'" class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform"></span>
                        </button>
                    </div>
                    <div class="flex gap-2 pt-2">
                        <button type="button" @click="formUbah = false" class="flex-1 border border-border rounded-xl py-2">Batal</button>
                        <button type="button" @click="simpanUbah()" class="flex-1 bg-primary text-white rounded-xl py-2">Simpan</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div x-show="pesan" x-cloak x-transition class="fixed bottom-20 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-sm px-4 py-2 rounded-xl z-50" x-text="pesan"></div>
</div>
@endsection