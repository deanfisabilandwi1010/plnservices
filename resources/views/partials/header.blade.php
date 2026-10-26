@php
    $menu = [
        ['label' => 'Home', 'route' => 'home', 'active' => 'home'],
        ['label' => 'Profil', 'route' => 'about', 'active' => ['about', 'management', 'network'], 'children' => [
            ['label' => 'Tentang Kami', 'url' => route('about')],
            ['label' => 'Visi Misi', 'url' => route('about').'#visi-misi'],
            ['label' => 'Tata Nilai', 'url' => route('about').'#tata-nilai'],
            ['label' => 'Profil Direksi', 'url' => route('management').'#direksi'],
            ['label' => 'Profil Komisaris', 'url' => route('management').'#komisaris'],
            ['label' => 'Wilayah Kerja', 'url' => route('network')],
            ['label' => 'Kontak Kami', 'url' => route('contact')],
        ]],
        ['label' => 'Tata Kelola', 'route' => 'governance', 'active' => 'governance', 'children' => [
            ['label' => 'Rapat Umum Pemegang Saham', 'url' => route('governance').'#rups'],
            ['label' => 'Anggaran Dasar', 'url' => route('governance').'#anggaran_dasar'],
            ['label' => 'Kebijakan ESG', 'url' => route('governance').'#esg'],
        ]],
        ['label' => 'Layanan', 'route' => 'services', 'active' => 'services*'],
        ['label' => 'Pengadaan', 'route' => 'procurement', 'active' => 'procurement'],
        ['label' => 'Media', 'route' => 'news', 'active' => ['news*', 'portfolio*'], 'children' => [
            ['label' => 'Berita', 'url' => route('news')],
            ['label' => 'Portofolio', 'url' => route('portfolio')],
        ]],
        ['label' => 'Kontak', 'route' => 'contact', 'active' => 'contact'],
    ];
@endphp

<header class="site-header fixed inset-x-0 top-0 z-50 bg-white" data-header>
    <div class="container-x flex h-[80px] items-center justify-between gap-6">
        <a href="{{ route('home') }}" class="shrink-0 lg:hidden" aria-label="PLN Services — Home">
            <img src="{{ asset('images/logo.png') }}" alt="PLN Services" class="h-10 w-auto">
        </a>

        <div class="hidden items-center gap-6 lg:flex">
        {{-- Danantara Indonesia (holding) logo, as on the PLN corporate sites --}}
        @if (file_exists(public_path('images/logos/danantara.png')))
            <img src="{{ asset('images/logos/danantara.png') }}" alt="Danantara Indonesia" class="h-8 w-auto xl:h-9">
        @endif
        <nav class="flex items-center" aria-label="Navigasi utama">
            @foreach ($menu as $item)
                @php $isActive = request()->routeIs(...(array) $item['active']); @endphp
                @if (isset($item['children']))
                    <div class="has-dropdown relative">
                        <a href="{{ route($item['route']) }}" data-nav-index="{{ $loop->index }}" class="nav-link {{ $isActive ? 'is-active' : '' }}">
                            {{ $item['label'] }} <x-glyph name="chevron-down" class="h-3.5 w-3.5 opacity-60" />
                            @if ($isActive)<span class="nav-active-bar absolute right-3 -bottom-1 left-3 h-[3px] rounded-full bg-brand"></span>@endif
                        </a>
                        <div class="dropdown-panel absolute top-full left-0 min-w-60 pt-5">
                            <ul class="rounded-md bg-white py-2 shadow-[0_0_30px_rgb(127_137_161/0.25)]">
                                @foreach ($item['children'] as $child)
                                    <li>
                                        <a href="{{ $child['url'] }}" data-nav-index="{{ $loop->parent->index }}" class="block px-5 py-2.5 text-[14px] text-[#444] transition hover:pl-6 hover:text-brand">{{ $child['label'] }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @else
                    <a href="{{ route($item['route']) }}" data-nav-index="{{ $loop->index }}" class="nav-link {{ $isActive ? 'is-active' : '' }}">
                        {{ $item['label'] }}
                        @if ($isActive)<span class="nav-active-bar absolute right-3 -bottom-1 left-3 h-[3px] rounded-full bg-brand"></span>@endif
                    </a>
                @endif
            @endforeach
        </nav>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('home') }}" class="hidden shrink-0 lg:block" aria-label="PLN Services — Home">
                <img src="{{ asset('images/logo.png') }}" alt="PLN Services" class="h-11 w-auto">
            </a>
            <button type="button" class="group relative z-[70] flex h-11 w-11 items-center justify-center rounded-md text-ink lg:hidden" data-menu-toggle aria-expanded="false" aria-label="Buka menu">
                <span class="absolute h-0.5 w-6 -translate-y-2 rounded bg-current transition duration-300 group-[.is-open]:translate-y-0 group-[.is-open]:rotate-45"></span>
                <span class="absolute h-0.5 w-6 rounded bg-current transition duration-300 group-[.is-open]:opacity-0"></span>
                <span class="absolute h-0.5 w-6 translate-y-2 rounded bg-current transition duration-300 group-[.is-open]:translate-y-0 group-[.is-open]:-rotate-45"></span>
            </button>
        </div>
    </div>
</header>

{{-- Mobile menu: two panels (resources/js/site/header.js) --}}
<div class="fixed inset-0 z-40 hidden flex-col overflow-hidden" data-mobile-menu data-lenis-prevent>
    <div class="flex-1 overflow-y-auto bg-white px-6 pt-24 pb-8" data-menu-panel>
        <nav class="flex flex-col">
            @foreach ($menu as $item)
                <div class="border-b border-line" data-mobile-item>
                    <a href="{{ route($item['route']) }}" class="flex items-center justify-between py-4 font-display text-xl font-bold {{ request()->routeIs(...(array) $item['active']) ? 'text-brand' : 'text-ink' }}">
                        {{ $item['label'] }} <x-glyph name="arrow-right" class="h-4 w-4 text-brand" />
                    </a>
                    @isset($item['children'])
                        <div class="flex flex-wrap gap-x-5 gap-y-2 pb-4">
                            @foreach ($item['children'] as $child)
                                <a href="{{ $child['url'] }}" class="text-sm text-body hover:text-brand">{{ $child['label'] }}</a>
                            @endforeach
                        </div>
                    @endisset
                </div>
            @endforeach
        </nav>
    </div>
    <div class="bg-brand px-6 py-6 text-sm text-white" data-menu-aside>
        <p class="font-display text-xs font-bold tracking-[0.16em] text-white/70 uppercase">Hubungi Kami</p>
        <div class="mt-2 flex flex-wrap gap-x-6 gap-y-1">
            @if (setting('email'))<a href="mailto:{{ setting('email') }}" class="hover:underline">{{ setting('email') }}</a>@endif
            @if (setting('phone'))<span>Telp {{ setting('phone') }}</span>@endif
            @if (setting('call_center'))<span>Call Center {{ setting('call_center') }}</span>@endif
        </div>
    </div>
</div>
