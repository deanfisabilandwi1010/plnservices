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
@php
    $socials = collect(['instagram' => 'instagram', 'linkedin' => 'linkedin', 'youtube' => 'youtube', 'facebook' => 'facebook'])
        ->map(fn ($icon, $key) => ['icon' => $icon, 'url' => setting($key), 'label' => ucfirst($key)])
        ->filter(fn ($s) => filled($s['url']));
@endphp
<div class="fixed inset-0 z-40 hidden flex-col overflow-hidden lg:hidden" data-mobile-menu data-lenis-prevent>
    <div class="relative flex-1 overflow-x-hidden overflow-y-auto bg-white px-6 pt-24 pb-8" data-menu-panel>
        <x-line-art variant="network" mode="static" class="pointer-events-none absolute -right-20 bottom-4 w-[340px] opacity-20" data-menu-art />

        <p class="kicker mb-3" data-mobile-item>Menu</p>
        <nav class="relative" aria-label="Navigasi utama (mobile)" data-accordion>
            @foreach ($menu as $item)
                @php $isActive = request()->routeIs(...(array) $item['active']); @endphp
                <div class="acc-item" data-acc-item data-mobile-item>
                    <div class="flex items-center gap-3">
                        <a href="{{ route($item['route']) }}" class="group flex flex-1 items-baseline gap-4 py-3.5">
                            <span class="w-6 shrink-0 text-xs font-bold tabular-nums {{ $isActive ? 'text-brand' : 'text-subtle' }}">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="font-display text-[1.65rem] leading-tight font-bold transition-colors group-active:text-brand {{ $isActive ? 'text-brand' : 'text-ink' }}">{{ $item['label'] }}</span>
                            @if ($isActive)<span class="mb-1.5 h-1.5 w-1.5 shrink-0 self-end rounded-full bg-volt ring-2 ring-brand"></span>@endif
                        </a>
                        @isset($item['children'])
                            <button type="button" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-mist text-brand" data-acc-toggle aria-expanded="false" aria-label="Submenu {{ $item['label'] }}">
                                <span class="acc-plus"></span>
                            </button>
                        @else
                            <x-glyph name="arrow-up-right" class="mr-3 h-5 w-5 shrink-0 text-brand" />
                        @endisset
                    </div>
                    @isset($item['children'])
                        <div class="acc-body">
                            <div>
                                <ul class="space-y-0.5 pb-4 pl-10">
                                    @foreach ($item['children'] as $child)
                                        <li><a href="{{ $child['url'] }}" class="flex items-center gap-2.5 py-1.5 text-[15px] text-body active:text-brand"><span class="h-px w-3 bg-brand"></span>{{ $child['label'] }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endisset
                </div>
            @endforeach
        </nav>

        @if (file_exists(public_path('images/logos/danantara.png')))
            <div class="relative mt-8 flex items-center gap-3 text-[11px] leading-snug text-subtle" data-mobile-item>
                <img src="{{ asset('images/logos/danantara.png') }}" alt="Danantara Indonesia" class="h-7 w-auto shrink-0">
                <span>Bagian dari PLN Group &amp; Danantara Indonesia</span>
            </div>
        @endif
    </div>

    <div class="bg-brand px-6 pt-5 pb-6 text-sm text-white" data-menu-aside>
        <div class="flex items-center justify-between gap-4">
            <p class="font-display text-xs font-bold tracking-[0.16em] text-white/75 uppercase">Hubungi Kami</p>
            @if ($socials->isNotEmpty())
                <div class="flex gap-2">
                    @foreach ($socials as $social)
                        <a href="{{ $social['url'] }}" target="_blank" rel="noopener" class="flex h-8 w-8 items-center justify-center rounded-full border border-white/40 transition active:bg-white active:text-brand" aria-label="{{ $social['label'] }}"><x-glyph :name="$social['icon']" class="h-3.5 w-3.5" /></a>
                    @endforeach
                </div>
            @endif
        </div>
        <ul class="mt-3 space-y-1.5">
            @if (setting('email'))<li class="flex items-center gap-2.5"><x-glyph name="mail" class="h-4 w-4 shrink-0 text-white/70" /><a href="mailto:{{ setting('email') }}">{{ setting('email') }}</a></li>@endif
            @if (setting('phone'))<li class="flex items-center gap-2.5"><x-glyph name="phone" class="h-4 w-4 shrink-0 text-white/70" />Telp {{ setting('phone') }}</li>@endif
            @if (setting('call_center'))<li class="flex items-center gap-2.5"><x-glyph name="headset" class="h-4 w-4 shrink-0 text-white/70" />Call Center {{ setting('call_center') }}</li>@endif
        </ul>
        <a href="{{ route('contact') }}" class="btn btn-accent mt-4 w-full">Kirim Pesan <x-glyph name="arrow-right" class="h-4 w-4" /></a>
    </div>
</div>
