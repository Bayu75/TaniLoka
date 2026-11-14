@php
    $menu = [
        ['admin.verifikasi', 'Verifikasi', 'verifikasi'],
    ];
@endphp

<x-navbar :menu="$menu" peran="Admin" />