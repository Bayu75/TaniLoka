@props(['judul', 'kembali' => null])

<div class="mb-4">
    @if ($kembali)
        <a
            href="{{ $kembali }}"
            class="mb-3 inline-flex items-center gap-1 text-sm font-semibold text-green-700 hover:text-green-800"
        >
            <svg
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <path d="M15 18l-6-6 6-6" />
            </svg>
            <span>Kembali</span>
        </a>
    @endif

    <h1 class="text-2xl font-extrabold">{{ $judul }}</h1>
</div>