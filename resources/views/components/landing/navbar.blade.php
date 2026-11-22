@php
    $menu = ['#tentang' => 'Tentang', '#cara-kerja' => 'Cara Kerja', '#petani' => 'Untuk Petani', '#kuliner' => 'Untuk Kuliner'];
@endphp

<nav x-data="{ open: false }" class="fixed inset-x-0 top-0 z-50 border-b border-border bg-white/95 backdrop-blur-sm">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 md:px-8">
        <x-logo :size="36" />

        <div class="hidden items-center gap-6 text-sm font-medium text-text-muted md:flex">
            @foreach ($menu as $href => $label)
                <a href="{{ $href }}" class="transition-colors hover:text-primary">{{ $label }}</a>
            @endforeach
        </div>

        <div class="hidden items-center gap-2 md:flex">
            <a href="{{ route('masuk') }}" class="rounded-xl border border-primary px-4 py-2 text-sm font-semibold text-primary transition hover:bg-green-muted">Masuk</a>
            <a href="{{ route('daftar') }}" class="rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-dark">Daftar</a>
        </div>

        <button @click="open = !open" class="rounded-lg p-2 text-text md:hidden" aria-label="Buka menu">
            <svg x-show="!open" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            <svg x-show="open" x-cloak width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18"/></svg>
        </button>
    </div>

    <div x-show="open" x-cloak x-transition @click.outside="open = false" class="border-t border-border bg-white px-4 py-4 md:hidden">
        @foreach ($menu as $href => $label)
            <a href="{{ $href }}" @click="open = false" class="block py-2 text-sm font-medium text-text-muted hover:text-primary">{{ $label }}</a>
        @endforeach
        <div class="mt-3 grid grid-cols-2 gap-2">
            <a href="{{ route('masuk') }}" class="rounded-xl border border-primary py-2 text-center text-sm font-semibold text-primary">Masuk</a>
            <a href="{{ route('daftar') }}" class="rounded-xl bg-primary py-2 text-center text-sm font-semibold text-white">Daftar</a>
        </div>
    </div>
</nav>
