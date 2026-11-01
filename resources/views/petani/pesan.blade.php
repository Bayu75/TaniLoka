@extends('layouts.petani')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6 space-y-4 pb-24">
    <x-page-header judul="Pesan" />
    <p class="text-sm text-text-muted -mt-2">Percakapan dan negosiasi harga dengan pembeli.</p>
    <div id="daftar-kontak" class="space-y-3">
        <a href="#chat-sari" class="pesan-kontak block bg-white border border-border rounded-2xl p-4 hover:border-primary">
            <div class="flex gap-3">
                <div class="w-14 h-14 rounded-xl bg-cover bg-center shrink-0" style="background-image:url('https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=200&h=200&fit=crop')"></div>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between gap-2">
                        <p class="font-semibold text-gray-900">Sari Kitchen</p>
                        <span class="text-xs text-text-muted whitespace-nowrap">09:32</span>
                    </div>
                    <p class="text-sm font-medium text-gray-800 mt-0.5">Bayam Hijau Organik</p>
                    <p class="text-sm text-text-muted mt-0.5">Tawaran balik: Rp 8.000/kg, 20 kg</p>
                    <span class="inline-block mt-2 text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full">Terbuka</span>
                </div>
            </div>
        </a>

        <a href="#chat-bali" class="pesan-kontak block bg-white border-2 border-yellow-400 rounded-2xl p-4 hover:border-primary">
            <div class="flex gap-3">
                <div class="w-14 h-14 rounded-xl bg-cover bg-center shrink-0" style="background-image:url('https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?w=200&h=200&fit=crop')"></div>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between gap-2 items-start">
                        <p class="font-semibold text-gray-900">Bali Delights Restaurant</p>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-xs bg-yellow-400 text-gray-900 font-semibold px-2 py-0.5 rounded-full">Baru</span>
                            <span class="text-xs text-text-muted">10:14</span>
                        </div>
                    </div>
                    <p class="text-sm font-medium text-gray-800 mt-0.5">Wortel Organik</p>
                    <p class="text-sm text-text-muted mt-0.5">Penawaran: Rp 12.500/kg, 40 kg</p>
                    <span class="inline-block mt-2 text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full">Terbuka</span>
                </div>
            </div>
        </a>
    </div>

    <div id="chat-sari" class="pesan-detail hidden scroll-mt-24 space-y-4">
        <div class="flex items-center gap-3 border-b border-border pb-3">
            <a href="#daftar-kontak" class="pesan-kembali text-sm text-primary font-medium">&lt; Kembali</a>
            <div>
                <p class="font-semibold text-gray-900">Sari Kitchen</p>
                <p class="text-sm text-text-muted">Bayam Hijau Organik</p>
            </div>
        </div>

        <div id="thread-sari" class="space-y-4">
            <div class="max-w-[85%] pesan-bubble">
                <div class="bg-white border border-border rounded-2xl p-4 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-semibold text-gray-900">Penawaran Harga</p>
                        <span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full">Ditawar balik</span>
                    </div>
                    <p class="text-xl font-semibold text-gray-900">Rp 7.500 per kg</p>
                    <p class="text-sm text-text-muted">Jumlah: 20 kg · Total Rp 150.000</p>
                    <p class="text-sm text-text-muted">Harga normal Rp 8.500/kg · 12% di bawah harga normal</p>
                    <p class="text-sm text-gray-800 mt-2">Halo Pak Made, saya tertarik dengan bayam organiknya. Bisa harga Rp7.500/kg untuk pembelian 20kg?</p>
                </div>
                <p class="text-xs text-text-muted mt-1">Sari Kitchen · 09:15</p>
            </div>

            <div class="max-w-[85%] ml-auto pesan-bubble">
                <div class="bg-primary text-white rounded-2xl p-4 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-semibold">Tawaran Balik</p>
                        <span class="text-xs bg-yellow-400 text-gray-900 px-2 py-0.5 rounded-full">Menunggu jawaban</span>
                    </div>
                    <p class="text-xl font-semibold">Rp 8.000 per kg</p>
                    <p class="text-sm text-white/90">Jumlah: 20 kg · Total Rp 160.000</p>
                    <p class="text-sm text-white/90">Harga normal Rp 8.500/kg · 6% di bawah harga normal</p>
                    <p class="text-sm mt-2">Salam, terima kasih minatnya. Untuk 20kg saya bisa kasih Rp8.000/kg ya. Sudah termasuk sortir dan packing.</p>
                </div>
                <p class="text-xs text-text-muted mt-1 text-right">Anda · 09:32</p>
            </div>
        </div>

        <form class="pesan-kirim flex gap-2" data-thread="thread-sari">
            <input type="text" name="pesan" placeholder="Tulis pesan..." class="flex-1 border border-border rounded-xl px-4 py-2 bg-white" required>
            <button type="submit" class="bg-primary text-white rounded-xl px-4 py-2 text-sm font-medium">Kirim</button>
        </form>
    </div>

    <div id="chat-bali" class="pesan-detail hidden scroll-mt-24 space-y-4">
        <div class="flex items-center gap-3 border-b border-border pb-3">
            <a href="#daftar-kontak" class="pesan-kembali text-sm text-primary font-medium">&lt; Kembali</a>
            <div>
                <p class="font-semibold text-gray-900">Bali Delights Restaurant</p>
                <p class="text-sm text-text-muted">Wortel Organik</p>
            </div>
        </div>

        <div id="thread-bali" class="space-y-4">
            <div class="max-w-[85%] pesan-bubble">
                <div class="bg-white border border-border rounded-2xl p-4 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-semibold text-gray-900">Penawaran Harga</p>
                        <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full">Terbuka</span>
                    </div>
                    <p class="text-xl font-semibold text-gray-900">Rp 12.500 per kg</p>
                    <p class="text-sm text-text-muted">Jumlah: 40 kg · Total Rp 500.000</p>
                    <p class="text-sm text-text-muted">Harga normal Rp 14.000/kg · 11% di bawah harga normal</p>
                    <p class="text-sm text-gray-800 mt-2">Halo Pak, kami ingin pesan wortel organik 40 kg. Apakah bisa Rp12.500/kg?</p>
                </div>
                <p class="text-xs text-text-muted mt-1">Bali Delights Restaurant · 10:14</p>
            </div>
        </div>

        <form class="pesan-kirim flex gap-2" data-thread="thread-bali">
            <input type="text" name="pesan" placeholder="Tulis pesan..." class="flex-1 border border-border rounded-xl px-4 py-2 bg-white" required>
            <button type="submit" class="bg-primary text-white rounded-xl px-4 py-2 text-sm font-medium">Kirim</button>
        </form>
    </div>
