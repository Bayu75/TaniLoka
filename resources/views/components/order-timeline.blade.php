
@props([
    'status' => 'menunggu_konfirmasi',
    'riwayatStatus' => [],
    'tanggalDibatalkan' => null,
    'alasanDibatalkan' => null,
    'sudahDinilai' => false,
])

@php
    // Status yang memang tersedia pada migration pesanan
    $tahapan = [
        'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
        'menunggu_pembayaran' => 'Menunggu Pembayaran',
        'diproses' => 'Diproses',
        'dikirim' => 'Dikirim',
        'selesai' => 'Selesai',
    ];

    $riwayat = collect($riwayatStatus);

    $dibatalkan = $status === 'dibatalkan';
    $ditolak = $status === 'ditolak';

    $statusTerakhir = array_search(
        $status,
        array_keys($tahapan)
    );

    // Cari riwayat untuk setiap tahapan
    $riwayatTahapan = [];

    foreach ($tahapan as $key => $nama) {
        $riwayatTahapan[$key] = $riwayat->firstWhere('status', $key);
    }
@endphp

<div class="space-y-0">
    @foreach ($tahapan as $key => $nama)
        @php
            $catatanStatus = $riwayatTahapan[$key];

            $aktif = $key === $status;
            $selesai = $catatanStatus !== null && !$aktif;

            // Tahapan selesai baru dicentang setelah pengusaha memberi penilaian
            if ($key === 'selesai') {
                $selesai = $catatanStatus !== null && $sudahDinilai;
                $aktif = !$sudahDinilai && $key === $status;
            }

            $waktu = $catatanStatus?->created_at
                ? \Carbon\Carbon::parse($catatanStatus->created_at)->format('d M Y, H.i')
                : null;
        @endphp

        <div class="flex gap-4">
            <div class="flex flex-col items-center">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-base font-bold
                    {{ $aktif ? 'border-4 border-yellow-200 bg-yellow-400 text-gray-900' : ($selesai ? 'bg-green-700 text-white' : 'bg-gray-200 text-gray-500') }}">
                    @if ($selesai)
                        ✓
                    @else
                        {{ $loop->iteration }}
                    @endif
                </div>

                @if (!$loop->last || $dibatalkan || $ditolak)
                    <div class="min-h-8 w-0.5 flex-1 bg-gray-200"></div>
                @endif
            </div>

            <div class="pb-5 pt-1">
                <p class="font-bold {{ $aktif || $selesai ? 'text-gray-800' : 'text-gray-500' }}">
                    {{ $nama }}
                </p>

                <p class="mt-1 text-sm {{ $aktif ? 'text-green-700' : 'text-gray-500' }}">
                    @if ($catatanStatus)
                        {{ $waktu }}
                        @if ($catatanStatus->keterangan)
                            <span class="block mt-1">{{ $catatanStatus->keterangan }}</span>
                        @endif
                    @else
                        Belum
                    @endif
                </p>
            </div>
        </div>
    @endforeach

    @if ($dibatalkan || $ditolak)
        @php
            $catatanAkhir = $riwayat->firstWhere('status', $status);

            $waktuAkhir = $catatanAkhir?->created_at
                ? \Carbon\Carbon::parse($catatanAkhir->created_at)->format('d M Y, H.i')
                : ($tanggalDibatalkan ?? null);

            $alasanAkhir = $catatanAkhir?->keterangan
                ?? $alasanDibatalkan
                ?? ($dibatalkan ? 'Dibatalkan pembeli' : 'Pesanan ditolak');
        @endphp

        <div class="flex gap-4">
            <div class="flex flex-col items-center">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-600 text-xl font-bold text-white">
                    ✕
                </div>
            </div>

            <div class="pb-5 pt-1">
                <p class="font-bold text-red-700">
                    {{ $dibatalkan ? 'Dibatalkan' : 'Ditolak' }}
                </p>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $waktuAkhir ?? 'Waktu tidak tersedia' }}
                </p>

                <p class="mt-1 text-sm text-red-600">
                    Alasan: {{ $alasanAkhir }}
                </p>
            </div>
        </div>
    @endif
</div>
