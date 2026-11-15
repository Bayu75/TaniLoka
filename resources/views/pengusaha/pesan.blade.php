
@extends('layouts.pengusaha')

@section('title', 'Pesan - TaniLoka')

@section('content')

@php
    // Status sementara untuk pengujian tampilan.
    // Ubah menjadi 'disepakati' untuk mencoba status berhasil.
    $statusNegosiasi = 'terbuka';
@endphp

<div
    x-data="{
        kataKunci: '',

        percakapan: [
            {
                id: 'pak-made-sukartha',
                nama: 'Pak Made Sukartha',
                produk: 'Bayam Hijau Organik',
                pesan: 'Tawaran balik: Rp 8.000/kg, 20 kg',
                waktu: '09:32',
                status: 'terbuka'
            },
            {
                id: 'Pak-Gede-Setiawan',
                nama: 'Pak Gede Setiawan',
                produk: 'Bayam Putih Lokal',
                pesan: 'Penawaran Rp 33.300/kg untuk 5 kg diterima',
                waktu: '12:48',
                status: 'disepakati'
            },
            {
                id: 'Bu-Sari-Wulandari',
                nama: 'Bu Sari Wulandari',
                produk: 'Tomat Merah',
                pesan: 'Penawaran: Rp 12.000/kg, 10 kg',
                waktu: '11:20',
                status: 'terbuka'
            },
            {
                id: 'Pak-Ketut-Suardana',
                nama: 'Pak Ketut Suardana',
                produk: 'Cabai Rawit Merah',
                pesan: 'Penawaran Rp 45.000/kg, 5 kg disepakati',
                waktu: '10:15',
                status: 'disepakati'
            },
            {
                id: 'Bu-Komang-Sri',
                nama: 'Bu Komang Sri',
                produk: 'Beras Organik',
                pesan: 'Tawaran balik: Rp 18.000/kg, 25 kg',
                waktu: '08:45',
                status: 'terbuka'
            },
            {
                id: 'Pak-Wayan-Dharma',
                nama: 'Pak Wayan Dharma',
                produk: 'Jagung Manis',
                pesan: 'Penawaran: Rp 7.500/kg, 15 kg',
                waktu: '08:10',
                status: 'terbuka'
            }
        ],

        get hasilPencarian() {
            return this.percakapan.filter(item => {
                const kata = this.kataKunci.toLowerCase().trim();

                return (
                    item.nama.toLowerCase().includes(kata) ||
                    item.produk.toLowerCase().includes(kata)
                );
            });
        }
    }"
    class="mx-auto max-w-2xl space-y-4"
>

    {{-- JUDUL HALAMAN --}}
    <div class="relative left-1/2 -mt-4 w-screen -translate-x-1/2 border-b border-green-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-5">
            <div class="mx-auto max-w-2xl">
                <h1 class="text-2xl font-extrabold text-gray-900">
                    Pesan
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    Percakapan dan negosiasi harga dengan petani.
                </p>
            </div>
        </div>
    </div>


    {{-- KOLOM PENCARIAN --}}
    <div>
        <input
            type="text"
            x-model="kataKunci"
            placeholder="Cari nama atau produk..."
            class="w-full rounded-xl border border-green-200 bg-white px-4 py-3 text-sm outline-none transition placeholder:text-gray-400 focus:border-green-500 focus:ring-2 focus:ring-green-100"
        >
    </div>


    {{-- DAFTAR PERCAKAPAN --}}
    <div class="space-y-3">

        <template
            x-for="item in hasilPencarian"
            :key="item.id"
        >
            <a
                :href="'/pengusaha/pesan/' + item.id"
                class="flex items-start gap-3 rounded-2xl bg-white p-3 transition hover:shadow-md"
                style="border: 2px solid #C8E8D0;"
            >

                {{-- GAMBAR PRODUK --}}
                <img
                    src="{{ asset('images/products/bayam.png') }}"
                    alt="Produk"
                    class="mt-1 h-14 w-14 shrink-0 rounded-xl object-cover"
                >

                {{-- INFORMASI PERCAKAPAN --}}
                <div class="min-w-0 flex-1">

                    <h2
                        class="truncate text-sm font-bold text-gray-900"
                        x-text="item.nama"
                    ></h2>

                    <p
                        class="mt-1 text-sm text-green-700"
                        x-text="item.produk"
                    ></p>

                    <p
                        class="mt-1 text-sm font-semibold text-gray-800"
                        x-text="item.pesan"
                    ></p>

                    {{-- STATUS --}}
                    <template x-if="item.status === 'terbuka'">
                        <span
                            class="mt-1 inline-block rounded-full px-2 py-0.5 text-xs font-semibold"
                            style="border: 1px solid #F59E0B; background-color: #FFF7E6; color: #B45309;"
                        >
                            Terbuka
                        </span>
                    </template>

                    <template x-if="item.status === 'disepakati'">
                        <span
                            class="mt-1 inline-block rounded-full px-2 py-0.5 text-xs font-semibold"
                            style="border: 1px solid #16A34A; background-color: #ECFDF3; color: #15803D;"
                        >
                            Disepakati
                        </span>
                    </template>

                </div>

                {{-- WAKTU --}}
                <span
                    class="shrink-0 text-xs text-gray-500"
                    x-text="item.waktu"
                ></span>

            </a>
        </template>


            {{-- JIKA HASIL TIDAK DITEMUKAN --}}
            <div
                x-show="hasilPencarian.length === 0"
                x-cloak
                class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-green-200 bg-white px-6 text-center"
                style="height: 190px; min-height: 190px; box-sizing: border-box;"
            >
                <div class="mb-3 text-4xl">🔍</div>

                <p class="font-semibold text-gray-700">
                    Percakapan tidak ditemukan
                </p>

                <p class="mt-2 text-sm text-gray-400">
                    Coba cari menggunakan nama petani atau nama produk lainnya.
                </p>
            </div>


    </div>

</div>

@endsection
