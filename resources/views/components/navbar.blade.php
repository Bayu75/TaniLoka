@props(['menu', 'peran'])

{{-- Desktop --}}
<nav class="fixed inset-x-0 top-0 z-50 hidden h-16 items-center border-b border-border bg-white/95 px-6 shadow-sm backdrop-blur-sm md:flex">
    <div class="mx-auto flex w-full max-w-6xl items-center justify-between gap-4">
        <x-logo :size="36" />
        <div class="flex items-center gap-1">
            @foreach ($menu as [$route, $label, $icon])
                <x-nav-item :route="$route" :label="$label" :icon="$icon" />
            @endforeach
        </div>
        <form method="POST" action="{{ route('keluar') }}">
            @csrf
            <button class="min-h-11 rounded-xl border-2 border-red-200 px-4 font-bold text-red-600 transition hover:bg-red-50">Keluar</button>
        </form>
    </div>
</nav>

{{-- Bar peran (HP) --}}
<div class="flex items-center justify-between bg-primary-dark px-4 py-2 md:hidden">
    <span class="text-sm text-white/90">Masuk sebagai: <b class="text-white">{{ $peran }}</b></span>
    <form method="POST" action="{{ route('keluar') }}">
        @csrf
        <button class="min-h-10 rounded-xl border-2 border-white/40 px-3 text-sm font-bold text-white">Keluar</button>
    </form>
</div>

{{-- Nav bawah (HP) --}}
<nav class="fixed inset-x-0 bottom-0 z-50 border-t-2 border-border bg-white md:hidden">
    <div class="grid grid-cols-6 gap-0.5 p-1">
        @foreach ($menu as [$route, $label, $icon])
            <x-nav-item :route="$route" :label="$label" :icon="$icon" mobile />
        @endforeach
    </div>
</nav>