@props(['items' => [], 'title' => null, 'cta' => null, 'ctaUrl' => null])
{{-- Sticky in-page menu; the underline follows the section in view (resources/js/site/editorial.js). --}}
<div class="section-nav" data-section-nav>
    <div class="container-x flex h-14 items-center gap-5">
        @if ($title)
            <p class="hidden shrink-0 font-display text-sm font-bold text-ink lg:block">{{ $title }}</p>
            <span class="hidden h-5 w-px bg-line lg:block"></span>
        @endif
        <div class="min-w-0 flex-1 overflow-x-auto [scrollbar-width:none]" data-section-scroller>
            <ul class="relative flex w-max items-center">
                @foreach ($items as $id => $label)
                    <li><a href="#{{ $id }}" class="block px-3 py-4 text-sm font-semibold whitespace-nowrap text-body transition-colors hover:text-brand">{{ $label }}</a></li>
                @endforeach
                <li class="absolute bottom-0 left-0 h-[3px] w-0 rounded-full bg-brand opacity-0" data-section-bar aria-hidden="true"></li>
            </ul>
        </div>
        @if ($cta)
            <a href="{{ $ctaUrl }}" class="btn btn-primary hidden shrink-0 !px-5 !py-2 !text-sm sm:inline-flex">{{ $cta }}</a>
        @endif
    </div>
</div>
