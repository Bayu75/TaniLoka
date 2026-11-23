@extends('layouts.guest')
@section('title', 'Daftar - TaniLoka')

@php
    $input = 'min-h-13 w-full rounded-xl border-2 bg-white px-4 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20';
@endphp

@section('content')
<div class="flex min-h-screen"
    x-data="{
        peran: '{{ old('peran', $peran) }}',
        modal: null,
        setuju: {{ old('setuju') ? 'true' : 'false' }},
        sudahSyarat: {{ old('setuju') ? 'true' : 'false' }},
        sudahPrivasi: {{ old('setuju') ? 'true' : 'false' }},
        get sudahBaca() { return this.sudahSyarat && this.sudahPrivasi },
        buka(tab) {
            this.modal = tab;
            this.$nextTick(() => { this.$refs.isi.scrollTop = 0; this.cekScroll(); });
        },
        cekScroll() {
            const el = this.$refs.isi;
            if (!el) return;
            // Dianggap selesai kalau sudah sampai 10px dari bawah (atau isinya pendek tanpa scroll)
            if (el.scrollTop + el.clientHeight >= el.scrollHeight - 10) {
                if (this.modal === 'syarat') this.sudahSyarat = true;
                if (this.modal === 'privasi') this.sudahPrivasi = true;
            }
        },
    }"
