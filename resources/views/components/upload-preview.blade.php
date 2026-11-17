@props(['name', 'label', 'keterangan' => null, 'accept' => '.jpg,.jpeg,.png'])

<div class="rounded-xl border border-[#888888] px-4 py-3"
     x-data="{
        url: null, nama: '', pdf: false,
        pilih(e) {
            const f = e.target.files[0];
            if (!f) return;
            if (f.size > 2 * 1024 * 1024) { alert('Ukuran file maksimal 2 MB'); this.hapus(); return; }
            if (this.url) URL.revokeObjectURL(this.url);
            this.url = URL.createObjectURL(f);
            this.nama = f.name;
            this.pdf = f.type === 'application/pdf';
        },
        hapus() {
            if (this.url) URL.revokeObjectURL(this.url);
            this.url = null; this.nama = ''; this.pdf = false;
            this.$refs.input.value = '';
        }
     }">
    <label class="mb-1 block font-medium">{{ $label }}</label>
    @if ($keterangan)
        <p class="mb-2 text-xs text-gray-500">{{ $keterangan }}</p>
    @endif

    <input type="file" name="{{ $name }}" accept="{{ $accept }}" x-ref="input" @change="pilih" class="hidden">

    {{-- Belum ada file --}}
    <button type="button" x-show="!url" @click="$refs.input.click()"
            class="flex w-full flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-[#D4E8D0] py-6 text-sm text-[#6B806B] transition hover:border-green-400 hover:bg-green-50/50">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor">
            <path d="M11 16V7.85l-2.6 2.6L7 9l5-5l5 5l-1.4 1.45l-2.6-2.6V16zm-5 4q-.825 0-1.412-.587T4 18v-3h2v3h12v-3h2v3q0 .825-.587 1.413T18 20z" />
        </svg>
        Klik untuk pilih file
    </button>

    {{-- Sudah ada file --}}
    <div x-show="url" x-cloak class="space-y-3">
        <template x-if="!pdf">
            <img :src="url" alt="Preview" class="max-h-56 w-full rounded-lg border border-[#D4E8D0] object-contain bg-gray-50">
        </template>
        <template x-if="pdf">
            <a :href="url" target="_blank" class="flex items-center gap-2 rounded-lg border border-[#D4E8D0] bg-gray-50 px-3 py-3 text-sm text-green-700 hover:underline">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 text-red-500" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M6 2h8l6 6v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2m7 1.5V9h5.5z" />
                </svg>
                <span>Lihat PDF</span>
            </a>
        </template>

        <div class="flex items-center justify-between gap-3">
            <p class="truncate text-xs text-gray-600" x-text="nama"></p>
            <div class="flex shrink-0 gap-2">
                <button type="button" @click="$refs.input.click()"
                        class="rounded-lg border border-[#D4E8D0] px-3 py-1.5 text-xs font-medium text-green-700 hover:bg-green-50">Ganti</button>
                <button type="button" @click="hapus()"
                        class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Hapus</button>
            </div>
        </div>
    </div>

    @error($name) <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>