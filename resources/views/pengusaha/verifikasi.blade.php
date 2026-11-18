@extends('layouts.pengusaha')

@section('title', 'Verifikasi Akun - TaniLoka')

@section('content')
<x-page-header judul="Verifikasi Akun" :kembali="route('pengusaha.akun')" lebar="max-w-3xl" />

<div class="mx-auto max-w-3xl px-4 py-6">
    <div class="rounded-2xl border border-[#A8CFA0] bg-white p-5 shadow-sm md:p-7">
        <p class="mb-5 text-sm text-[#6B806B]">Lengkapi langkah berikut agar akunmu bisa melakukan checkout.</p>

        {{-- Notifikasi --}}
        @if (session('sukses'))
            <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('sukses') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">{{ session('error') }}</div>
        @endif

        {{-- Status pengajuan --}}
        @if ($verifikasi)
            @php
                $label = [
                    'menunggu'        => ['Menunggu ditinjau', 'bg-yellow-50 text-yellow-700'],
                    'perlu_perbaikan' => ['Perlu perbaikan', 'bg-orange-50 text-orange-700'],
                    'terverifikasi'   => ['Terverifikasi', 'bg-green-50 text-green-700'],
                    'ditolak'         => ['Ditolak', 'bg-red-50 text-red-700'],
                ][$verifikasi->status] ?? ['-', 'bg-gray-50 text-gray-600'];
            @endphp
            <div class="mb-5 rounded-xl px-4 py-3 {{ $label[1] }}">
                <p class="font-medium">Status: {{ $label[0] }}</p>
                @if ($verifikasi->catatan_admin)
                    <p class="mt-1 text-sm">Catatan admin: {{ $verifikasi->catatan_admin }}</p>
                @endif
            </div>
        @endif

        {{-- Checklist kelengkapan --}}
        @php
            $daftarCek = [
                [
                    'ok'    => $cek['profil'],
                    'sudah' => 'Profil usaha sudah lengkap',
                    'belum' => 'Profil usaha belum lengkap',
                    'link'  => route('pengusaha.profil', ['dari' => 'verifikasi']),
                    'aksi'  => 'Lengkapi',
                ],
                [
                    'ok'    => $cek['alamat'],
                    'sudah' => 'Alamat usaha sudah diisi',
                    'belum' => 'Alamat usaha belum diisi',
                    'link'  => route('pengusaha.alamat'),
                    'aksi'  => 'Isi alamat',
                ],
            ];
            $lengkap = $cek['profil'] && $cek['alamat'];
        @endphp
        <div class="mb-5 space-y-3">
            @foreach ($daftarCek as $item)
                <div class="flex items-center justify-between rounded-xl border px-4 py-3
            {{ $item['ok'] ? 'border-green-400 bg-green-50' : 'border-orange-300 bg-orange-50' }}">
                    <span class="flex items-center gap-2 text-sm {{ $item['ok'] ? 'text-green-800' : 'text-orange-800' }}">
                        @if ($item['ok'])
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-green-600" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2a10 10 0 1 0 0 20a10 10 0 0 0 0-20m-1.2 14.2l-4-4l1.4-1.4l2.6 2.6l5.6-5.6l1.4 1.4z" />
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-orange-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="9" />
                            </svg>
                        @endif
                        {{ $item['ok'] ? $item['sudah'] : $item['belum'] }}
                    </span>
                    @unless ($item['ok'])
                        <a href="{{ $item['link'] }}" class="text-sm font-medium text-green-700 hover:underline">{{ $item['aksi'] }}</a>
                    @endunless
                </div>
            @endforeach
        </div>

        {{-- Form pengajuan --}}
        @if ($bisaAjukan)
            <form method="POST" action="{{ route('pengusaha.verifikasi.ajukan') }}" enctype="multipart/form-data"
                  x-data="{ bukaSk: false, sudahBaca: false, setuju: false }" class="space-y-4">
                @csrf

                <x-upload-preview name="nib" label="Dokumen NIB"
                    keterangan="Format JPG, PNG, atau PDF, maksimal 2 MB."
                    accept=".jpg,.jpeg,.png,.pdf" />

                <x-upload-preview name="foto_tempat_usaha" label="Foto Tempat Usaha"
                    keterangan="Foto tampak depan tempat usaha, JPG atau PNG, maksimal 2 MB." />

                <div class="rounded-xl border border-[#A8CFA0] px-4 py-3">
                    <label class="flex items-start gap-2" :class="!sudahBaca && 'opacity-60'">
                        <input type="checkbox" name="setuju_sk" value="1" x-model="setuju" :disabled="!sudahBaca" class="mt-1">
                        <span class="text-sm">
                            Saya telah membaca dan menyetujui
                            <button type="button" @click="bukaSk = true" class="font-medium text-green-700 underline cursor-pointer">Syarat &amp; Ketentuan TaniLoka</button>.
                        </span>
                    </label>
                    <p x-show="!sudahBaca" class="mt-1 text-xs text-gray-500">Baca Syarat &amp; Ketentuan sampai selesai untuk bisa mencentang.</p>
                    @error('setuju_sk') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit"
                        :disabled="!setuju || {{ $lengkap ? 'false' : 'true' }}"
                        class="w-full rounded-xl bg-green-700 py-3 font-medium text-white transition hover:bg-green-800 disabled:cursor-not-allowed disabled:opacity-50">
                    Ajukan Verifikasi
                </button>

                {{-- Modal Syarat & Ketentuan --}}
                @php
                    $syarat = [
                        'Kebenaran data' => 'Saya menyatakan bahwa semua data yang saya isi (identitas, profil usaha, alamat, NIB, dan foto tempat usaha) benar, milik saya atau usaha saya, dan masih berlaku.',
                        'Penggunaan data' => 'Saya menyetujui TaniLoka menyimpan dan memeriksa data tersebut untuk verifikasi akun dan kelancaran transaksi. Data tidak dibagikan ke pihak lain di luar keperluan transaksi, misalnya alamat pengiriman kepada petani.',
                        'Proses verifikasi' => 'Admin TaniLoka akan meninjau pengajuan saya. Pengajuan bisa disetujui, atau dikembalikan dengan catatan perbaikan. Selama menunggu, saya tetap bisa melihat katalog dan menggunakan keranjang, tetapi belum bisa checkout.',
                        'Perubahan data' => 'Jika saya mengubah nama usaha, alamat usaha, atau dokumen setelah terverifikasi, akun saya dapat perlu diverifikasi ulang.',
                        'Kewajiban transaksi' => 'Saya bersedia membayar pesanan sesuai ketentuan, menerima barang sesuai alamat yang saya berikan, dan berkomunikasi dengan sopan kepada petani.',
                        'Pelanggaran' => 'Jika data terbukti palsu atau saya menyalahgunakan platform (penipuan, pesanan fiktif, dan sejenisnya), TaniLoka berhak menolak verifikasi, menangguhkan, atau menonaktifkan akun saya.',
                        'Perubahan ketentuan' => 'TaniLoka dapat memperbarui ketentuan ini, dan pemberitahuannya akan disampaikan melalui platform.',
                    ];
                @endphp
                <div x-show="bukaSk" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
                    <div class="w-full max-w-lg rounded-2xl bg-white p-5" @click.outside="bukaSk = false">
                        <h2 class="mb-3 text-lg font-semibold">Syarat &amp; Ketentuan Verifikasi Akun Pengusaha</h2>
                        <div class="h-72 space-y-3 overflow-y-auto pr-2 text-sm text-gray-700"
                             @scroll="if ($el.scrollTop + $el.clientHeight >= $el.scrollHeight - 10) sudahBaca = true">
                            @foreach ($syarat as $judulSyarat => $isi)
                                <p><b>{{ $loop->iteration }}. {{ $judulSyarat }}.</b> {{ $isi }}</p>
                            @endforeach
                        </div>
                        <button type="button" @click="bukaSk = false; setuju = true" :disabled="!sudahBaca"
                                class="mt-4 w-full rounded-xl bg-green-700 py-2.5 font-medium text-white disabled:opacity-50">
                            <span x-text="sudahBaca ? 'Saya Mengerti' : 'Gulir sampai bawah'"></span>
                        </button>
                    </div>
                </div>
            </form>
        @elseif ($verifikasi?->status === 'menunggu')
            <p class="text-center text-sm text-[#6B806B]">Pengajuanmu sedang ditinjau admin. Kami akan mengabari setelah selesai.</p>
        @endif
    </div>
</div>
@endsection