>
    <x-auth.panel judul="Bergabung dengan TaniLoka" teks="Daftar gratis sebagai petani atau pengusaha kuliner, lalu mulai jual atau beli hasil tani Bali." />

    <div class="flex w-full flex-col justify-center p-5 md:p-12 lg:w-7/12">
        <x-logo :size="36" class="mb-6 lg:hidden" />

        <div class="mx-auto w-full max-w-md">
            <h1 class="mb-1 text-3xl font-extrabold">Buat Akun Baru</h1>
            <p class="mb-6 text-text-muted">Sudah punya akun?
                <a href="{{ route('masuk') }}" class="font-bold text-primary hover:underline">Masuk di sini</a>
            </p>

            <form method="POST" action="{{ route('daftar.proses') }}" class="space-y-4" novalidate>
                @csrf
                <input type="hidden" name="peran" :value="peran">

                <div>
                    <p class="mb-1.5 font-semibold">Saya bergabung sebagai:</p>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach (['pengusaha' => ['Pengusaha Kuliner', 'Restoran, kafe, katering'], 'petani' => ['Petani', 'Penjual hasil tani']] as $key => [$judul, $desc])
                            <button type="button" @click="peran = '{{ $key }}'"
                                :class="peran === '{{ $key }}' ? 'border-primary bg-green-muted' : 'border-border bg-card hover:border-primary-light'"
                                class="min-h-24 rounded-2xl border-2 p-3 text-left transition">
                                <p class="text-sm font-bold">{{ $judul }}</p>
                                <p class="text-sm text-text-muted">{{ $desc }}</p>
                            </button>
                        @endforeach
                    </div>
                    @error('peran') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="name" class="mb-1.5 block font-semibold">Nama Lengkap</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Nama sesuai KTP" autocomplete="name"
                        class="{{ $input }} {{ $errors->has('name') ? 'border-red-400' : 'border-border' }}">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="mb-1.5 block font-semibold">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="email@contoh.com" autocomplete="email"
                        class="{{ $input }} {{ $errors->has('email') ? 'border-red-400' : 'border-border' }}">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="no_wa" class="mb-1.5 block font-semibold">Nomor WhatsApp</label>
                    <input id="no_wa" name="no_wa" type="tel" value="{{ old('no_wa') }}" placeholder="08xxxxxxxxxx" autocomplete="tel"
                        class="{{ $input }} {{ $errors->has('no_wa') ? 'border-red-400' : 'border-border' }}">
                    @error('no_wa') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-sm text-text-muted">Isi minimal salah satu: email atau nomor WA.</p>
                </div>

                <div>
                    <label for="password" class="mb-1.5 block font-semibold">Password</label>
                    <input id="password" name="password" type="password" placeholder="Minimal 8 karakter" autocomplete="new-password"
                        class="{{ $input }} {{ $errors->has('password') ? 'border-red-400' : 'border-border' }}">
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1.5 block font-semibold">Konfirmasi Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Ulangi password" autocomplete="new-password"
                        class="{{ $input }} border-border">
                </div>

                <div>
                    <label class="flex items-start gap-2 text-text-muted">
                        <input type="checkbox" name="setuju" value="1" x-model="setuju"
                            :disabled="!sudahBaca"
                            class="mt-0.5 size-5 shrink-0 rounded border-2 border-border accent-primary disabled:cursor-not-allowed disabled:opacity-50">
                        <span>Saya menyetujui
                            <button type="button" @click="buka('syarat')" class="font-semibold text-primary underline cursor-pointer">Syarat &amp; Ketentuan</button>
                            serta
                            <button type="button" @click="buka('privasi')" class="font-semibold text-primary underline cursor-pointer">Kebijakan Privasi</button>
                            TaniLoka.</span>
                    </label>
                    <p x-show="!sudahBaca" class="mt-1 text-xs text-text-muted">
                        Baca Syarat &amp; Ketentuan dan Kebijakan Privasi sampai selesai untuk bisa mencentang.
                        <span x-show="sudahSyarat || sudahPrivasi" x-text="sudahSyarat ? '(Kebijakan Privasi belum dibaca)' : '(Syarat & Ketentuan belum dibaca)'"></span>
                    </p>
                    @error('setuju') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button class="mt-2 min-h-14 w-full rounded-xl bg-accent text-lg font-bold transition hover:bg-accent-dark cursor-pointer">Buat Akun</button>
            </form>
        </div>
    </div>

    {{-- Modal Syarat & Ketentuan / Kebijakan Privasi --}}
    @php
        $syarat = [
            'Tentang TaniLoka' => 'TaniLoka adalah platform yang mempertemukan petani di Bali dengan pengusaha kuliner (restoran, kafe, katering) untuk jual beli hasil tani secara langsung. TaniLoka berperan sebagai perantara dan bukan pihak penjual.',
            'Akun dan pendaftaran' => 'Saya wajib mengisi data yang benar dan terbaru, serta menjaga kerahasiaan password. Satu akun hanya untuk satu orang atau satu usaha, dan segala aktivitas di akun saya menjadi tanggung jawab saya.',
            'Verifikasi akun' => 'Untuk bisa berjualan (petani) atau checkout (pengusaha), akun harus diverifikasi admin dengan dokumen yang diminta. Admin dapat menyetujui, meminta perbaikan, atau menolak pengajuan.',
            'Kewajiban petani' => 'Petani wajib menampilkan foto, harga, stok, dan masa tersedia produk yang sesuai kenyataan, memproses pesanan sesuai tenggat, serta mengirim atau menyiapkan barang sesuai pesanan.',
            'Kewajiban pengusaha' => 'Pengusaha wajib membayar pesanan sesuai tenggat, mengunggah bukti pembayaran yang asli, dan menerima barang sesuai alamat atau metode pengambilan yang dipilih.',
            'Negosiasi dan harga' => 'Harga yang berlaku adalah harga produk saat checkout, atau harga hasil negosiasi yang sudah disepakati kedua pihak melalui fitur chat.',
            'Pesanan dan pembatalan' => 'Pesanan yang tidak dikonfirmasi dan dibayar dalam 24 jam, atau tidak diproses petani dalam 24 jam setelah dibayar, dibatalkan otomatis. Pengusaha hanya bisa membatalkan pesanan sebelum pembayaran diproses.',
            'Larangan' => 'Pengguna dilarang menjual barang terlarang, memberikan data atau bukti palsu, melakukan penipuan, serta berkata kasar atau melecehkan pengguna lain. Pelanggaran dapat membuat akun dibatasi atau dinonaktifkan.',
            'Batas tanggung jawab' => 'Kualitas barang dan pelaksanaan transaksi menjadi tanggung jawab petani dan pengusaha yang bertransaksi. TaniLoka membantu menengahi jika terjadi masalah, sebatas data yang tercatat di platform.',
            'Perubahan ketentuan' => 'TaniLoka dapat memperbarui ketentuan ini sewaktu-waktu. Dengan tetap menggunakan TaniLoka setelah perubahan, saya dianggap menyetujui ketentuan yang baru.',
        ];

        $privasi = [
            'Data yang dikumpulkan' => 'Nama, email, nomor WhatsApp, password (disimpan dalam bentuk terenkripsi), alamat, profil usaha atau lahan, dokumen verifikasi, foto produk, riwayat pesanan, bukti pembayaran, dan isi chat.',
            'Penggunaan data' => 'Data digunakan untuk membuat dan mengelola akun, memverifikasi identitas, memproses pesanan dan pengiriman, menampilkan produk, serta menghubungkan petani dengan pengusaha.',
            'Data yang ditampilkan ke pengguna lain' => 'Nama toko, foto, dan produk petani dapat dilihat pengusaha. Nama, alamat pengiriman, dan nomor kontak hanya dibagikan kepada pihak yang sedang bertransaksi dengan saya.',
            'Dokumen verifikasi' => 'Dokumen seperti KTP atau dokumen usaha hanya dapat dilihat admin TaniLoka untuk keperluan verifikasi dan tidak ditampilkan kepada pengguna lain.',
            'Penyimpanan dan keamanan' => 'Data disimpan di server yang digunakan TaniLoka dan dilindungi dengan langkah keamanan yang wajar. Meski begitu, tidak ada sistem yang sepenuhnya bebas risiko.',
            'Berbagi dengan pihak ketiga' => 'TaniLoka tidak menjual data pribadi pengguna. Data hanya dibagikan jika diperlukan untuk menjalankan layanan atau diwajibkan oleh hukum.',
            'Hak pengguna' => 'Saya dapat melihat dan memperbarui data akun saya, serta meminta penghapusan akun melalui admin TaniLoka, dengan tetap memperhatikan riwayat transaksi yang masih berjalan.',
            'Perubahan kebijakan' => 'Kebijakan ini dapat diperbarui sewaktu-waktu, dan versi terbaru selalu tersedia di halaman pendaftaran.',
        ];
    @endphp

    <div x-show="modal" x-cloak x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        @keydown.escape.window="modal = null" @click.self="modal = null">
        <div class="flex max-h-[85vh] w-full max-w-2xl flex-col rounded-2xl bg-white shadow-xl">

            {{-- Tab --}}
            <div class="flex border-b border-border">
                <button type="button" @click="buka('syarat')"
                    :class="modal === 'syarat' ? 'border-primary text-primary' : 'border-transparent text-text-muted'"
                    class="flex-1 border-b-2 px-4 py-3 font-semibold cursor-pointer">
                    Syarat &amp; Ketentuan <span x-show="sudahSyarat" class="text-primary">✓</span>
                </button>
                <button type="button" @click="buka('privasi')"
                    :class="modal === 'privasi' ? 'border-primary text-primary' : 'border-transparent text-text-muted'"
                    class="flex-1 border-b-2 px-4 py-3 font-semibold cursor-pointer">
                    Kebijakan Privasi <span x-show="sudahPrivasi" class="text-primary">✓</span>
                </button>
            </div>

            {{-- Isi --}}
            <div x-ref="isi" @scroll.debounce.50ms="cekScroll()"
                class="flex-1 space-y-3 overflow-y-auto p-5 text-sm leading-relaxed text-text-muted">
                <div x-show="modal === 'syarat'" class="space-y-3">
                    <p>Dengan membuat akun TaniLoka, saya menyetujui ketentuan berikut:</p>
                    @foreach ($syarat as $judul => $isi)
                        <p><b class="text-text">{{ $loop->iteration }}. {{ $judul }}.</b> {{ $isi }}</p>
                    @endforeach
                </div>
                <div x-show="modal === 'privasi'" class="space-y-3">
                    <p>Kebijakan ini menjelaskan bagaimana TaniLoka mengumpulkan, menggunakan, dan melindungi data pengguna.</p>
                    @foreach ($privasi as $judul => $isi)
                        <p><b class="text-text">{{ $loop->iteration }}. {{ $judul }}.</b> {{ $isi }}</p>
                    @endforeach
                </div>
                <p class="pt-2 text-xs">Terakhir diperbarui: {{ \Illuminate\Support\Carbon::parse('2026-11-23')->translatedFormat('d F Y') }}</p>
            </div>

            {{-- Tombol --}}
            <div class="flex items-center justify-between gap-3 border-t border-border p-4">
                <p class="text-xs text-text-muted" x-show="!sudahBaca">Scroll sampai bawah di kedua tab.</p>
                <div class="ml-auto flex gap-3">
                    <button type="button" @click="modal = null"
                        class="min-h-11 rounded-xl border-2 border-border px-5 font-semibold cursor-pointer">Tutup</button>
                    <template x-if="modal === 'syarat' && !sudahPrivasi">
                        <button type="button" @click="buka('privasi')" :disabled="!sudahSyarat"
                            class="min-h-11 rounded-xl bg-primary px-5 font-semibold text-white hover:opacity-90 cursor-pointer disabled:cursor-not-allowed disabled:opacity-50">
                            Lanjut ke Kebijakan Privasi
                        </button>
                    </template>
                    <template x-if="!(modal === 'syarat' && !sudahPrivasi)">
                        <button type="button" @click="setuju = true; modal = null" :disabled="!sudahBaca"
                            class="min-h-11 rounded-xl bg-primary px-5 font-semibold text-white hover:opacity-90 cursor-pointer disabled:cursor-not-allowed disabled:opacity-50">
                            Saya Setuju
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection