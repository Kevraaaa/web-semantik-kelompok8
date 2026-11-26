<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#182415">
    <script>
        try {
            const savedTheme = localStorage.getItem('jf_theme');
            if (savedTheme === 'light' || savedTheme === 'dark') {
                document.documentElement.dataset.theme = savedTheme;
                document.querySelector('meta[name="theme-color"]').content = savedTheme === 'light' ? '#F5F7F2' : '#182415';
            }
        } catch (_) {}
    </script>
    <meta name="description" content="Jelajahi keanekaragaman fauna Pulau Kalimantan melalui eksplorasi berbasis semantik.">
    <title>@yield('title', 'Jejak Fauna Kalimantan')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body data-page="@yield('page', 'default')" data-user-scope="{{ auth()->id() ?? 'guest' }}">
    <a class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-fresh-grown focus:px-4 focus:py-2 focus:text-moss-green" href="#konten">Lewati ke konten</a>
    <x-navbar />
    <main id="konten">
        @yield('content')
    </main>
    <x-footer />
    <div class="toast-region" data-toast-region aria-live="polite" aria-atomic="false"></div>
    @stack('scripts')
</body>
</html>
