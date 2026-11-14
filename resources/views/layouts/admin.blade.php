<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - TaniLoka')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-cream text-text antialiased">
    <x-navbar-admin />

    <main class="mx-auto max-w-6xl px-4 pb-10 pt-4 md:pt-20">
        @if (session('sukses'))
            <div class="mb-4 rounded-xl bg-green-muted p-3 font-medium text-primary">{{ session('sukses') }}</div>
        @endif
        @if (session('peringatan'))
            <div class="mb-4 rounded-xl bg-accent/20 p-3 font-medium text-text">{{ session('peringatan') }}</div>
        @endif

        @yield('content')
    </main>
</body>
</html>