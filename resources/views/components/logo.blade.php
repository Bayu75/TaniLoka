@props(['size' => 40, 'light' => false])

<a href="{{ route('landing') }}" {{ $attributes->merge(['class' => 'flex items-center gap-2.5']) }}>
    <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 100 100" fill="none" aria-hidden="true">
        <circle cx="50" cy="50" r="50" fill="#2E7D32" />
        <circle cx="74" cy="26" r="12" fill="#F4B400" />
        <path d="M8 75 28 42 48 75Z" fill="#1B5E20" />
        <path d="M35 75 58 38 81 75Z" fill="#43A047" />
        <path d="M5 82q22-5 45 0t45 0M5 90q22-5 45 0t45 0" stroke="#A5D6A7" stroke-width="3" stroke-linecap="round" />
    </svg>
    <div class="leading-none">
        <span @class(['text-xl font-extrabold tracking-tight', 'text-white' => $light, 'text-text' => ! $light])>
            Tani<span class="text-accent">Loka</span>
        </span>
        @if ($size >= 36)
            <span @class(['mt-1 block text-[10px] font-medium uppercase tracking-widest', 'text-white/70' => $light, 'text-text-muted' => ! $light])>
                Pasar Tani Bali
            </span>
        @endif
    </div>
</a>
