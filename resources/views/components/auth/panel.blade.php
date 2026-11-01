@props(['judul' => 'Pasar tani digital untuk Bali', 'teks'])

<div class="hidden flex-col justify-between bg-gradient-to-br from-primary-dark to-primary p-10 lg:flex lg:w-5/12">
    <x-logo :size="36" light />
    <div>
        <h2 class="mb-4 text-4xl font-extrabold leading-tight text-white">{{ $judul }}</h2>
        <p class="max-w-md text-lg leading-relaxed text-white/75">{{ $teks }}</p>
    </div>
    <p class="text-sm text-white/40">&copy; {{ date('Y') }} TaniLoka</p>
</div>