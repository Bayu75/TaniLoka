@props([
    'judul',
    'kembali' => url()->previous(),
    'lebar' => 'max-w-3xl',
])

<div class="relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen border-b border-[#D4E8D0] bg-white">
    <div class="mx-auto {{ $lebar }} px-4 py-3 flex items-center gap-3">
        @if ($kembali)
            <a href="{{ $kembali }}"
               class="shrink-0 inline-flex items-center justify-center w-10 h-10 -ml-2 rounded-full text-primary-dark hover:bg-[#EEF6EC] transition"
               aria-label="Kembali">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" viewBox="0 0 24 24">
                    <path d="M0 0h24v24H0z" fill="none" />
                    <path fill="currentColor" d="M21 11H6.414l5.293-5.293l-1.414-1.414L2.586 12l7.707 7.707l1.414-1.414L6.414 13H21z" />
                </svg>
            </a>
        @endif
        <h1 class="text-xl font-extrabold text-[#1A2A1A] truncate">{{ $judul }}</h1>
    </div>
</div>