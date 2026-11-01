@php
    $menu = [
        ['petani.dashboard', 'Dashboard', 'beranda'],
        ['petani.produk', 'Produk', 'produk'],
        ['petani.katalog', 'Katalog', 'katalog'],
        ['petani.pesan', 'Pesan', 'pesan'],
        ['petani.pesanan', 'Pesanan', 'pesanan'],
        ['petani.akun', 'Akun', 'akun'],
    ];
@endphp

<x-navbar :menu="$menu" peran="Petani" />