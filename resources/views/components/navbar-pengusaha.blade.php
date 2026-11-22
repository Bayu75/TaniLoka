@php
    $menu = [
        ['pengusaha.beranda', 'Beranda', 'beranda'],
        ['pengusaha.katalog', 'Katalog', 'katalog'],
        ['pengusaha.pesan', 'Pesan', 'pesan'],
        ['pengusaha.keranjang', 'Keranjang', 'keranjang'],
        ['pengusaha.pesanan', 'Pesanan', 'pesanan'],
        ['pengusaha.akun', 'Akun', 'akun'],
    ];
@endphp

<x-navbar :menu="$menu" peran="Pengusaha Kuliner" />