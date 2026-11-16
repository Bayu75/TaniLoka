@extends('layouts.petani')

@section('content')
<style>
    [x-cloak] { display: none !important; }
</style>

<div class="mx-auto max-w-3xl py-4" x-data="{
    tab: 'semua',
    detail: null,
    pesan: @js(session('sukses') ?? session('gagal') ?? ($errors->any() ? $errors->first() : '')),
    timer: null,
    tabs: [
        { id: 'semua', nama: 'Semua' },
        { id: 'menunggu', nama: 'Menunggu' },
        { id: 'diproses', nama: 'Diproses' },
        { id: 'dikirim', nama: 'Dikirim' },
        { id: 'selesai', nama: 'Selesai' },
        { id: 'pembayaran', nama: 'Belum Dibayar' },
        { id: 'ditolak', nama: 'Ditolak' },
        { id: 'dibatalkan', nama: 'Dibatalkan' }
    ],
    {{-- Data dari Petani\PesananController (tabel pesanan, pesanan_item, riwayat_status_pesanan). --}}
    orders: @js($orders),
    detailAwal: @js($detailAwal),
    tabAwal: @js($tabAwal),
    urlAksi: {
        terima: @js(route('petani.pesanan.terima', ['pesanan' => '__ID__'])),
        tolak: @js(route('petani.pesanan.tolak', ['pesanan' => '__ID__'])),
        kirim: @js(route('petani.pesanan.kirim', ['pesanan' => '__ID__']))
    },
    tahapan: [
        { id: 'pembayaran', nama: 'Menunggu Pembayaran' },
        { id: 'menunggu', nama: 'Menunggu Konfirmasi' },
        { id: 'diproses', nama: 'Diproses' },
        { id: 'dikirim', nama: 'Dikirim' },
        { id: 'selesai', nama: 'Selesai' }
    ],
    init() {
        this.bacaHash();
        if (this.tabAwal && this.tabs.some(t => t.id === this.tabAwal)) this.tab = this.tabAwal;
        if (this.detailAwal) this.detail = this.orders.find(o => o.id === this.detailAwal) || null;
        if (this.pesan) this.kabar(this.pesan);
    },
    bacaHash() {
        const hash = window.location.hash.slice(1);
        this.tab = this.tabs.some(t => t.id === hash) ? hash : 'semua';
    },
    rupiah(n) {
        return 'Rp ' + Number(n).toLocaleString('id-ID');
    },
    label(s) {
        return { pembayaran: 'Menunggu Pembayaran', menunggu: 'Menunggu Konfirmasi', diproses: 'Diproses', dikirim: 'Dikirim', selesai: 'Selesai', dibatalkan: 'Dibatalkan', ditolak: 'Ditolak' }[s] || s;
    },
    warna(s) {
        return { pembayaran: 'bg-gray-100 text-gray-800', menunggu: 'bg-yellow-100 text-yellow-900', diproses: 'bg-blue-100 text-blue-800', dikirim: 'bg-indigo-100 text-indigo-800', selesai: 'bg-emerald-100 text-emerald-800', dibatalkan: 'bg-red-100 text-red-800', ditolak: 'bg-red-100 text-red-800' }[s] || 'bg-gray-100 text-gray-800';
    },
    ikon(s) {
        return { pembayaran: '💳', menunggu: '⌛', diproses: '📦', dikirim: '🚚', selesai: '🎉', dibatalkan: '✕', ditolak: '✕' }[s] || '📋';
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
        // Petani hanya: terima/tolak (menunggu) dan tandai dikirim (diproses). Status selesai dikonfirmasi pembeli.
        return { menunggu: 'Terima Pesanan', diproses: 'Tandai Dikirim' }[s] || '';
    },
    petunjuk(s) {
        return { pembayaran: 'Menunggu pembeli membayar. Pesanan belum bisa diproses.', menunggu: 'Perlu tindakan: Terima atau tolak pesanan', diproses: 'Perlu tindakan: Siapkan pesanan untuk pengiriman', dikirim: 'Pesanan sedang dalam pengiriman', selesai: 'Pesanan sudah selesai', dibatalkan: 'Pesanan telah dibatalkan', ditolak: 'Pesanan telah ditolak' }[s] || '';
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
        const aksi = tolak ? 'tolak' : { menunggu: 'terima', diproses: 'kirim' }[o.status];
        if (!aksi) return;
        const f = this.$refs.formStatus;
        if (tolak) {
            const alasan = window.prompt('Tolak pesanan ' + o.nomor + '? Tulis alasan (boleh dikosongkan):', '');
            if (alasan === null) return;
            this.$refs.alasan.value = alasan;
        } else {
            if (!window.confirm(this.aksi(o.status) + ' ' + o.nomor + '?')) return;
            this.$refs.alasan.value = '';
        }
        f.action = this.urlAksi[aksi].replace('__ID__', o.id);
        f.submit();
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
            <template x-for="o in orders" :key="o.id">
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

                        <div x-show="o.status === 'menunggu' || o.status === 'diproses' || o.status === 'pembayaran'"
                            class="space-y-2 text-xs">
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

                <div x-show="detail.status === 'pembayaran'" class="rounded-xl border border-amber-300 bg-white p-4 text-xs text-amber-800">
                    <p class="font-semibold">Menunggu pembayaran pembeli</p>
                    <p class="mt-1">Pesanan baru bisa diterima atau ditolak setelah pembeli membayar.</p>
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
                    <p x-show="detail.status === 'dibatalkan' || detail.status === 'ditolak'" class="mb-4 rounded-lg bg-red-50 p-3 text-xs font-semibold text-red-700"
                        x-text="(detail.status === 'ditolak' ? 'Pesanan ditolak.' : 'Pesanan dibatalkan.') + (detail.riwayat.length && detail.riwayat[detail.riwayat.length - 1].keterangan ? ' ' + detail.riwayat[detail.riwayat.length - 1].keterangan : '')"></p>
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
                        Waktu setiap tahap diambil dari riwayat status pesanan (WITA).
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
                    <p class="mt-2 font-medium" x-text="detail.penerima + ' · ' + detail.telepon"></p>
                    <p class="mt-1 text-gray-500" x-text="detail.alamat"></p>
                    <p x-show="detail.catatan" class="mt-2 text-gray-500" x-text="'Catatan: ' + detail.catatan"></p>
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

    {{-- Form tersembunyi untuk aksi status (POST + CSRF) --}}
    <form x-ref="formStatus" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="alasan" x-ref="alasan">
    </form>

    <div x-show="pesan" x-cloak x-transition role="status" aria-live="polite"
        class="fixed bottom-20 left-1/2 z-50 w-max max-w-[90vw] -translate-x-1/2 rounded-xl bg-gray-900 px-4 py-3 text-sm text-white shadow-lg"
        x-text="pesan">
    </div>
</div>
@endsection