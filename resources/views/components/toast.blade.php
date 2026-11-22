
@props([
    'message' => 'Berhasil!',
    'type' => 'success',
])

<div
    x-data="{ tampil: true }"
    x-show="tampil"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-3"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-init="setTimeout(() => tampil = false, 4000)"
    class="fixed bottom-5 right-5 z-50 flex items-center gap-3 rounded-2xl bg-green-700 px-4 py-3 text-white shadow-lg"
    role="status"
>
    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-green-600 text-lg">
        ✓
    </span>

    <p class="text-sm font-semibold">
        {{ $message }}
    </p>

    <button
        type="button"
        @click="tampil = false"
        class="ml-2 text-white/80 transition hover:text-white"
        aria-label="Tutup notifikasi"
    >
        ✕
    </button>
</div>
