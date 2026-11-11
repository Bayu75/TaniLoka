
@extends('layouts.pengusaha')

@section('title', 'Alamat Saya - TaniLoka')

@section('content')

<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>
@php
    $alamat = session('alamat_pengusaha');
    $edit = request()->query('edit') == '1';
@endphp

<div class="min-h-screen bg-[#F8FAF5]">

{{-- HEADER HALAMAN --}}
<div class="relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen border-b border-[#D4E8D0] bg-white">

    <div class="relative mx-auto max-w-3xl px-4 py-3">

        {{-- TOMBOL KEMBALI --}}
        <a href="{{ route('pengusaha.akun') }}"
            class="mb-2 inline-flex items-center gap-1 text-sm font-extrabold text-[#087C3E] hover:text-green-800
                   md:absolute md:right-full md:top-1/2 md:mb-0 md:mr-4 md:-translate-y-1/2 md:whitespace-nowrap">

            <span class="text-2xl font-black leading-none">&lt;</span>
            <span>Kembali</span>
        </a>

        {{-- JUDUL DAN DESKRIPSI --}}
        <div>
            <h1 class="text-xl font-extrabold text-[#1A2A1A]">
                Alamat Saya
            </h1>

            <p class="mt-1 text-sm text-[#6B806B]">
                Kelola alamat pengiriman usaha kuliner Anda
            </p>
        </div>

    </div>

</div>


   {{-- KONTEN HALAMAN ALAMAT --}}
<div class="mx-auto max-w-3xl px-4 py-5 md:py-6">

    {{-- NOTIFIKASI --}}
    @if(session('sukses'))
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('sukses') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-600">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- ALAMAT SUDAH ADA --}}
    @if($alamat && !$edit)

        <div class="rounded-2xl border-2 border-green-700 bg-white p-5 shadow-sm md:p-7">

            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-bold text-gray-900">
                    {{ $alamat['label'] }}
                </h2>

                <span class="rounded-full bg-green-100 px-4 py-1.5 text-xs font-bold text-green-700">
                    Alamat Pengiriman
                </span>
            </div>

            <div class="mt-4 space-y-2">
                <p class="font-semibold text-gray-900">
                    {{ $alamat['nama'] }} · {{ $alamat['telepon'] }}
                </p>

                <p class="text-sm leading-relaxed text-gray-600">
                    {{ $alamat['alamat'] }}
                </p>

                <p class="text-sm text-gray-600">
                    {{ $alamat['kota'] }}
                </p>

                @if(!empty($alamat['catatan']))
                    <p class="mt-3 text-sm text-gray-500">
                        Catatan: {{ $alamat['catatan'] }}
                    </p>
                @endif
            </div>

            <div class="mt-6 flex flex-wrap gap-3 border-t border-gray-100 pt-5">

                <a href="{{ route('pengusaha.alamat', ['edit' => 1]) }}"
                   class="rounded-xl border-2 border-green-700 px-6 py-2.5 text-sm font-semibold text-green-700 hover:bg-green-50">
                    Ubah
                </a>

                <form action="{{ route('pengusaha.alamat.hapus') }}"
                      method="POST"
                      onsubmit="return confirm('Yakin ingin menghapus alamat ini?')">
                    @csrf

                    <button type="submit"
                            class="rounded-xl border-2 border-red-200 px-6 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">
                        Hapus
                    </button>
                </form>

            </div>
        </div>

    {{-- FORM TAMBAH ATAU EDIT --}}
    @elseif(!$alamat || $edit)

        <div class="rounded-2xl border border-green-100 bg-white p-5 shadow-sm md:p-7">

            <div class="mb-6">
                <h2 class="text-lg font-bold text-gray-900">
                    {{ $alamat ? 'Ubah Alamat' : 'Tambah Alamat Baru' }}
                </h2>

