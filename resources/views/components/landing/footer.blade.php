<footer class="bg-primary-dark px-4 py-12">
    <div class="mx-auto max-w-6xl">
        <div class="mb-8 grid gap-8 md:grid-cols-4">
            <div class="md:col-span-2">
                <x-logo :size="36" light />
                <p class="mt-4 max-w-xs text-sm leading-relaxed text-white/60">
                    Platform B2B pertanian yang menghubungkan petani lokal Bali dengan pelaku usaha kuliner secara langsung.
                </p>
            </div>
            <div>
                <h4 class="mb-4 font-semibold text-white">Platform</h4>
                <a href="#tentang" class="mb-2 block text-sm text-white/60 hover:text-white">Tentang</a>
                <a href="#cara-kerja" class="mb-2 block text-sm text-white/60 hover:text-white">Cara Kerja</a>
                <a href="#petani" class="mb-2 block text-sm text-white/60 hover:text-white">Untuk Petani</a>
                <a href="#kuliner" class="mb-2 block text-sm text-white/60 hover:text-white">Untuk Kuliner</a>
            </div>
            <div>
                <h4 class="mb-4 font-semibold text-white">Bantuan</h4>
                <a href="#" class="mb-2 block text-sm text-white/60 hover:text-white">Pusat Bantuan</a>
                <a href="#" class="mb-2 block text-sm text-white/60 hover:text-white">Hubungi Kami</a>
            </div>
        </div>
        <div class="flex flex-col items-center justify-between gap-2 border-t border-white/10 pt-6 text-sm text-white/40 md:flex-row">
            <span>&copy; {{ date('Y') }} TaniLoka. Semua hak dilindungi.</span>
            <span>Dibuat di Bali</span>
        </div>
    </div>
</footer>
