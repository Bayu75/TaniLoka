@props(['variant' => 'primary', 'href' => null])

@php
    $kelas = match ($variant) {
        'secondary' => 'border-2 border-primary text-primary hover:bg-green-muted',
        'accent'    => 'bg-accent text-text hover:bg-accent-dark',
        default     => 'bg-primary text-white hover:bg-primary-dark',
    };
    $kelas = "inline-flex min-h-12 items-center justify-center gap-2 rounded-xl px-5 font-bold transition disabled:cursor-not-allowed disabled:opacity-50 {$kelas}";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $kelas]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $kelas]) }}>{{ $slot }}</button>
@endif