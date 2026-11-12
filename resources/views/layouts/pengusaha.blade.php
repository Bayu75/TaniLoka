
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'TaniLoka')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-cream text-text antialiased">
    <x-navbar-pengusaha />

    <main class="@yield('main-class', 'mx-auto max-w-6xl px-4 pb-24 pt-1 md:pb-10 md:pt-16')">

        {{-- NOTIFIKASI BERHASIL --}}
        @if (session('sukses'))
            <div class="mb-4 rounded-xl bg-green-muted p-3 font-medium text-primary">
                {{ session('sukses') }}
            </div>
        @endif

        {{-- NOTIFIKASI ERROR --}}
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
