@extends('layouts.petani')

@section('content')
<style>
    [x-cloak] { display: none !important; }
</style>

<div class="mx-auto max-w-3xl py-4" x-data="{
    tab: 'semua',
    detail: null,
    pesan: '',
    timer: null,
    tabs: [
        { id: 'semua', nama: 'Semua' },
        { id: 'menunggu', nama: 'Menunggu' },
        { id: 'diproses', nama: 'Diproses' },
        { id: 'dikirim', nama: 'Dikirim' },
        { id: 'selesai', nama: 'Selesai' },
        { id: 'dibatalkan', nama: 'Dibatalkan' }
    ],
    orders: [
        { nomor: 'TL-0012', pembeli: 'Warung Bu Komang', tanggal: '2 Nov 2026', status: 'menunggu', pengiriman: 'Kendaraan Sendiri', alamat: 'Denpasar Barat', produk: [{ nama: 'Bayam Hijau Organik', jumlah: 10, harga: 8500 }], total: 85000, riwayat: [] },
        { nomor: 'TL-0011', pembeli: 'Resto Segara', tanggal: '1 Nov 2026', status: 'diproses', pengiriman: 'JNE', alamat: 'Kuta, Badung', produk: [{ nama: 'Tomat Segar Grade A', jumlah: 20, harga: 12000 }, { nama: 'Kangkung Segar', jumlah: 5, harga: 6000 }], total: 270000, riwayat: [] },
        { nomor: 'TL-0010', pembeli: 'Katering Sari Rasa', tanggal: '30 Okt 2026', status: 'dikirim', pengiriman: 'J&T Express', alamat: 'Gianyar', produk: [{ nama: 'Tomat Segar Grade A', jumlah: 15, harga: 12000 }], total: 180000, riwayat: [] },
        { nomor: 'TL-0009', pembeli: 'Kafe Daun', tanggal: '28 Okt 2026', status: 'selesai', pengiriman: 'Ambil Sendiri', alamat: 'Tabanan', produk: [{ nama: 'Bayam Hijau Organik', jumlah: 8, harga: 8500 }], total: 68000, riwayat: [] }
    ],
    tahapan: [
        { id: 'menunggu', nama: 'Menunggu Konfirmasi' },
        { id: 'pembayaran', nama: 'Menunggu Pembayaran' },
        { id: 'verifikasi', nama: 'Menunggu Verifikasi Pembayaran' },
        { id: 'diproses', nama: 'Diproses' },
        { id: 'siap', nama: 'Siap Diambil' },
        { id: 'dikirim', nama: 'Terkirim' },
        { id: 'selesai', nama: 'Selesai' }
    ],
    init() {
        this.bacaHash();
    },
    bacaHash() {
        const hash = window.location.hash.slice(1);
        this.tab = this.tabs.some(t => t.id === hash) ? hash : 'semua';
    },
    rupiah(n) {
        return 'Rp ' + Number(n).toLocaleString('id-ID');
    },
    label(s) {
        return { menunggu: 'Menunggu Konfirmasi', diproses: 'Diproses', dikirim: 'Dikirim', selesai: 'Selesai', dibatalkan: 'Dibatalkan' }[s] || s;
    },
    warna(s) {
        return { menunggu: 'bg-yellow-100 text-yellow-900', diproses: 'bg-blue-100 text-blue-800', dikirim: 'bg-indigo-100 text-indigo-800', selesai: 'bg-emerald-100 text-emerald-800', dibatalkan: 'bg-red-100 text-red-800' }[s] || 'bg-gray-100 text-gray-800';
    },
    ikon(s) {
        return { menunggu: '⌛', diproses: '📦', dikirim: '🚚', selesai: '🎉', dibatalkan: '✕' }[s] || '📋';
    },
    jumlah(s) {
        return this.orders.filter(o => s === 'semua' || o.status === s).length;
    },
    terlihat(o) {
        return this.tab === 'semua' || this.tab === o.status;
    },
    buka(o) {
        this.detail = o;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    kembali() {
        this.detail = null;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    aksi(s) {
        return { menunggu: 'Terima Pesanan', diproses: 'Tandai Dikirim', dikirim: 'Tandai Selesai' }[s] || '';
    },
    petunjuk(s) {
        return { menunggu: 'Perlu tindakan: Terima atau tolak pesanan', diproses: 'Perlu tindakan: Siapkan pesanan untuk pengiriman', dikirim: 'Pesanan sedang dalam pengiriman', selesai: 'Pesanan sudah selesai', dibatalkan: 'Pesanan telah dibatalkan' }[s] || '';
    },
    kabar(teks) {
        clearTimeout(this.timer);
        this.pesan = teks;
        this.timer = setTimeout(() => this.pesan = '', 3500);
    },
    ubah(tolak = false) {
        const o = this.detail;
        if (!o) return;
        if (tolak && o.status !== 'menunggu') return;
        const tujuan = tolak ? 'dibatalkan' : { menunggu: 'diproses', diproses: 'dikirim', dikirim: 'selesai' }[o.status];
        if (!tujuan) return;
        const tanya = tolak ? 'Tolak pesanan ' + o.nomor + '?' : this.aksi(o.status) + ' ' + o.nomor + '?';
        if (!window.confirm(tanya)) return;
        o.status = tujuan;
        o.riwayat.push({ status: tujuan, waktu: new Date().toLocaleString('id-ID', { timeZone: 'Asia/Makassar' }) + ' WITA' });
        this.kabar('Status ' + o.nomor + ' menjadi ' + this.label(tujuan));
    },
    waktuTahap(id) {
        if (!this.detail) return '';
        const riwayat = this.detail.riwayat.filter(r => r.status === id);
        if (riwayat.length) return riwayat[riwayat.length - 1].waktu;
        return this.detail.status === id ? 'Status saat ini' : 'Riwayat belum tersedia';
    },
    tercatat(id) {
        return this.detail && this.detail.riwayat.some(r => r.status === id);
    },
    subtotal(o) {
        return o.produk.reduce((total, item) => total + item.jumlah * item.harga, 0);
    }
}" @hashchange.window="bacaHash(); detail = null">

    {{-- DAFTAR PESANAN --}}
    <section x-show="!detail">
        <h1 class="text-xl font-bold">Pesanan Masuk</h1>

        <div class="flex gap-2 overflow-x-auto py-4 mb-1" aria-label="Filter pesanan">
            <template x-for="t in tabs" :key="t.id">
                <button type="button" @click="tab = t.id"
                    :aria-pressed="tab === t.id"
                    :class="tab === t.id ? 'bg-green-700 border-green-700 text-white' : 'bg-white border-green-200 text-gray-800'"
                    class="shrink-0 rounded-full border px-4 py-2 text-xs font-semibold"
                    x-text="t.nama + ' (' + jumlah(t.id) + ')'">
                </button>
            </template>
        </div>

        <div class="space-y-4">
            <template x-for="o in orders" :key="o.nomor">
                <article x-show="terlihat(o)" class="overflow-hidden rounded-2xl border border-green-200 bg-white">
                    <header class="flex items-center gap-3 p-4" :class="warna(o.status)">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/70 text-2xl" aria-hidden="true" x-text="ikon(o.status)"></div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold" x-text="label(o.status)"></p>
                            <h2 class="mt-0.5 break-words text-sm font-bold text-gray-900" x-text="o.pembeli"></h2>
                            <p class="mt-0.5 text-xs text-gray-600">
                                #<span x-text="o.nomor"></span> · <span x-text="o.tanggal"></span>
                            </p>
                        </div>
                    </header>

                    <div class="space-y-3 p-4">
                        <template x-for="(item, i) in o.produk" :key="i">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-green-50 overflow-hidden">
                                    <template x-if="item.gambar">
                                        <img :src="item.gambar" :alt="item.nama" class="h-full w-full object-cover">
                                    </template>
                                    <span x-show="!item.gambar" class="px-1 text-center text-[10px] leading-tight text-gray-500">Foto belum tersedia</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="break-words text-sm font-semibold" x-text="item.nama"></p>
                                    <p class="mt-0.5 text-xs text-gray-500" x-text="item.jumlah + ' kg'"></p>
                                </div>
                            </div>
                        </template>

                        <p class="text-sm font-bold">Total: <span class="text-green-700" x-text="rupiah(o.total)"></span></p>

                        <div x-show="o.status === 'menunggu' || o.status === 'diproses'"
                            class="space-y-2 text-xs">
                            <p class="inline-block rounded-lg bg-amber-50 px-3 py-2 text-amber-800">Tenggat belum tersedia</p>
                            <p class="rounded-lg bg-amber-50 px-3 py-2 font-semibold text-amber-800" x-text="petunjuk(o.status)"></p>
                        </div>

                        <button type="button" @click="buka(o)"
                            :aria-label="'Lihat detail pesanan ' + o.nomor"
                            class="w-full rounded-xl bg-green-700 py-3 text-sm font-bold text-white hover:bg-green-800">
                            Lihat Detail
                        </button>
                    </div>
                </article>
            </template>
        </div>

        <div x-show="jumlah(tab) === 0" x-cloak class="rounded-xl border border-green-200 bg-white p-8 text-center text-sm text-gray-500">
            Belum ada pesanan pada status ini.
        </div>
    </section>

    {{-- DETAIL PESANAN --}}
    <template x-if="detail">
        <section>
            <header class="mb-5 grid min-h-12 grid-cols-[5rem_minmax(0,1fr)_5rem] items-center gap-2 border-b border-green-100 pb-3">
                <button type="button" @click="kembali()" class="inline-flex items-center gap-1 py-2 text-sm font-semibold text-green-700 hover:text-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-700 justify-self-start">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                <span>Kembali</span>
            </button>
                <div class="min-w-0 break-words text-center">
                    <h1 class="text-sm font-bold">Detail Pesanan</h1>
                    <p class="mt-0.5 text-xs text-gray-500" x-text="'#' + detail.nomor"></p>
                </div>
                <span aria-hidden="true"></span>
            </header>

            <div class="space-y-4">
                <div class="flex items-center gap-3 rounded-2xl border border-green-100 p-5" :class="warna(detail.status)">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-white/70 text-3xl" aria-hidden="true" x-text="ikon(detail.status)"></div>
                    <div>
                        <h2 class="font-bold" x-text="label(detail.status)"></h2>
                        <p class="mt-1 text-xs text-gray-700">Dipesan <span x-text="detail.tanggal"></span></p>
                        <p class="mt-1 text-xs text-gray-700">Anda sebagai penjual</p>
                    </div>
                </div>

                <div x-show="detail.status === 'menunggu' || detail.status === 'diproses'" class="rounded-xl border border-amber-300 bg-white p-4 text-xs text-amber-800">
                    <p class="font-semibold">Tenggat belum tersedia</p>
                    <p class="mt-1">Batas waktu belum terhubung ke data pesanan. Tidak ada pembatalan otomatis pada tampilan ini.</p>
                </div>

                <div x-show="aksi(detail.status)" class="space-y-2">
                    <button type="button" @click="ubah()"
                        class="w-full rounded-xl bg-green-700 py-3 text-sm font-bold text-white hover:bg-green-800"
                        x-text="aksi(detail.status)">
                    </button>
                    <button type="button" x-show="detail.status === 'menunggu'" @click="ubah(true)"
                        class="w-full rounded-xl border border-red-300 bg-white py-3 text-sm font-semibold text-red-600 hover:bg-red-50">
                        Tolak Pesanan
                    </button>
                </div>

                <section class="rounded-xl border border-green-200 bg-white p-4">
                    <h2 class="mb-4 text-sm font-bold">Perjalanan Pesanan</h2>
                    <p x-show="detail.status === 'dibatalkan'" class="mb-4 rounded-lg bg-red-50 p-3 text-xs font-semibold text-red-700">Pesanan dibatalkan.</p>
                    <ol>
                        <template x-for="(tahap, i) in tahapan" :key="tahap.id">
                            <li class="relative flex gap-3 pb-5 last:pb-0">
                                <span x-show="i < tahapan.length - 1" class="absolute left-3 top-6 bottom-0 w-px bg-gray-200" aria-hidden="true"></span>
                                <span class="relative flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                                    :class="detail.status === tahap.id ? 'bg-yellow-400 text-gray-900' : (tercatat(tahap.id) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500')"
                                    x-text="i + 1">
                                </span>
                                <div class="pt-0.5">
                                    <p class="text-xs" :class="detail.status === tahap.id ? 'font-bold text-gray-900' : 'font-medium text-gray-600'" x-text="tahap.nama"></p>
                                    <p class="mt-1 text-[11px] text-gray-500" x-text="waktuTahap(tahap.id)"></p>
                                </div>
                            </li>
                        </template>
                    </ol>
                    <p class="mt-4 border-t border-gray-100 pt-3 text-[11px] text-gray-500">
                        Tahap pembayaran dan siap diambil belum terhubung ke sistem. Riwayat perubahan pada tampilan ini hanya dicatat selama halaman terbuka.
                    </p>
                </section>

                <section class="rounded-xl border border-green-200 bg-white p-4">
                    <h2 class="mb-3 text-sm font-bold">Produk Dipesan</h2>
                    <div class="space-y-4">
                        <template x-for="(item, i) in detail.produk" :key="i">
                            <div class="flex items-center gap-3">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-green-50">
                                    <template x-if="item.gambar">
                                        <img :src="item.gambar" :alt="item.nama" class="h-full w-full object-cover">
                                    </template>
                                    <span x-show="!item.gambar" class="px-1 text-center text-[10px] leading-tight text-gray-500">Foto belum tersedia</span>
                                </div>
                                <div class="min-w-0 text-xs">
                                    <p class="break-words font-semibold" x-text="item.nama"></p>
                                    <p class="mt-1 text-gray-500"><span x-text="item.jumlah"></span> kg × <span x-text="rupiah(item.harga)"></span> / kg</p>
                                    <p class="mt-1 font-semibold text-green-700" x-text="rupiah(item.jumlah * item.harga)"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </section>

                <section class="rounded-xl border border-green-200 bg-white p-4 text-xs">
                    <h2 class="text-sm font-bold">Pengiriman</h2>
                    <p class="mt-2 font-medium" x-text="detail.pengiriman"></p>
                    <p class="mt-1 text-gray-500" x-text="detail.pembeli + ', ' + detail.alamat"></p>
                    <h3 class="mt-4 font-bold">Metode Pembayaran</h3>
                    <p class="mt-1 text-gray-500" x-text="detail.pembayaran || 'Belum tersedia'"></p>
                </section>

                <section class="rounded-xl border border-green-200 bg-white p-4 text-xs">
                    <h2 class="mb-3 text-sm font-bold">Rincian Biaya</h2>
                    <dl class="space-y-3">
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500">Subtotal produk</dt>
                            <dd x-text="rupiah(subtotal(detail))"></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500">Ongkos kirim</dt>
                            <dd x-text="detail.ongkir == null ? 'Belum tersedia' : (detail.ongkir === 0 ? 'Gratis' : rupiah(detail.ongkir))"></dd>
                        </div>
                        <div class="flex justify-between gap-3 border-t border-gray-100 pt-3 font-bold text-green-700">
                            <dt>Total tercatat</dt>
                            <dd x-text="rupiah(detail.total)"></dd>
                        </div>
                    </dl>
                </section>

                <section class="rounded-xl border border-green-200 bg-white p-4">
                    <h2 class="text-sm font-bold">Pembeli</h2>
                    <p class="mt-2 text-xs" x-text="detail.pembeli"></p>
                    <a href="{{ url('/petani/pesan') }}" class="mt-3 block rounded-xl border border-green-700 py-2.5 text-center text-xs font-semibold text-green-700 hover:bg-green-50">
                        Hubungi Pembeli
                    </a>
                    <p class="mt-2 text-[11px] text-gray-500">Membuka menu pesan; pilih percakapan pembeli yang sesuai.</p>
                </section>
            </div>
        </section>
    </template>

    <div x-show="pesan" x-cloak x-transition role="status" aria-live="polite"
        class="fixed bottom-20 left-1/2 z-50 w-max max-w-[90vw] -translate-x-1/2 rounded-xl bg-gray-900 px-4 py-3 text-sm text-white shadow-lg"
        x-text="pesan">
    </div>
</div>
@endsection