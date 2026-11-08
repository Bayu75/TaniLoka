@extends('layouts.petani')

@section('content')
<div x-data="{
    cari: '',
    kategori: 'Semua',
    urut: '',
    saringBuka: false,
    pilih: null,
    toko: null,
    riwayatNavigasi: [],
    tabDetail: 'info',
    fotoAktif: 0,
    banner: '{{ asset('images/banner-toko.jpg') }}',
    namaSaya: 'Pak Made Sukartha',
    daftarKategori: [
        { nama: 'Semua', ikon: '' },
        { nama: 'Sayuran', ikon: '🥬' },
        { nama: 'Bumbu & Rempah', ikon: '🌶️' },
        { nama: 'Umbi-umbian', ikon: '🥕' },
        { nama: 'Buah-buahan', ikon: '🍉' },
        { nama: 'Serealia', ikon: '🌽' },
        { nama: 'Jamur', ikon: '🍄' }
    ],
    produk: [
        { nama: 'Bayam Hijau Organik', kategori: 'Sayuran', harga: 8500, minimal: 5, stokKg: 120, petani: 'Pak Made Sukartha', rating: 4.8, transaksi: 234, lokasi: 'Tabanan', label: ['Organik'], nego: true, stok: true, gambar: '{{ asset('images/bayam.jpg') }}', ikon: '🥬', panen: 'Tersedia sepanjang tahun, panen tiap 3 minggu', deskripsi: 'Bayam hijau organik segar, dipanen pagi hari dari kebun bebas pestisida. Cocok untuk restoran dan katering yang mengutamakan bahan alami. Tersertifikasi organik oleh LSO Bali.' },
        { nama: 'Cabai Rawit Merah', kategori: 'Bumbu & Rempah', harga: 45000, minimal: 2, stokKg: 40, petani: 'Ibu Wayan Sari', rating: 4.9, transaksi: 187, lokasi: 'Bangli', label: [], nego: false, stok: true, gambar: '{{ asset('images/cabai.jpg') }}', ikon: '🌶️', panen: 'Panen tiap 2 minggu', deskripsi: 'Cabai rawit merah pedas dari dataran tinggi Bangli, dipetik saat matang penuh.' },
        { nama: 'Tomat Segar Grade A', kategori: 'Sayuran', harga: 12000, minimal: 10, stokKg: 200, petani: 'Pak Ketut Wirasa', rating: 4.7, transaksi: 156, lokasi: 'Tabanan', label: ['Grade A'], nego: true, stok: true, gambar: '{{ asset('images/tomat.jpg') }}', ikon: '🍅', panen: 'Panen tiap minggu', deskripsi: 'Tomat segar grade A dengan ukuran seragam, cocok untuk saus dan salad.' },
        { nama: 'Kangkung Segar', kategori: 'Sayuran', harga: 5500, minimal: 10, stokKg: 90, petani: 'Ibu Nyoman Dewi', rating: 4.6, transaksi: 98, lokasi: 'Gianyar', label: [], nego: true, stok: true, gambar: '{{ asset('images/kangkung.jpg') }}', ikon: '🥬', panen: 'Panen tiap 3 minggu', deskripsi: 'Kangkung segar dari sawah Gianyar, batang renyah dan daun lebar.' },
        { nama: 'Bawang Putih Lokal', kategori: 'Bumbu & Rempah', harga: 35000, minimal: 5, stokKg: 60, petani: 'Pak Gede Setiawan', rating: 4.5, transaksi: 76, lokasi: 'Karangasem', label: [], nego: true, stok: true, gambar: '{{ asset('images/bawang-putih.jpg') }}', ikon: '🧄', panen: 'Panen musiman', deskripsi: 'Bawang putih lokal Karangasem dengan aroma kuat, sudah dikeringkan.' },
        { nama: 'Kembang Kol Putih', kategori: 'Sayuran', harga: 15000, minimal: 5, stokKg: 70, petani: 'Pak Ketut Wirasa', rating: 4.7, transaksi: 156, lokasi: 'Tabanan', label: [], nego: false, stok: true, gambar: '{{ asset('images/kembang-kol.jpg') }}', ikon: '🥦', panen: 'Panen tiap 2 bulan', deskripsi: 'Kembang kol putih padat dan bersih, dipanen dari kebun Bedugul.' },
        { nama: 'Wortel Organik', kategori: 'Umbi-umbian', harga: 14000, minimal: 5, stokKg: 85, petani: 'Pak Made Sukartha', rating: 4.8, transaksi: 234, lokasi: 'Tabanan', label: ['Organik'], nego: true, stok: true, gambar: '{{ asset('images/wortel.jpg') }}', ikon: '🥕', panen: 'Panen tiap 3 bulan', deskripsi: 'Wortel organik manis dan renyah, ditanam tanpa pestisida kimia.' },
        { nama: 'Paprika Merah', kategori: 'Sayuran', harga: 55000, minimal: 3, stokKg: 0, petani: 'Ibu Wayan Sari', rating: 4.9, transaksi: 187, lokasi: 'Bangli', label: [], nego: true, stok: false, gambar: '{{ asset('images/paprika.jpg') }}', ikon: '🫑', panen: 'Panen tiap 2 bulan', deskripsi: 'Paprika merah segar dari greenhouse Bangli, warna cerah dan daging tebal.' }
    ],
    hasil() {
        const q = this.cari.toLowerCase();
        let list = this.produk.filter(p =>
            (this.kategori === 'Semua' || p.kategori === this.kategori) &&
            (p.nama + ' ' + p.petani + ' ' + p.lokasi).toLowerCase().includes(q)
        );
        if (this.urut === 'murah') list = [...list].sort((a, b) => a.harga - b.harga);
        if (this.urut === 'mahal') list = [...list].sort((a, b) => b.harga - a.harga);
        if (this.urut === 'rating') list = [...list].sort((a, b) => b.rating - a.rating);
        return list;
    },
    produkToko() { return this.produk.filter(p => p.petani === this.toko); },
    infoToko() { return this.produkToko()[0] || {}; },
    // Simpan induk lokal tanpa mengubah pencarian, kategori, atau urutan.
    buka(p) {
        this.riwayatNavigasi.push({
            pilih: this.pilih,
            toko: this.toko,
            tabDetail: this.tabDetail,
            fotoAktif: this.fotoAktif
        });
        this.toko = null;
        this.pilih = p;
        this.tabDetail = 'info';
        this.fotoAktif = 0;
        window.scrollTo(0, 0);
    },
    kembaliInduk() {
        const induk = this.riwayatNavigasi.pop() || { pilih: null, toko: null };
        this.pilih = induk.pilih;
        this.toko = induk.toko;
        if (induk.tabDetail !== undefined) this.tabDetail = induk.tabDetail;
        if (induk.fotoAktif !== undefined) this.fotoAktif = induk.fotoAktif;
        window.scrollTo(0, 0);
    },
    tutup() { this.kembaliInduk(); },
    bukaToko(nama) {
        this.riwayatNavigasi.push({
            pilih: this.pilih,
            toko: this.toko,
            tabDetail: this.tabDetail,
            fotoAktif: this.fotoAktif
        });
        this.toko = nama;
        window.scrollTo(0, 0);
    },
    tutupToko() { this.kembaliInduk(); },
    inisial(n) { return n.split(' ').pop().charAt(0); },
    rupiah(n) { return 'Rp ' + n.toLocaleString('id-ID'); },
    bintang(r) { return '★★★★★'.slice(0, Math.round(r)) + '☆☆☆☆☆'.slice(0, 5 - Math.round(r)); }
}">

    {{-- ===== DAFTAR KATALOG ===== --}}
    <div x-show="!pilih && !toko">
        <div class="border-b border-gray-200">
            <div class="max-w-5xl mx-auto px-4 py-5 space-y-3">
                <h1 class="text-xl font-bold">Katalog</h1>
                <div class="flex gap-2 relative">
                    <input type="text" x-model="cari" placeholder="Cari produk, petani, lokasi..." class="flex-1 bg-white border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-green-600">
                    <button type="button" @click="saringBuka = !saringBuka" class="bg-white border border-gray-300 rounded-xl px-4 text-sm font-semibold hover:border-green-600">☰ Saring</button>
                    <div x-show="saringBuka" x-cloak @click.outside="saringBuka = false" class="absolute right-0 top-12 bg-white border border-gray-200 rounded-xl shadow-lg p-2 z-20 w-48 text-sm">
                        <button type="button" @click="urut = ''; saringBuka = false" class="block w-full text-left px-3 py-2 rounded-lg hover:bg-gray-100">Default</button>
                        <button type="button" @click="urut = 'murah'; saringBuka = false" class="block w-full text-left px-3 py-2 rounded-lg hover:bg-gray-100">Harga termurah</button>
                        <button type="button" @click="urut = 'mahal'; saringBuka = false" class="block w-full text-left px-3 py-2 rounded-lg hover:bg-gray-100">Harga termahal</button>
                        <button type="button" @click="urut = 'rating'; saringBuka = false" class="block w-full text-left px-3 py-2 rounded-lg hover:bg-gray-100">Rating tertinggi</button>
                    </div>
                </div>
                <div class="flex gap-2 overflow-x-auto pb-1">
                    <template x-for="k in daftarKategori" :key="k.nama">
                        <button type="button" @click="kategori = k.nama" :class="kategori === k.nama ? 'bg-green-700 text-white border-green-700' : 'bg-white text-gray-800 border-gray-300'" class="px-4 py-1.5 rounded-full border text-sm font-medium whitespace-nowrap">
                            <span x-text="k.ikon"></span> <span x-text="k.nama"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto px-4 py-5 pb-16">
            <p class="text-sm text-gray-600 mb-3"><span class="font-semibold" x-text="hasil().length"></span> produk ditemukan</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <template x-for="p in hasil()" :key="p.nama">
                    <div @click="buka(p)" class="bg-white border border-gray-200 rounded-2xl overflow-hidden cursor-pointer hover:border-green-600" :class="!p.stok && 'opacity-80'">
                        <div class="relative h-32 bg-green-50 flex items-center justify-center text-5xl">
                            <span x-text="p.ikon"></span>
                            <img x-show="p.gambar" :src="p.gambar" :alt="p.nama" x-on:error="p.gambar = ''" class="absolute inset-0 w-full h-full object-cover">
                            <div class="absolute top-2 left-2 flex flex-col gap-1 items-start">
                                <template x-for="l in p.label" :key="l">
                                    <span class="bg-green-700 text-white text-[10px] font-semibold px-2 py-0.5 rounded" x-text="l"></span>
                                </template>
                                <span x-show="p.nego" class="bg-yellow-400 text-gray-900 text-[10px] font-semibold px-2 py-0.5 rounded">Bisa Nego</span>
                            </div>
                            <div x-show="!p.stok" class="absolute inset-0 bg-black/55 flex items-center justify-center text-white text-sm font-bold">Stok Habis</div>
                        </div>
                        <div class="p-3">
                            <span class="inline-block bg-green-50 text-green-800 text-[11px] px-2 py-0.5 rounded-full" x-text="p.kategori"></span>
                            <p class="font-semibold text-sm mt-1.5" x-text="p.nama"></p>
                            <p class="mt-1"><span class="text-green-700 font-bold" x-text="rupiah(p.harga)"></span><span class="text-xs text-gray-500">/kg</span></p>
                            <p class="text-xs text-gray-500">Minimal pesan <span x-text="p.minimal"></span> kg</p>
                            <div class="border-t border-gray-100 mt-2 pt-2">
                                <p class="text-xs text-gray-700" x-text="p.petani"></p>
                                <p class="text-xs text-gray-500"><span class="text-yellow-500" x-text="bintang(p.rating)"></span> <span x-text="p.rating"></span> • <span x-text="p.lokasi"></span></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            <p x-show="hasil().length === 0" x-cloak class="text-center text-sm text-gray-500 py-10">Produk tidak ditemukan.</p>
        </div>
    </div>

    {{-- ===== DETAIL PRODUK ===== --}}
    <template x-if="pilih && !toko">
        <div>
            <div class="bg-white border-b border-gray-200">
                <div class="max-w-4xl mx-auto px-4 py-3 grid grid-cols-[5rem_minmax(0,1fr)_5rem] items-center gap-2">
                    <button type="button" @click="tutup()" class="inline-flex items-center gap-1 py-2 text-sm font-semibold text-green-700 hover:text-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-700 justify-self-start">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                <span>Kembali</span>
            </button>
                    <div class="min-w-0 break-words text-center">
                        <p class="font-bold text-sm" x-text="pilih.nama"></p>
                        <p class="text-xs text-gray-500" x-text="pilih.kategori"></p>
                    </div>
                    <span aria-hidden="true"></span>
                </div>
            </div>

            <div class="max-w-4xl mx-auto px-4 py-5 pb-28 grid md:grid-cols-2 gap-6">
                <div>
                    <div class="relative h-72 bg-green-50 rounded-2xl overflow-hidden flex items-center justify-center text-7xl">
                        <span x-text="pilih.ikon"></span>
                        <img x-show="pilih.gambar" :src="pilih.gambar" :alt="pilih.nama" x-on:error="pilih.gambar = ''" class="absolute inset-0 w-full h-full object-cover">
                        <div class="absolute top-3 left-3 flex flex-col gap-1 items-start">
                            <template x-for="l in pilih.label" :key="l">
                                <span class="bg-green-700 text-white text-xs font-semibold px-2.5 py-1 rounded-full" x-text="l"></span>
                            </template>
                            <span x-show="pilih.nego" class="bg-yellow-400 text-gray-900 text-xs font-semibold px-2.5 py-1 rounded-full">Bisa Nego</span>
                        </div>
                        <div x-show="!pilih.stok" class="absolute inset-0 bg-black/55 flex items-center justify-center text-white font-bold">Stok Habis</div>
                    </div>
                    <div class="flex gap-2 mt-3">
                        <template x-for="i in [0, 1, 2]" :key="i">
                            <button type="button" @click="fotoAktif = i" :class="fotoAktif === i ? 'border-green-700' : 'border-transparent opacity-70'" class="w-12 h-12 rounded-lg overflow-hidden border-2 bg-green-50 flex items-center justify-center">
                                <img x-show="pilih.gambar" :src="pilih.gambar" class="w-full h-full object-cover">
                                <span x-show="!pilih.gambar" x-text="pilih.ikon"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex gap-2">
                        <span class="bg-green-50 text-green-800 text-xs px-2.5 py-1 rounded-full" x-text="pilih.kategori"></span>
                        <span x-show="pilih.stok" class="bg-green-100 text-green-700 text-xs px-2.5 py-1 rounded-full">● Tersedia</span>
                        <span x-show="!pilih.stok" class="bg-red-100 text-red-700 text-xs px-2.5 py-1 rounded-full">● Stok Habis</span>
                    </div>
                    <h1 class="text-xl font-bold" x-text="pilih.nama"></h1>

                    <div class="bg-green-50 rounded-2xl p-4">
                        <p><span class="text-2xl font-bold text-green-700" x-text="rupiah(pilih.harga)"></span> <span class="text-sm text-gray-500">/kg</span></p>
                        <p class="text-sm text-gray-600 mt-1">Minimal pesan: <b x-text="pilih.minimal + ' kg'"></b> · Stok: <b x-text="pilih.stokKg + ' kg'"></b></p>
                        <p x-show="pilih.nego" class="text-xs text-yellow-700 mt-2">★ Harga ini bisa dinegosiasikan untuk pembelian dalam jumlah besar</p>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-2xl p-3 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-green-700 text-white flex items-center justify-center font-bold" x-text="inisial(pilih.petani)"></div>
                        <div class="flex-1">
                            <p class="font-semibold text-sm" x-text="pilih.petani"></p>
                            <p class="text-xs text-gray-500"><span class="text-yellow-500" x-text="bintang(pilih.rating)"></span> <span x-text="pilih.rating + ' · ' + pilih.transaksi + ' transaksi · ' + pilih.lokasi + ', Bali'"></span></p>
                        </div>
                        <button type="button" @click="bukaToko(pilih.petani)" class="text-green-700 text-sm font-semibold whitespace-nowrap">Kunjungi Toko →</button>
                    </div>

                    <div>
                        <div class="flex border-b border-gray-200 text-sm">
                            <button type="button" @click="tabDetail = 'info'" :class="tabDetail === 'info' ? 'border-green-700 text-green-700' : 'border-transparent text-gray-600'" class="px-3 py-2 border-b-2 font-semibold">Informasi Produk</button>
                            <button type="button" @click="tabDetail = 'kirim'" :class="tabDetail === 'kirim' ? 'border-green-700 text-green-700' : 'border-transparent text-gray-600'" class="px-3 py-2 border-b-2 font-semibold">Pengiriman</button>
                        </div>
                        <div x-show="tabDetail === 'info'" class="pt-3 space-y-3">
                            <p class="text-sm text-gray-700 leading-relaxed" x-text="pilih.deskripsi"></p>
                            <div class="bg-green-50 rounded-xl p-3">
                                <p class="text-xs text-gray-500">Periode Panen</p>
                                <p class="text-sm" x-text="pilih.panen"></p>
                            </div>
                            <div class="bg-green-50 rounded-xl p-3">
                                <p class="text-xs text-gray-500">Kabupaten</p>
                                <p class="text-sm" x-text="pilih.lokasi + ', Bali'"></p>
                            </div>
                        </div>
                        <div x-show="tabDetail === 'kirim'" class="pt-3 space-y-3">
                            <div class="bg-green-50 rounded-xl p-3">
                                <p class="text-xs text-gray-500">Metode Pengiriman</p>
                                <p class="text-sm">Kendaraan Sendiri, JNE, J&amp;T Express, Ambil Sendiri</p>
                            </div>
                            <div class="bg-green-50 rounded-xl p-3">
                                <p class="text-xs text-gray-500">Dikirim dari</p>
                                <p class="text-sm" x-text="pilih.lokasi + ', Bali'"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fixed bottom-0 left-0 right-0 bg-white/90 border-t border-gray-200">
                <div class="max-w-4xl mx-auto px-4 py-3">
                    <div x-show="pilih.petani === namaSaya" class="flex gap-3">
                        <p class="flex-1 bg-green-50 rounded-xl px-4 py-3 text-sm text-gray-700">Ini produk Anda. Ubah lewat Kelola Produk.</p>
                        <a href="{{ url('/petani/produk') }}" class="bg-yellow-400 hover:bg-yellow-500 rounded-xl px-5 py-3 text-sm font-semibold">Kelola Produk</a>
                    </div>
                    <div x-show="pilih.petani !== namaSaya" class="flex gap-3">
                        <p class="flex-1 bg-green-50 rounded-xl px-4 py-3 text-sm text-gray-700">Produk milik <span x-text="pilih.petani"></span>.</p>
                        </div>
                </div>
            </div>
        </div>
    </template>

    {{-- ===== PROFIL TOKO ===== --}}
    <template x-if="toko">
        <div class="pb-16">
            <div class="bg-white border-b border-gray-200">
                <div class="max-w-4xl mx-auto px-4 py-3 grid grid-cols-[5rem_minmax(0,1fr)_5rem] items-center gap-2">
                    <button type="button" @click="tutupToko()" class="inline-flex items-center gap-1 py-2 text-sm font-semibold text-green-700 hover:text-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-700 justify-self-start">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                <span>Kembali</span>
            </button>
                    <div class="min-w-0 break-words text-center">
                        <p class="font-bold text-sm">Profil Toko</p>
                        <p class="text-xs text-gray-500" x-text="toko"></p>
                    </div>
                    <span aria-hidden="true"></span>
                </div>
            </div>

            <div class="relative h-32 md:h-40 bg-gradient-to-r from-green-800 to-green-600 overflow-hidden">
                <img x-show="banner" :src="banner" alt="Banner toko" x-on:error="banner = ''" class="w-full h-full object-cover">
            </div>

            <div class="bg-white border-b border-gray-200">
                <div class="max-w-4xl mx-auto px-4 pb-5">
                    <div class="w-16 h-16 -mt-8 relative rounded-full bg-green-700 border-4 border-white text-white flex items-center justify-center text-2xl font-bold" x-text="inisial(toko)"></div>
                    <div class="flex items-start justify-between mt-2">
                        <div>
                            <h1 class="text-lg font-bold" x-text="toko"></h1>
                            <p class="text-sm text-gray-500" x-text="infoToko().lokasi + ', Bali'"></p>
                        </div>
                        <span class="bg-green-50 text-green-800 text-xs font-semibold px-3 py-1 rounded-full">✓ Terverifikasi</span>
                    </div>
                    <div class="flex items-center gap-6 border-t border-gray-100 mt-4 pt-4">
                        <div><p class="font-bold" x-text="produkToko().length"></p><p class="text-xs text-gray-500">Produk</p></div>
                        <div><p class="font-bold" x-text="infoToko().transaksi"></p><p class="text-xs text-gray-500">Transaksi</p></div>
                        <div class="flex items-center gap-2">
                            <span class="text-yellow-500" x-text="bintang(infoToko().rating)"></span>
                            <div><p class="font-bold" x-text="infoToko().rating"></p><p class="text-xs text-gray-500">Rating</p></div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a x-show="toko === namaSaya" href="{{ url('/petani/produk') }}" class="inline-block bg-yellow-400 hover:bg-yellow-500 rounded-xl px-5 py-3 text-sm font-semibold">Kelola Produk</a>
                    </div>
                </div>
            </div>

            <div class="max-w-4xl mx-auto px-4 py-5">
                <h2 class="font-semibold mb-3">Produk dari <span x-text="toko"></span></h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <template x-for="p in produkToko()" :key="p.nama">
                        <div @click="buka(p)" class="bg-white border border-gray-200 rounded-2xl overflow-hidden cursor-pointer hover:border-green-600" :class="!p.stok && 'opacity-80'">
                            <div class="relative h-32 bg-green-50 flex items-center justify-center text-5xl">
                                <span x-text="p.ikon"></span>
                                <img x-show="p.gambar" :src="p.gambar" :alt="p.nama" x-on:error="p.gambar = ''" class="absolute inset-0 w-full h-full object-cover">
                                <div class="absolute top-2 left-2 flex flex-col gap-1 items-start">
                                    <template x-for="l in p.label" :key="l">
                                        <span class="bg-green-700 text-white text-[10px] font-semibold px-2 py-0.5 rounded" x-text="l"></span>
                                    </template>
                                    <span x-show="p.nego" class="bg-yellow-400 text-gray-900 text-[10px] font-semibold px-2 py-0.5 rounded">Bisa Nego</span>
                                </div>
                                <div x-show="!p.stok" class="absolute inset-0 bg-black/55 flex items-center justify-center text-white text-sm font-bold">Stok Habis</div>
                            </div>
                            <div class="p-3">
                                <span class="inline-block bg-green-50 text-green-800 text-[11px] px-2 py-0.5 rounded-full" x-text="p.kategori"></span>
                                <p class="font-semibold text-sm mt-1.5" x-text="p.nama"></p>
                                <p class="mt-1"><span class="text-green-700 font-bold" x-text="rupiah(p.harga)"></span><span class="text-xs text-gray-500">/kg</span></p>
                                <p class="text-xs text-gray-500">Minimal pesan <span x-text="p.minimal"></span> kg</p>
                                <div class="border-t border-gray-100 mt-2 pt-2">
                                    <p class="text-xs text-gray-700" x-text="p.petani"></p>
                                    <p class="text-xs text-gray-500"><span class="text-yellow-500" x-text="bintang(p.rating)"></span> <span x-text="p.rating"></span> • <span x-text="p.lokasi"></span></p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection