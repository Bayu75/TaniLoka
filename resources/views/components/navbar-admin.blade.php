@php
    $menu = [
        ['admin.verifikasi', 'Verifikasi', 'verifikasi'],
        ['admin.pencairan', 'Pencairan', 'pencairan'],
    ];
@endphp

<x-navbar :menu="$menu" peran="Admin" />