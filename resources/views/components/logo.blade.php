@props(['size' => 40, 'light' => false])

<a href="{{ route('landing') }}"
   {{ $attributes->merge(['class' => 'flex items-center gap-2.5']) }}>
    <img
        src="{{ asset('images/logo.png') }}"
        alt=""
        width="{{ $size }}"
        height="{{ $size }}"
        class="shrink-0 object-contain"
    >

    <div class="leading-none">
        <span @class([
            'text-xl font-extrabold tracking-tight',
            'text-white' => $light,
            'text-text' => ! $light,
        ])>
            Tani<span class="text-accent">Loka</span>
        </span>

        @if ($size >= 36)
            <span @class([
                'mt-1 block text-[10px] font-medium uppercase tracking-widest',
                'text-white/70' => $light,
                'text-text-muted' => ! $light,
            ])>
                Pasar Tani Bali
            </span>
        @endif
    </div>
</a>