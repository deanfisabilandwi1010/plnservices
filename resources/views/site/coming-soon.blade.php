<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Coming Soon — PT PLN Services</title>
    <meta name="description" content="Website Korporat Sub Holding ke-5 PT PLN (Persero). PT PLN Services gabungan PT PLN Nusa Daya dan PT PLN Electricity Services.">
    <meta name="theme-color" content="#3498db">
    <meta property="og:title" content="Coming Soon — PT PLN Services">
    <meta property="og:description" content="Website Korporat Sub Holding ke-5 PT PLN (Persero).">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    {{-- Decide before first paint: the page starts empty so the intro is the first thing seen. --}}
    <script nonce="{{ Vite::cspNonce() }}">
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('cs-intro');
            // Safety net: show the plain page if the script never arrives.
            window.csFallback = setTimeout(function () {
                document.documentElement.classList.remove('cs-intro');
            }, 6000);
        }
    </script>

    {{-- Fetched ahead so "Tentang Kami" opens straight after its transition --}}
    <link rel="prefetch" href="{{ route('home') }}">
    <link rel="modulepreload" href="{{ Vite::asset('resources/js/app.js') }}">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/coming-soon.js'])
</head>
<body class="cs" data-coming-soon>
    <canvas class="cs-canvas" data-cs-canvas aria-hidden="true"></canvas>

    {{-- The two companies, shown only while their dots merge into the title --}}
    <p class="cs-tag" style="left: 25%" data-cs-tag aria-hidden="true">PT PLN Nusa Daya</p>
    <p class="cs-tag" style="left: 75%" data-cs-tag aria-hidden="true">PT PLN Electricity Services</p>

    <header class="container-x cs-top">
        @if (file_exists(public_path('images/logos/danantara.png')))
            <img src="{{ asset('images/logos/danantara.png') }}" alt="Danantara Indonesia" class="h-8 w-auto sm:h-9">
        @endif
        <img src="{{ asset('images/logo.png') }}" alt="PLN Services" class="ml-auto h-10 w-auto sm:h-11">
    </header>

    <main class="container-x cs-main">
        <h1 class="cs-title" aria-label="Coming Soon" data-cs-title>
            <span class="cs-word" data-cs-word>Coming</span>
            <span class="cs-word" data-cs-word>Soon</span>
        </h1>
        <p class="cs-sub" data-cs-in data-cs-fade>Website Korporat Sub Holding ke-5 PT PLN (Persero)</p>
        <p class="cs-lead" data-cs-in data-cs-fade>PT PLN Services gabungan PT PLN Nusa Daya dan PT PLN Electricity Services.</p>
        <div class="mt-9" data-cs-in>
            <a href="{{ route('home') }}" class="btn btn-primary cs-btn" data-cs-enter>Tentang Kami <x-glyph name="arrow-right" class="h-4 w-4" /></a>
        </div>
    </main>

    <footer class="cs-foot" data-cs-in data-cs-fade>© {{ now()->year }} {{ setting('company_name', 'PT PLN Services') }}</footer>

    {{-- Ends identical to the site's page wipe (layouts/site), which then takes over --}}
    <div class="cs-wipe" data-cs-wipe aria-hidden="true">
        <div class="bg-brand-100" data-cs-wipe-panel></div>
        <div class="flex items-center bg-brand text-left" data-cs-wipe-panel>
            <div class="container-x" data-cs-wipe-label>
                <p class="kicker kicker-light mb-3">PLN Services</p>
                <p class="font-display text-3xl font-bold text-white md:text-5xl">Selamat Datang</p>
            </div>
        </div>
    </div>
</body>
</html>
