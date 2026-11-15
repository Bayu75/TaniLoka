@extends('layouts.pengusaha')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">
   <a href="{{ route('pengusaha.akun') }}"
   class="mb-2 inline-flex items-center gap-1 text-sm font-extrabold text-[#087C3E] hover:text-green-800">
    <span class="text-2xl font-black leading-none">&lt;</span>
    <span>Kembali</span>
</a>
    <h1 class="text-2xl font-semibold mt-2 mb-1">Verifikasi Akun</h1>
    <p class="text-sm text-gray-500 mb-6">Lengkapi langkah berikut agar akunmu bisa melakukan checkout.</p>

    @if (session('sukses'))
        <div class="mb-4 rounded-lg bg-green-50 text-green-700 px-4 py-3 text-sm">{{ session('sukses') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-50 text-red-700 px-4 py-3 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Status --}}
    @if ($verifikasi)
        @php
            $label = [
                'menunggu'        => ['Menunggu ditinjau', 'bg-yellow-50 text-yellow-700'],
                'perlu_perbaikan' => ['Perlu perbaikan', 'bg-orange-50 text-orange-700'],
                'terverifikasi'   => ['Terverifikasi', 'bg-green-50 text-green-700'],
                'ditolak'         => ['Ditolak', 'bg-red-50 text-red-700'],
            ][$verifikasi->status];
        @endphp
        <div class="mb-6 rounded-xl px-4 py-3 {{ $label[1] }}">
            <p class="font-medium">Status: {{ $label[0] }}</p>
            @if ($verifikasi->catatan_admin)
                <p class="text-sm mt-1">Catatan admin: {{ $verifikasi->catatan_admin }}</p>
            @endif
        </div>
    @endif

    {{-- Checklist --}}
    <div class="space-y-3 mb-6">
        <div class="flex items-center justify-between rounded-xl border px-4 py-3">
            <span>{{ $cek['profil'] ? '✅' : '⬜' }} Profil usaha lengkap</span>
            @unless ($cek['profil'])
                <a href="{{ route('pengusaha.profil') }}" class="text-sm text-green-700 font-medium">Lengkapi</a>
            @endunless
        </div>
        <div class="flex items-center justify-between rounded-xl border px-4 py-3">
            <span>{{ $cek['alamat'] ? '✅' : '⬜' }} Alamat usaha sudah diisi</span>
            @unless ($cek['alamat'])
                <a href="{{ route('pengusaha.alamat') }}" class="text-sm text-green-700 font-medium">Isi alamat</a>
            @endunless
        </div>
    </div>

    @if ($bisaAjukan)
    <form method="POST" action="{{ route('pengusaha.verifikasi.ajukan') }}" enctype="multipart/form-data"
          x-data="{ bukaSk: false, sudahBaca: false, setuju: false }" class="space-y-4">
        @csrf

        <div class="rounded-xl border px-4 py-3">
            <label class="block font-medium mb-1">Dokumen NIB</label>
            <p class="text-xs text-gray-500 mb-2">Format JPG, PNG, atau PDF, maksimal 2 MB.</p>
            <input type="file" name="nib" accept=".jpg,.jpeg,.png,.pdf" class="text-sm">
            @error('nib') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="rounded-xl border px-4 py-3">
            <label class="block font-medium mb-1">Foto Tempat Usaha</label>
            <p class="text-xs text-gray-500 mb-2">Foto tampak depan tempat usaha, JPG atau PNG, maksimal 2 MB.</p>
            <input type="file" name="foto_tempat_usaha" accept=".jpg,.jpeg,.png" class="text-sm">
            @error('foto_tempat_usaha') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Syarat & Ketentuan --}}
        <div class="rounded-xl border px-4 py-3">
            <label class="flex items-start gap-2" :class="!sudahBaca && 'opacity-60'">
                <input type="checkbox" name="setuju_sk" value="1" x-model="setuju" :disabled="!sudahBaca" class="mt-1">
                <span class="text-sm">
                    Saya telah membaca dan menyetujui
                    <button type="button" @click="bukaSk = true" class="text-green-700 font-medium underline">Syarat & Ketentuan TaniLoka</button>.
                </span>
            </label>
            <p x-show="!sudahBaca" class="text-xs text-gray-500 mt-1">Baca Syarat & Ketentuan sampai selesai untuk bisa mencentang.</p>
            @error('setuju_sk') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit"
                :disabled="!setuju || {{ ($cek['profil'] && $cek['alamat']) ? 'false' : 'true' }}"
                class="w-full rounded-xl bg-green-700 text-white py-3 font-medium disabled:opacity-50 disabled:cursor-not-allowed">
            Ajukan Verifikasi
        </button>

        {{-- Modal S&K --}}
        <div x-show="bukaSk" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
            <div class="bg-white rounded-2xl max-w-lg w-full p-5" @click.outside="bukaSk = false">
                <h2 class="text-lg font-semibold mb-3">Syarat & Ketentuan Verifikasi Akun Pengusaha</h2>
                <div class="h-72 overflow-y-auto text-sm text-gray-700 space-y-3 pr-2"
                     @scroll="if ($el.scrollTop + $el.clientHeight >= $el.scrollHeight - 10) sudahBaca = true">
                    <p><b>1. Kebenaran data.</b> Saya menyatakan bahwa semua data yang saya isi (identitas, profil usaha, alamat, NIB, dan foto tempat usaha) benar, milik saya atau usaha saya, dan masih berlaku.</p>
                    <p><b>2. Penggunaan data.</b> Saya menyetujui TaniLoka menyimpan dan memeriksa data tersebut untuk verifikasi akun dan kelancaran transaksi. Data tidak dibagikan ke pihak lain di luar keperluan transaksi, misalnya alamat pengiriman kepada petani.</p>
                    <p><b>3. Proses verifikasi.</b> Admin TaniLoka akan meninjau pengajuan saya. Pengajuan bisa disetujui, atau dikembalikan dengan catatan perbaikan. Selama menunggu, saya tetap bisa melihat katalog dan menggunakan keranjang, tetapi belum bisa checkout.</p>
                    <p><b>4. Perubahan data.</b> Jika saya mengubah nama usaha, alamat usaha, atau dokumen setelah terverifikasi, akun saya dapat perlu diverifikasi ulang.</p>
                    <p><b>5. Kewajiban transaksi.</b> Saya bersedia membayar pesanan sesuai ketentuan, menerima barang sesuai alamat yang saya berikan, dan berkomunikasi dengan sopan kepada petani.</p>
                    <p><b>6. Pelanggaran.</b> Jika data terbukti palsu atau saya menyalahgunakan platform (penipuan, pesanan fiktif, dan sejenisnya), TaniLoka berhak menolak verifikasi, menangguhkan, atau menonaktifkan akun saya.</p>
                    <p><b>7. Perubahan ketentuan.</b> TaniLoka dapat memperbarui ketentuan ini, dan pemberitahuannya akan disampaikan melalui platform.</p>
                </div>
                <button type="button" @click="bukaSk = false" :disabled="!sudahBaca"
                        class="mt-4 w-full rounded-xl bg-green-700 text-white py-2.5 font-medium disabled:opacity-50">
                    <span x-text="sudahBaca ? 'Saya Mengerti' : 'Gulir sampai bawah'"></span>
                </button>
            </div>
        </div>
    </form>
    @endif
</div>
@endsection