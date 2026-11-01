@extends('layouts.petani')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6" x-data="{
    tab: 'semua',
    pesan: '',
    orders: [
        { nomor: 'TL-0012', pembeli: 'Warung Bu Komang', tanggal: '2 Nov 2026', status: 'menunggu', pengiriman: 'Kendaraan Sendiri', alamat: 'Denpasar Barat', produk: [{ nama: 'Bayam Hijau Organik', jumlah: 10, harga: 8500 }], total: 85000 },
        { nomor: 'TL-0011', pembeli: 'Resto Segara', tanggal: '1 Nov 2026', status: 'diproses', pengiriman: 'JNE', alamat: 'Kuta, Badung', produk: [{ nama: 'Tomat Segar Grade A', jumlah: 20, harga: 12000 }, { nama: 'Kangkung Segar', jumlah: 5, harga: 6000 }], total: 270000 },
        { nomor: 'TL-0010', pembeli: 'Katering Sari Rasa', tanggal: '30 Okt 2026', status: 'dikirim', pengiriman: 'J&T Express', alamat: 'Gianyar', produk: [{ nama: 'Tomat Segar Grade A', jumlah: 15, harga: 12000 }], total: 180000 },
        { nomor: 'TL-0009', pembeli: 'Kafe Daun', tanggal: '28 Okt 2026', status: 'selesai', pengiriman: 'Ambil Sendiri', alamat: 'Tabanan', produk: [{ nama: 'Bayam Hijau Organik', jumlah: 8, harga: 8500 }], total: 68000 }
    ],
    labelStatus(s) {
        return { menunggu: 'Menunggu Konfirmasi', diproses: 'Diproses', dikirim: 'Dikirim', selesai: 'Selesai', dibatalkan: 'Dibatalkan' }[s] || s;
    },
    kelasStatus(s) {
        return { menunggu: 'bg-yellow-100 text-yellow-800', diproses: 'bg-blue-100 text-blue-800', dikirim: 'bg-indigo-100 text-indigo-800', selesai: 'bg-primary/10 text-primary', dibatalkan: 'bg-red-100 text-red-700' }[s] || 'bg-gray-100 text-gray-800';
    },
    aksiLabel(s) {
        return { menunggu: 'Terima Pesanan', diproses: 'Tandai Dikirim', dikirim: 'Tandai Selesai' }[s] || '';
    },
    maju(o) {
        const next = { menunggu: 'diproses', diproses: 'dikirim', dikirim: 'selesai' };
        if (next[o.status]) {
            o.status = next[o.status];
            this.pesan = 'Status ' + o.nomor + ' diperbarui';
            setTimeout(() => this.pesan = '', 2000);
        }
    },
    tolak(o) {
        o.status = 'dibatalkan';
        this.pesan = 'Pesanan ' + o.nomor + ' ditolak';
        setTimeout(() => this.pesan = '', 2000);
    },
    terlihat(o) {
        return this.tab === 'semua' || this.tab === o.status;
    }
}">

    <x-page-header judul="Pesanan Masuk" />

    <div class="flex gap-2 overflow-x-auto my-4">
        <button type="button" @click="tab = 'semua'" :class="tab === 'semua' ? 'bg-primary text-white border-primary' : 'bg-white text-gray-700 border-border'" class="px-4 py-1.5 rounded-full border text-sm whitespace-nowrap">Semua</button>
        <button type="button" @click="tab = 'menunggu'" :class="tab === 'menunggu' ? 'bg-primary text-white border-primary' : 'bg-white text-gray-700 border-border'" class="px-4 py-1.5 rounded-full border text-sm whitespace-nowrap">Baru</button>
        <button type="button" @click="tab = 'diproses'" :class="tab === 'diproses' ? 'bg-primary text-white border-primary' : 'bg-white text-gray-700 border-border'" class="px-4 py-1.5 rounded-full border text-sm whitespace-nowrap">Diproses</button>
        <button type="button" @click="tab = 'dikirim'" :class="tab === 'dikirim' ? 'bg-primary text-white border-primary' : 'bg-white text-gray-700 border-border'" class="px-4 py-1.5 rounded-full border text-sm whitespace-nowrap">Dikirim</button>
        <button type="button" @click="tab = 'selesai'" :class="tab === 'selesai' ? 'bg-primary text-white border-primary' : 'bg-white text-gray-700 border-border'" class="px-4 py-1.5 rounded-full border text-sm whitespace-nowrap">Selesai</button>
    </div>

    <div class="grid md:grid-cols-2 gap-3">
        <template x-for="o in orders" :key="o.nomor">
            <div x-show="terlihat(o)" class="bg-white border border-border rounded-2xl p-4">
                <div class="flex justify-between gap-2">
                    <div>
                        <p class="font-semibold" x-text="o.nomor"></p>
                        <p class="text-sm text-text-muted"><span x-text="o.pembeli"></span> · <span x-text="o.tanggal"></span></p>
                    </div>
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full whitespace-nowrap" :class="kelasStatus(o.status)" x-text="labelStatus(o.status)"></span>
                </div>
                <ul class="mt-3 text-sm space-y-1">
                    <template x-for="(item, i) in o.produk" :key="i">
                        <li class="flex justify-between">
                            <span><span x-text="item.nama"></span> × <span x-text="item.jumlah"></span> kg</span>
                            <span x-text="'Rp' + (item.harga * item.jumlah).toLocaleString('id-ID')"></span>
                        </li>
                    </template>
                </ul>
                <p class="mt-3 pt-3 border-t border-border text-sm text-text-muted">🚚 <span x-text="o.pengiriman"></span> · <span x-text="o.alamat"></span></p>
                <p class="flex justify-between font-semibold mt-1 text-sm"><span>Total</span><span x-text="'Rp' + o.total.toLocaleString('id-ID')"></span></p>
                <div class="mt-3 flex gap-2" x-show="o.status !== 'selesai' && o.status !== 'dibatalkan'">
                    <button type="button" x-show="o.status === 'menunggu'" @click="tolak(o)" class="flex-1 border border-red-300 text-red-600 rounded-xl py-2 text-sm">Tolak</button>
                    <button type="button" x-show="aksiLabel(o.status)" @click="maju(o)" class="flex-1 bg-primary text-white rounded-xl py-2 text-sm" x-text="aksiLabel(o.status)"></button>
                </div>
            </div>
        </template>
    </div>

    <div x-show="pesan" x-cloak x-transition class="fixed bottom-20 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-sm px-4 py-2 rounded-xl z-50" x-text="pesan"></div>
</div>
@endsection