{{-- PILIH LOKASI DI PETA --}}
<div>
    <label class="mb-2 block text-sm font-semibold text-gray-700">
        Pilih Lokasi Usaha
    </label>

    <p class="mb-3 text-xs text-gray-500">
        Cari alamat atau geser pin pada peta untuk menentukan lokasi usaha.
    </p>

    {{-- PENCARIAN LOKASI --}}
    <div class="mb-3 flex flex-col gap-2 sm:flex-row">
        <input
            type="text"
            id="cari-lokasi"
            placeholder="Cari nama jalan, tempat, atau kota..."
            class="min-w-0 flex-1 rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-green-600"
        >

        <button
            type="button"
            id="tombol-cari"
            class="rounded-xl bg-green-700 px-5 py-3 text-sm font-semibold text-white hover:bg-green-800"
        >
            Cari Lokasi
        </button>
    </div>

    {{-- PETA --}}
    <div
        id="peta-alamat"
        class="z-0 h-[350px] w-full rounded-xl border border-gray-200 sm:h-[420px]"
    ></div>

    {{-- GPS --}}
    <button
        type="button"
        id="tombol-gps"
        class="mt-3 rounded-xl border border-green-700 px-4 py-2.5 text-sm font-semibold text-green-700 hover:bg-green-50"
    >
        📍 Gunakan Lokasi Saya
    </button>

    <p id="status-peta" class="mt-2 text-xs text-gray-500">
        Klik peta atau geser pin untuk memilih lokasi.
    </p>

    {{-- KOORDINAT --}}
    <input
        type="hidden"
        name="latitude"
        id="latitude"
        value="{{ old('latitude', $alamat['latitude'] ?? '-8.650000') }}"
    >

    <input
        type="hidden"
        name="longitude"
        id="longitude"
        value="{{ old('longitude', $alamat['longitude'] ?? '115.216667') }}"
    >
</div>

                <p class="mt-1 text-sm text-gray-500">
                    Masukkan alamat lengkap untuk pengiriman pesanan.
                </p>
            </div>

            <form action="{{ route('pengusaha.alamat.simpan') }}"
                  method="POST"
                  class="space-y-5">
                @csrf

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Label Alamat *
                    </label>

                    <input type="text"
                           name="label"
                           value="{{ old('label', $alamat['label'] ?? 'Restoran') }}"
                           placeholder="Contoh: Restoran, Dapur Pusat"
                           required
                           class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Nama Penerima *
                    </label>

                    <input type="text"
                           name="nama"
                           value="{{ old('nama', $alamat['nama'] ?? session('nama')) }}"
                           required
                           class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Nomor WhatsApp *
                    </label>

                    <input type="tel"
                           name="telepon"
                           value="{{ old('telepon', $alamat['telepon'] ?? '') }}"
                           placeholder="081234567890"
                           required
                           class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Alamat Lengkap *
                    </label>

                    <textarea name="alamat"
                              rows="3"
                              required
                              placeholder="Nama jalan, nomor bangunan, kelurahan, kecamatan..."
                              class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100">{{ old('alamat', $alamat['alamat'] ?? '') }}</textarea>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Kota / Kabupaten *
                    </label>

                    <input type="text"
                           name="kota"
                           value="{{ old('kota', $alamat['kota'] ?? '') }}"
                           placeholder="Contoh: Denpasar, Bali"
                           required
                           class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Catatan Tambahan
                    </label>

                    <textarea name="catatan"
                              rows="2"
                              placeholder="Contoh: Pintu masuk berada di sebelah minimarket."
                              class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100">{{ old('catatan', $alamat['catatan'] ?? '') }}</textarea>
                </div>

                <div class="flex flex-col-reverse justify-end gap-3 border-t border-gray-100 pt-5 sm:flex-row">

                    @if($alamat)
                        <a href="{{ route('pengusaha.alamat') }}"
                           class="rounded-xl border border-gray-200 px-6 py-3 text-center text-sm font-semibold text-gray-600 hover:bg-gray-50">
                            Batal
                        </a>
                    @else
                        <a href="{{ route('pengusaha.beranda') }}"
                           class="rounded-xl border border-gray-200 px-6 py-3 text-center text-sm font-semibold text-gray-600 hover:bg-gray-50">
                            Batal
                        </a>
                    @endif

                    <button type="submit"
                            class="rounded-xl bg-green-700 px-6 py-3 text-sm font-semibold text-white hover:bg-green-800">
                        Simpan Alamat
                    </button>
                </div>

            </form>
        </div>

    @endif

