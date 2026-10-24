<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' — ' : '' }}PLN Services</title>
    <meta name="description" content="{{ $description ?? setting('hero_subtitle') }}">
    <meta name="theme-color" content="#3498db">
    <meta property="og:title" content="{{ $title ?? 'PLN Services' }}">
    <meta property="og:description" content="{{ $description ?? setting('hero_subtitle') }}">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    {{-- Decide before first paint: first-visit loader, or arrive with the content hidden for its slide-in. --}}
    <script>
        (function () {
            var html = document.documentElement;
            html.classList.add('js');
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            try {
                if (sessionStorage.getItem('pln:transition')) {
                    sessionStorage.removeItem('pln:transition');
                    html.classList.add('is-arriving');
                } else if (!sessionStorage.getItem('pln:visited')) {
                    html.classList.add('show-loader');
                }
            } catch (e) {}
        })();
    </script>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if ($ga = setting('ga_measurement_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag() { dataLayer.push(arguments); }
            gtag('js', new Date());
            gtag('config', @json($ga));
        </script>
    @endif
    @stack('head')
</head>
<body class="min-h-screen">
    {{-- First-visit loader: logo and a thin progress line, then a quick fade --}}
    <div class="loader flex-col items-center justify-center bg-white" data-loader role="status" aria-label="Memuat">
        <img src="{{ asset('images/logo.png') }}" alt="PLN Services" class="h-14 w-auto opacity-0" data-loader-logo>
        <div class="mt-7 h-[2px] w-40 overflow-hidden rounded-full bg-line">
            <div class="h-full w-full origin-left bg-brand" style="transform: scaleX(0)" data-loader-bar></div>
        </div>
    </div>

    {{-- Thin progress bar shown while the next page loads --}}
    <div class="nav-progress" data-nav-progress aria-hidden="true"></div>

    {{-- Page wipe: two flat brand panels sweep across the content area (below the header) --}}
    <div class="page-wipe" data-wipe aria-hidden="true">
        <div class="page-wipe-panel bg-brand-100" data-wipe-panel></div>
        <div class="page-wipe-panel flex items-center bg-brand" data-wipe-panel>
            <div class="container-x" data-wipe-label>
                <p class="kicker kicker-light mb-3">PLN Services</p>
                <p class="font-display text-3xl font-bold text-white md:text-5xl" data-wipe-title></p>
            </div>
        </div>
    </div>

    @include('partials.header')

    <main class="page-main">
        {{ $slot }}
    </main>

    @include('partials.footer')

    <button type="button" data-to-top class="pointer-events-none fixed right-5 bottom-5 z-40 flex h-12 w-12 items-center justify-center rounded-md bg-brand text-white opacity-0 shadow-lg transition-opacity duration-300 hover:bg-brand-700" aria-label="Kembali ke atas">
        <x-glyph name="arrow-up" class="h-5 w-5" />
    </button>

    @stack('scripts')
</body>
</html>
