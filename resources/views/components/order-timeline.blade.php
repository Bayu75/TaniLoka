
@props(['status' => 'menunggu_konfirmasi'])

@php
    $tahapan = [
        'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
        'menunggu_pembayaran' => 'Menunggu Pembayaran',
        'menunggu_verifikasi_pembayaran' => 'Menunggu Verifikasi Pembayaran',
        'diproses' => 'Diproses',
        'dikirim' => 'Dikirim',
        'terkirim' => 'Terkirim',
        'selesai' => 'Selesai',
    ];

    $dibatalkan = $status === 'dibatalkan';

    $urutanStatus = array_search($status, array_keys($tahapan));

    if ($urutanStatus === false || $dibatalkan) {
        $urutanStatus = 0;
    }
@endphp

<div class="space-y-0">
    @foreach ($tahapan as $key => $nama)
        @php
            $urutan = $loop->index;
            $aktif = !$dibatalkan && $urutan === $urutanStatus;
            $selesai = $dibatalkan
                ? $urutan === 0
                : $urutan < $urutanStatus;
        @endphp

        <div class="flex gap-4">
            <div class="flex flex-col items-center">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-base font-bold
                    {{ $aktif ? 'border-4 border-yellow-200 bg-yellow-400 text-gray-900' : ($selesai ? 'bg-green-700 text-white' : 'bg-gray-200 text-gray-500') }}">
                    @if ($selesai)
                        ✓
                    @else
                        {{ $urutan + 1 }}
                    @endif
                </div>

                @if (!$loop->last)
                    <div class="min-h-8 w-0.5 flex-1 bg-gray-200"></div>
                @endif
            </div>

            <div class="pb-5 pt-1">
                <p class="font-bold {{ $aktif || $selesai ? 'text-gray-800' : 'text-gray-500' }}">
                    {{ $nama }}
                </p>

                <p class="mt-1 text-sm {{ $aktif ? 'text-green-700' : 'text-gray-500' }}">
                    @if ($aktif && $urutan === 0)
                        26 Okt, 07.57
                    @elseif ($aktif)
                        Status pesanan saat ini
                    @elseif ($selesai)
                        Selesai
                    @else
                        Belum
                    @endif
                </p>
            </div>
        </div>
    @endforeach

        @if ($dibatalkan)
            <div class="flex gap-4">
                <div class="flex flex-col items-center">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-600 text-xl font-bold text-white">
                        ✕
                    </div>
                </div>

                <div class="pb-5 pt-1">
                    <p class="font-bold text-red-700">
                        Dibatalkan
                    </p>

                    <p class="mt-1 text-sm text-gray-500">
                        {{ $tanggalDibatalkan ?? 'Waktu pembatalan' }}
                    </p>

                    <p class="mt-1 text-sm text-red-600">
                        Alasan: {{ $alasanDibatalkan ?? 'Dibatalkan pembeli' }}
                    </p>
                </div>
            </div>
        @endif
</div>
