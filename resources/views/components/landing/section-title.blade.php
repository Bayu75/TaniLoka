@props(['label' => null, 'title', 'subtitle' => null])

<div class="mx-auto mb-12 max-w-2xl text-center">
    @if ($label)
        <span class="mb-4 inline-block rounded-full bg-green-muted px-3 py-1.5 text-xs font-semibold text-primary">{{ $label }}</span>
    @endif
    <h2 class="mb-4 text-3xl font-extrabold text-text md:text-4xl">{!! $title !!}</h2>
    @if ($subtitle)
        <p class="text-text-muted">{{ $subtitle }}</p>
    @endif
</div>