</div>

<script>
(function () {
    function hideAll() {
        document.querySelectorAll('.pesan-detail').forEach(function (el) { el.classList.add('hidden'); });
        var daftar = document.getElementById('daftar-kontak');
        if (daftar) daftar.classList.remove('hidden');
    }
    function showChat(id) {
        hideAll();
        var daftar = document.getElementById('daftar-kontak');
        if (daftar) daftar.classList.add('hidden');
        var box = document.getElementById(id);
        if (box) {
            box.classList.remove('hidden');
            box.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
    function waktuSekarang() {
        var d = new Date();
        return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }
    document.querySelectorAll('.pesan-kontak').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            var id = (link.getAttribute('href') || '').replace('#', '');
            if (id) {
                history.replaceState(null, '', '#' + id);
                showChat(id);
            }
        });
    });
    document.querySelectorAll('.pesan-kembali').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            hideAll();
            history.replaceState(null, '', '{{ url('/petani/pesan') }}');
        });
    });
    document.querySelectorAll('.pesan-kirim').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var input = form.querySelector('input[name="pesan"]');
            var teks = (input && input.value || '').trim();
            if (!teks) return;
            var threadId = form.getAttribute('data-thread');
            var thread = document.getElementById(threadId);
            if (!thread) return;
            var wrap = document.createElement('div');
            wrap.className = 'max-w-[85%] ml-auto pesan-bubble';
            wrap.innerHTML =
                '<div class="bg-primary text-white rounded-2xl p-4 space-y-2">' +
                    '<div class="flex items-center justify-between gap-2">' +
                        '<p class="font-semibold">Pesan</p>' +
                        '<span class="text-xs bg-yellow-400 text-gray-900 px-2 py-0.5 rounded-full">Terkirim</span>' +
                    '</div>' +
                    '<p class="text-sm mt-1"></p>' +
                '</div>' +
                '<p class="text-xs text-text-muted mt-1 text-right">Anda · ' + waktuSekarang() + '</p>';
            wrap.querySelector('p.text-sm').textContent = teks;
            thread.appendChild(wrap);
            input.value = '';
            wrap.scrollIntoView({ behavior: 'smooth', block: 'end' });
        });
    });
    var hash = (window.location.hash || '').replace('#', '');
    if (hash === 'chat-sari' || hash === 'chat-bali') showChat(hash);
})();
</script>
@endsection