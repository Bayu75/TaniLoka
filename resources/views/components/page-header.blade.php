@props(['judul', 'kembali' => null])

<div class="mb-4 flex items-center gap-3">
    <a href="{{ $kembali ?? url()->previous() }}" class="flex h-11 w-11 items-center justify-center rounded-xl border border-border bg-card hover:bg-cream-dark" aria-label="Kembali">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
    </a>
    <h1 class="text-2xl font-extrabold">{{ $judul }}</h1>
</div>