@extends('layouts.petani')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6 space-y-5 pb-24">
    <x-page-header judul="Bantuan & Informasi" />

    <a href="https://wa.me/6281234567890" target="_blank" class="block w-full text-center bg-primary text-white font-semibold rounded-xl py-3">
        Hubungi via WhatsApp
    </a>

    <section>
        <h2 class="font-semibold text-gray-900 mb-3">Pertanyaan Umum</h2>
        <div class="space-y-2">
            <details open class="bg-white border border-border rounded-2xl p-4">
                <summary class="font-medium cursor-pointer list-none flex justify-between items-center">
                    Bagaimana cara memesan?
                    <span class="text-primary text-sm">Tutup</span>
                </summary>
                <p class="text-sm text-text-muted mt-2">Buka Katalog, pilih produk petani, masukkan jumlah (kg), masukkan ke keranjang, pilih pengiriman dan pembayaran, lalu tekan Kirim Pesanan.</p>
            </details>
            <details class="bg-white border border-border rounded-2xl p-4">
                <summary class="font-medium cursor-pointer list-none flex justify-between items-center">
                    Apa arti status pesanan dan tenggatnya?
                    <span class="text-primary text-sm">Buka</span>
                </summary>
                <p class="text-sm text-text-muted mt-2">Status meliputi Menunggu, Diproses, Dikirim, dan Selesai. Batas waktu konfirmasi biasanya 1x24 jam.</p>
            </details>
            <details class="bg-white border border-border rounded-2xl p-4">
                <summary class="font-medium cursor-pointer list-none flex justify-between items-center">
                    Bagaimana pembayaran dan unggah bukti?
                    <span class="text-primary text-sm">Buka</span>
                </summary>
                <p class="text-sm text-text-muted mt-2">Setelah checkout, unggah bukti transfer agar petani dapat memverifikasi pembayaran.</p>
            </details>
            <details class="bg-white border border-border rounded-2xl p-4">
                <summary class="font-medium cursor-pointer list-none flex justify-between items-center">
                    Bagaimana cara menawar harga (negosiasi)?
                    <span class="text-primary text-sm">Buka</span>
                </summary>
                <p class="text-sm text-text-muted mt-2">Gunakan menu Pesan untuk negosiasi harga dan percakapan dengan petani.</p>
            </details>
            <details class="bg-white border border-border rounded-2xl p-4">
                <summary class="font-medium cursor-pointer list-none flex justify-between items-center">
                    Mengapa akun saya perlu diverifikasi?
                    <span class="text-primary text-sm">Buka</span>
                </summary>
                <p class="text-sm text-text-muted mt-2">Verifikasi memastikan petani terdaftar resmi sehingga produk tampil di katalog dan bisa dipesan.</p>
            </details>
            <details class="bg-white border border-border rounded-2xl p-4">
                <summary class="font-medium cursor-pointer list-none flex justify-between items-center">
                    Apa itu Minimal pesan?
                    <span class="text-primary text-sm">Buka</span>
                </summary>
                <p class="text-sm text-text-muted mt-2">Setiap produk memiliki minimal pesan (kg) yang tercantum di kartu produk.</p>
            </details>
            <details class="bg-white border border-border rounded-2xl p-4">
                <summary class="font-medium cursor-pointer list-none flex justify-between items-center">
                    Bagaimana mencari produk berdasarkan kategori?
                    <span class="text-primary text-sm">Buka</span>
                </summary>
                <p class="text-sm text-text-muted mt-2">Di Katalog, gunakan filter kategori untuk menemukan produk yang dibutuhkan.</p>
            </details>
        </div>
    </section>

    <section class="bg-white border border-border rounded-2xl p-4 space-y-2 text-sm">
        <h2 class="font-semibold text-gray-900">Kontak & Jam Layanan</h2>
        <p>WhatsApp: 0812-3456-7890</p>
        <p>Email: halo@taniloka.id</p>
        <p>Jam layanan: Senin–Sabtu, 08.00–17.00 WITA</p>
        <p>Alamat: Denpasar, Bali</p>
    </section>

    <section class="bg-primary/10 border border-primary/20 rounded-2xl p-4">
        <h2 class="font-semibold text-gray-900 mb-1">Tentang TaniLoka</h2>
        <p class="text-sm text-text-muted">TaniLoka menghubungkan petani Bali langsung dengan pengusaha kuliner agar harga lebih adil dan produk tetap segar.</p>
    </section>
</div>
@endsection