@extends('layouts.pengusaha')

@section('title', 'Alamat Saya - TaniLoka')

@section('content')

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

@php
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
            <h1 class="text-xl font-extrabold text-[#1A2A1A]">Alamat Saya</h1>
            <p class="mt-1 text-sm text-[#6B806B]">Kelola alamat pengiriman usaha kuliner Anda</p>
        </div>

    </div>
</div>

{{-- KONTEN HALAMAN ALAMAT --}}
<div class="mx-auto max-w-3xl px-4 py-5 md:py-6 space-y-4">

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
                <h2 class="text-lg font-bold text-gray-900">{{ $alamat->label }}</h2>
                <span class="rounded-full bg-green-100 px-4 py-1.5 text-xs font-bold text-green-700">
                    Alamat Utama
                </span>
            </div>

            <div class="mt-4 space-y-2">
                <p class="font-semibold text-gray-900">
                    {{ $alamat->nama_penerima }} · {{ $alamat->no_hp }}
                </p>
                <p class="text-sm leading-relaxed text-gray-600">{{ $alamat->alamat_lengkap }}</p>
                <p class="text-sm text-gray-600">
                    Kec. {{ $alamat->kecamatan }}, {{ $alamat->kabupaten }}
                    @if($alamat->kode_pos) · {{ $alamat->kode_pos }} @endif
                </p>

                @if($alamat->catatan)
                    <p class="mt-3 text-sm text-gray-500">Catatan: {{ $alamat->catatan }}</p>
                @endif

                @if($alamat->latitude && $alamat->longitude)
                    <a href="https://www.google.com/maps?q={{ $alamat->latitude }},{{ $alamat->longitude }}"
                       target="_blank"
                       class="inline-block text-sm font-semibold text-green-700 hover:underline">
                        📍 Lihat di peta
                    </a>
                @endif
            </div>

            <div class="mt-6 flex flex-wrap gap-3 border-t border-gray-100 pt-5">
                <a href="{{ route('pengusaha.alamat', ['edit' => 1]) }}"
                   class="rounded-xl border-2 border-green-700 px-6 py-2.5 text-sm font-semibold text-green-700 hover:bg-green-50">
                    Ubah
                </a>

                <form action="{{ route('pengusaha.alamat.hapus') }}" method="POST"
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
    @else

        <div class="rounded-2xl border border-green-100 bg-white p-5 shadow-sm md:p-7">

            <div class="mb-6">
                <h2 class="text-lg font-bold text-gray-900">
                    {{ $alamat ? 'Ubah Alamat' : 'Tambah Alamat Baru' }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Masukkan alamat lengkap untuk pengiriman pesanan.
                </p>
            </div>

            {{-- PILIH LOKASI DI PETA --}}
            <div class="mb-6">
                <label class="mb-2 block text-sm font-semibold text-gray-700">Pilih Lokasi Usaha *</label>
                <p class="mb-3 text-xs text-gray-500">
                    Cari alamat atau geser pin pada peta untuk menentukan lokasi usaha.
                </p>

                <div class="mb-3 flex flex-col gap-2 sm:flex-row">
                    <input type="text" id="cari-lokasi"
                           placeholder="Cari nama jalan, tempat, atau kota..."
                           class="min-w-0 flex-1 rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-green-600">
                    <button type="button" id="tombol-cari"
                            class="rounded-xl bg-green-700 px-5 py-3 text-sm font-semibold text-white hover:bg-green-800">
                        Cari Lokasi
                    </button>
                </div>

                <div id="peta-alamat" class="z-0 h-[350px] w-full rounded-xl border border-gray-200 sm:h-[420px]"></div>

                <button type="button" id="tombol-gps"
                        class="mt-3 rounded-xl border border-green-700 px-4 py-2.5 text-sm font-semibold text-green-700 hover:bg-green-50">
                    📍 Gunakan Lokasi Saya
                </button>

                <p id="status-peta" class="mt-2 text-xs text-gray-500">
                    Klik peta atau geser pin untuk memilih lokasi.
                </p>

                {{-- KOORDINAT (ikut terkirim lewat form="form-alamat") --}}
                <input type="hidden" name="latitude" id="latitude" form="form-alamat"
                       value="{{ old('latitude', $alamat->latitude ?? '-8.6500000') }}">
                <input type="hidden" name="longitude" id="longitude" form="form-alamat"
                       value="{{ old('longitude', $alamat->longitude ?? '115.2166670') }}">
            </div>

            <form id="form-alamat" action="{{ route('pengusaha.alamat.simpan') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Label Alamat *</label>
                    <input type="text" name="label"
                           value="{{ old('label', $alamat->label ?? 'Restoran') }}"
                           placeholder="Contoh: Restoran, Dapur Pusat" required
                           class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Nama Penerima *</label>
                    <input type="text" name="nama_penerima"
                           value="{{ old('nama_penerima', $alamat->nama_penerima ?? auth()->user()->name) }}" required
                           class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Nomor WhatsApp *</label>
                    <input type="tel" name="no_hp"
                           value="{{ old('no_hp', $alamat->no_hp ?? auth()->user()->no_wa) }}"
                           placeholder="081234567890" required
                           class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Alamat Lengkap *</label>
                    <textarea name="alamat_lengkap" rows="3" required
                              placeholder="Nama jalan, nomor bangunan, kelurahan..."
                              class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100">{{ old('alamat_lengkap', $alamat->alamat_lengkap ?? '') }}</textarea>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">Kecamatan *</label>
                        <input type="text" name="kecamatan"
                               value="{{ old('kecamatan', $alamat->kecamatan ?? '') }}"
                               placeholder="Contoh: Denpasar Selatan" required
                               class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">Kota / Kabupaten *</label>
                        <input type="text" name="kabupaten"
                               value="{{ old('kabupaten', $alamat->kabupaten ?? '') }}"
                               placeholder="Contoh: Denpasar" required
                               class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Kode Pos</label>
                    <input type="text" name="kode_pos"
                           value="{{ old('kode_pos', $alamat->kode_pos ?? '') }}"
                           placeholder="80222"
                           class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Catatan Tambahan</label>
                    <textarea name="catatan" rows="2"
                              placeholder="Contoh: Pintu masuk berada di sebelah minimarket."
                              class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100">{{ old('catatan', $alamat->catatan ?? '') }}</textarea>
                </div>

                <div class="flex flex-col-reverse justify-end gap-3 border-t border-gray-100 pt-5 sm:flex-row">
                    <a href="{{ $alamat ? route('pengusaha.alamat') : route('pengusaha.beranda') }}"
                       class="rounded-xl border border-gray-200 px-6 py-3 text-center text-sm font-semibold text-gray-600 hover:bg-gray-50">
                        Batal
                    </a>
                    <button type="submit"
                            class="rounded-xl bg-green-700 px-6 py-3 text-sm font-semibold text-white hover:bg-green-800">
                        Simpan Alamat
                    </button>
                </div>
            </form>
        </div>

    @endif

</div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!document.getElementById('peta-alamat')) return;

    const latitude  = document.getElementById('latitude');
    const longitude = document.getElementById('longitude');
    const status    = document.getElementById('status-peta');
    const alamatInput    = document.querySelector('[name="alamat_lengkap"]');
    const kabupatenInput = document.querySelector('[name="kabupaten"]');
    const kecamatanInput = document.querySelector('[name="kecamatan"]');
    const kodePosInput   = document.querySelector('[name="kode_pos"]');

    const awalLat = Number(latitude.value);
    const awalLng = Number(longitude.value);

    const map = L.map('peta-alamat').setView([awalLat, awalLng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    const marker = L.marker([awalLat, awalLng], { draggable: true }).addTo(map);

    async function pilihLokasi(lat, lng) {
        latitude.value  = lat.toFixed(7);
        longitude.value = lng.toFixed(7);
        marker.setLatLng([lat, lng]);
        status.textContent = 'Mengambil informasi alamat...';

        try {
            const response = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&accept-language=id`
            );
            if (!response.ok) throw new Error('Gagal mengambil alamat');

            const data = await response.json();

            if (data.display_name) {
                const d = data.address || {};

                alamatInput.value = data.display_name;
                kabupatenInput.value = d.city || d.town || d.municipality || d.county || '';
                kecamatanInput.value = d.city_district || d.suburb || d.village || '';
                if (d.postcode) kodePosInput.value = d.postcode;

                status.textContent = 'Lokasi berhasil dipilih! Periksa kembali isian di bawah.';
            } else {
                status.textContent = 'Koordinat tersimpan, tetapi alamat tidak ditemukan.';
            }
        } catch (error) {
            status.textContent = 'Koordinat tersimpan. Silakan isi alamat secara manual.';
        }
    }

    map.on('click', e => pilihLokasi(e.latlng.lat, e.latlng.lng));

    marker.on('dragend', function () {
        const p = marker.getLatLng();
        pilihLokasi(p.lat, p.lng);
    });

    document.getElementById('tombol-cari').addEventListener('click', async function () {
        const kataKunci = document.getElementById('cari-lokasi').value;

        if (!kataKunci.trim()) {
            status.textContent = 'Masukkan alamat yang ingin dicari.';
            return;
        }

        status.textContent = 'Sedang mencari lokasi...';

        try {
            const url = 'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&countrycodes=id&q='
                + encodeURIComponent(kataKunci);
            const hasil = await (await fetch(url)).json();

            if (!hasil.length) {
                status.textContent = 'Lokasi tidak ditemukan.';
                return;
            }

            const lat = Number(hasil[0].lat);
            const lng = Number(hasil[0].lon);
            map.setView([lat, lng], 17);
            await pilihLokasi(lat, lng);
        } catch (error) {
            status.textContent = 'Pencarian gagal. Silakan coba lagi.';
        }
    });

    document.getElementById('cari-lokasi').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('tombol-cari').click();
        }
    });

    document.getElementById('tombol-gps').addEventListener('click', function () {
        if (!navigator.geolocation) {
            status.textContent = 'Perangkat tidak mendukung fitur lokasi.';
            return;
        }

        status.textContent = 'Mengambil lokasi perangkat...';

        navigator.geolocation.getCurrentPosition(
            function (pos) {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                map.setView([lat, lng], 17);
                pilihLokasi(lat, lng);
            },
            function () {
                status.textContent = 'Lokasi tidak dapat diakses. Periksa izin GPS browser.';
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    });

    setTimeout(() => map.invalidateSize(), 300);
});
</script>

@endsection
