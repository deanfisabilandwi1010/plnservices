@props(['title' => 'Dashboard'])
@php
    $modules = config('cms.modules');
    $settings = config('cms.settings');
    $unread = \App\Models\Feedback::where('is_read', false)->count();

    $isActive = function (array $item) {
        return match ($item['type']) {
            'link' => request()->routeIs($item['route']),
            'module' => request()->route('module') === $item['key'],
            'setting' => request()->route('section') === $item['key'],
            default => false,
        };
    };
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>{{ $title }} — Admin PLN Services</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    @fonts
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="min-h-screen">
    <div class="fixed inset-0 z-30 hidden bg-ink/30 lg:hidden" data-sidebar-backdrop></div>

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 flex w-[248px] -translate-x-full flex-col border-r border-line bg-white transition-transform duration-200 lg:translate-x-0" data-sidebar>
        <div class="flex h-16 shrink-0 items-center justify-between border-b border-line px-5">
            <a href="{{ route('admin.dashboard') }}"><img src="{{ asset('images/logo.png') }}" alt="PLN Services" class="h-9 w-auto"></a>
            <button type="button" class="btn-ghost rounded-md p-1.5 lg:hidden" data-sidebar-toggle aria-label="Tutup menu"><x-glyph name="x" class="h-5 w-5" /></button>
        </div>

        <nav class="sidebar-scroll flex-1 space-y-0.5 overflow-y-auto px-3 py-4" aria-label="Menu admin">
            @foreach (config('cms.menu') as $item)
                @if ($item['type'] === 'heading')
                    <p class="px-3 pt-5 pb-1.5 text-[11px] font-semibold tracking-wider text-muted uppercase">{{ $item['label'] }}</p>
                    @continue
                @endif

                @php
                    [$label, $icon, $url] = match ($item['type']) {
                        'link' => [$item['label'], $item['icon'], route($item['route'])],
                        'module' => [$modules[$item['key']]['label'], $modules[$item['key']]['icon'], route('admin.resource.index', $item['key'])],
                        'setting' => [$settings[$item['key']]['label'], $settings[$item['key']]['icon'], route('admin.settings.edit', $item['key'])],
                    };
                @endphp
                <a href="{{ $url }}" @if ($item['external'] ?? false) target="_blank" @endif class="menu-item {{ $isActive($item) ? 'is-active' : '' }}" @if ($isActive($item)) aria-current="page" @endif>
                    <x-glyph :name="$icon" class="h-[18px] w-[18px] shrink-0" />
                    <span class="flex-1">{{ $label }}</span>
                    @if (($item['key'] ?? null) === 'feedbacks' && $unread)
                        <span class="rounded bg-rose-50 px-1.5 py-0.5 text-[11px] font-semibold text-rose-600">{{ $unread }}</span>
                    @endif
                    @if ($item['external'] ?? false)
                        <x-glyph name="external-link" class="h-3.5 w-3.5 text-muted" />
                    @endif
                </a>
            @endforeach
        </nav>
    </aside>

    <div class="lg:pl-[248px]">
        {{-- Top bar --}}
        <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-line bg-white px-4 md:px-6">
            <button type="button" class="btn-ghost -ml-1 rounded-md p-2 lg:hidden" data-sidebar-toggle aria-label="Buka menu"><x-glyph name="menu" class="h-5 w-5" /></button>
            <p class="min-w-0 flex-1 truncate text-sm font-semibold">{{ $title }}</p>
            <a href="{{ route('home') }}" target="_blank" class="btn btn-ghost hidden sm:inline-flex"><x-glyph name="external-link" class="h-4 w-4" /> Lihat website</a>
            <div class="group relative">
                <button type="button" class="flex items-center gap-2 rounded-md py-1 pr-2 pl-1 hover:bg-canvas" aria-haspopup="true">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-50 text-xs font-bold text-primary">{{ auth()->user()->initials() }}</span>
                    <span class="hidden text-sm font-medium md:block">{{ auth()->user()->name }}</span>
                    <x-glyph name="chevron-down" class="h-4 w-4 text-muted" />
                </button>
                <div class="invisible absolute top-full right-0 w-56 pt-1 opacity-0 transition-opacity group-focus-within:visible group-focus-within:opacity-100 group-hover:visible group-hover:opacity-100">
                    <div class="card py-1 shadow-lg">
                        <p class="border-b border-line px-3 py-2 text-xs text-muted">{{ auth()->user()->email }}</p>
                        <a href="{{ route('admin.resource.edit', ['users', auth()->id()]) }}" class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-canvas"><x-glyph name="user-cog" class="h-4 w-4 text-muted" /> Profil saya</a>
                        <form action="{{ route('admin.logout') }}" method="post">
                            @csrf
                            <button class="flex w-full items-center gap-2 px-3 py-2 text-sm text-rose-600 hover:bg-rose-50"><x-glyph name="log-out" class="h-4 w-4" /> Keluar</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Flash messages --}}
        <div class="pointer-events-none fixed top-20 right-4 z-50 space-y-2 md:right-6" role="status">
            @foreach (['success' => ['border-emerald-500', 'text-emerald-600', 'circle-check'], 'error' => ['border-rose-500', 'text-rose-600', 'circle-alert']] as $type => [$border, $color, $icon])
                @if (session($type))
                    <div class="toast pointer-events-auto flex items-center gap-3 rounded-md border border-l-4 border-line {{ $border }} bg-white px-4 py-3 text-sm shadow-md" data-toast>
                        <x-glyph :name="$icon" class="h-5 w-5 {{ $color }}" /> {{ session($type) }}
                    </div>
                @endif
            @endforeach
        </div>

        <main class="space-y-6 px-4 py-6 md:px-6">
            {{ $slot }}
        </main>

        <footer class="px-4 pb-6 text-xs text-muted md:px-6">
            © {{ now()->year }} {{ setting('company_name', 'PT PLN Services') }}
        </footer>
    </div>
</body>
</html>