</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
if (!document.getElementById('peta-alamat')) return;

    const awalLat = Number(
        document.getElementById('latitude').value
    );

    const awalLng = Number(
        document.getElementById('longitude').value
    );

    const map = L.map('peta-alamat').setView(
        [awalLat, awalLng], 15
    );

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    let marker = L.marker([awalLat, awalLng], {
        draggable: true
    }).addTo(map);

    const latitude = document.getElementById('latitude');
    const longitude = document.getElementById('longitude');
    const status = document.getElementById('status-peta');
    const alamatInput = document.querySelector('[name="alamat"]');
    const kotaInput = document.querySelector('[name="kota"]');

    // Menyimpan koordinat dan mengambil alamat
    async function pilihLokasi(lat, lng) {

        latitude.value = lat.toFixed(7);
        longitude.value = lng.toFixed(7);

        marker.setLatLng([lat, lng]);

        status.textContent = 'Mengambil informasi alamat...';

        try {
            const response = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&accept-language=id`
            );

            if (!response.ok) {
                throw new Error('Gagal mengambil alamat');
            }

            const data = await response.json();

            if (data.display_name) {
                const detail = data.address || {};

                alamatInput.value = data.display_name;

                const kota =
                    detail.city ||
                    detail.town ||
                    detail.municipality ||
                    detail.county ||
                    '';

                const provinsi = detail.state || '';

                kotaInput.value = [kota, provinsi]
                    .filter(Boolean)
                    .join(', ');

                status.textContent = 'Lokasi berhasil dipilih!';
            } else {
                status.textContent =
                    'Koordinat tersimpan, tetapi alamat tidak ditemukan.';
            }

        } catch (error) {
            status.textContent =
                'Koordinat tersimpan. Silakan isi alamat secara manual.';
        }
    }

    // Klik peta
    map.on('click', function (event) {
        pilihLokasi(
            event.latlng.lat,
            event.latlng.lng
        );
    });

    // Geser pin
    marker.on('dragend', function () {
        const posisi = marker.getLatLng();

        pilihLokasi(
            posisi.lat,
            posisi.lng
        );
    });

    // Cari lokasi
    document.getElementById('tombol-cari')
        .addEventListener('click', async function () {

        const kataKunci = document.getElementById('cari-lokasi').value;

        if (!kataKunci.trim()) {
            status.textContent = 'Masukkan alamat yang ingin dicari.';
            return;
        }

        status.textContent = 'Sedang mencari lokasi...';

        try {
            const url =
                'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&countrycodes=id&q=' +
                encodeURIComponent(kataKunci);

            const response = await fetch(url);
            const hasil = await response.json();

            if (!hasil.length) {
                status.textContent = 'Lokasi tidak ditemukan.';
                return;
            }

            const lokasi = hasil[0];

            const lat = Number(lokasi.lat);
            const lng = Number(lokasi.lon);

            map.setView([lat, lng], 17);

            await pilihLokasi(lat, lng);

        } catch (error) {
            status.textContent = 'Pencarian gagal. Silakan coba lagi.';
        }
    });

    // Cari dengan tombol Enter
    document.getElementById('cari-lokasi')
        .addEventListener('keydown', function (event) {

        if (event.key === 'Enter') {
            event.preventDefault();
            document.getElementById('tombol-cari').click();
        }
    });

    // GPS perangkat
    document.getElementById('tombol-gps')
        .addEventListener('click', function () {

        if (!navigator.geolocation) {
            status.textContent =
                'Perangkat tidak mendukung fitur lokasi.';
            return;
        }

        status.textContent = 'Mengambil lokasi perangkat...';

        navigator.geolocation.getCurrentPosition(
            function (position) {

                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                map.setView([lat, lng], 17);

                pilihLokasi(lat, lng);
            },

            function () {
                status.textContent =
                    'Lokasi tidak dapat diakses. Periksa izin GPS browser.';
            },

            {
                enableHighAccuracy: true,
                timeout: 10000
            }
        );
    });

    setTimeout(function () {
        map.invalidateSize();
    }, 300);

});
</script>

@endsection
