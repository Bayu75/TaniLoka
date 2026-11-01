@props(['route', 'label', 'icon', 'mobile' => false])

@php
    $aktif = request()->routeIs($route) || request()->routeIs($route . '.*');
    $ikon = [
        'beranda'   => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
        'produk'    => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18M16 10a4 4 0 0 1-8 0"/>',
        'katalog'   => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>',
        'pesan'     => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
        'keranjang' => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>',
        'pesanan'   => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8"/>',
        'akun'      => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    ][$icon];
@endphp

<a href="{{ route($route) }}" @if ($aktif) aria-current="page" @endif
   @class([
       'flex items-center transition font-semibold',
       'gap-2 rounded-xl px-3 min-h-11' => ! $mobile,
       'flex-col justify-center gap-0.5 rounded-2xl py-1.5 min-h-14 text-xs' => $mobile,
       'bg-green-muted text-primary' => $aktif,
       'text-text-muted hover:bg-cream-dark hover:text-primary' => ! $aktif,
   ])>
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $ikon !!}</svg>
    <span>{{ $label }}</span>
</a>