@props(['eyebrow' => null, 'title', 'subtitle' => null, 'crumbs' => []])
<section class="relative overflow-hidden bg-mist pt-[124px] pb-14 md:pt-[140px] md:pb-16">
    <x-pylons class="pointer-events-none absolute right-0 bottom-0 hidden h-[80%] w-[38%] text-brand/25 lg:block" preserveAspectRatio="xMaxYMax meet" wire="#3498db" />

    <div class="container-x relative">
        <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm text-subtle" data-reveal="fade" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="transition hover:text-brand">Home</a>
            @foreach ($crumbs as $label => $url)
                <span>/</span>
                @if ($url)
                    <a href="{{ $url }}" class="transition hover:text-brand">{{ $label }}</a>
                @else
                    <span class="text-brand">{{ $label }}</span>
                @endif
            @endforeach
        </nav>

        @if ($eyebrow)
            <p class="kicker mb-4" data-reveal="fade">{{ $eyebrow }}</p>
        @endif

        <h1 class="max-w-3xl text-[2.25rem] leading-[1.15] font-bold md:text-5xl" data-split>{{ $title }}</h1>

        @if ($subtitle)
            <p class="mt-5 max-w-2xl text-[1.05rem] leading-relaxed" data-reveal data-delay="0.2">{{ $subtitle }}</p>
        @endif

        {{ $slot }}
    </div>
</section>
