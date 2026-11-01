<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'TaniLoka')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-cream text-text antialiased">
    <x-navbar-petani />

    <main class="mx-auto max-w-6xl px-4 pb-24 pt-4 md:pb-10 md:pt-20">
        @if (session('sukses'))
            <div class="mb-4 rounded-xl bg-green-muted p-3 font-medium text-primary">{{ session('sukses') }}</div>
        @endif

        @yield('content')
    </main>
</body>
</html>