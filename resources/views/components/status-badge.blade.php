
@props(['status' => 'menunggu_pembayaran'])

@php
    [$label, $kelas] = match ($status) {
        'menunggu_pembayaran' => [
            'Menunggu Pembayaran',
            'bg-yellow-100 text-yellow-800'
        ],
        'menunggu_konfirmasi' => [
            'Menunggu Konfirmasi',
            'bg-orange-100 text-orange-800'
        ],
        'diproses' => [
            'Diproses',
            'bg-blue-100 text-blue-800'
        ],
        'dikirim' => [
            'Dikirim',
            'bg-indigo-100 text-indigo-800'
        ],
        'selesai' => [
            'Selesai',
            'bg-green-100 text-green-800'
        ],
        'dibatalkan' => [
            'Dibatalkan',
            'bg-red-100 text-red-800'
        ],
        default => [
            'Status Tidak Diketahui',
            'bg-gray-100 text-gray-700'
        ],
    };
@endphp

<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $kelas }}">
    {{ $label }}
</span>
