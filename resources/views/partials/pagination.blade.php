@if ($paginator->hasPages())
    <nav class="flex items-center justify-center gap-2" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="flex h-11 w-11 items-center justify-center rounded-full border border-current/10 opacity-30"><x-glyph name="chevron-left" class="h-4 w-4" /></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="flex h-11 w-11 items-center justify-center rounded-full border border-current/15 transition hover:border-sky hover:bg-sky hover:text-white" aria-label="Sebelumnya"><x-glyph name="chevron-left" class="h-4 w-4" /></a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 opacity-50">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="flex h-11 min-w-11 items-center justify-center rounded-full bg-sky px-3 text-sm font-bold text-white">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="flex h-11 min-w-11 items-center justify-center rounded-full border border-current/15 px-3 text-sm font-semibold transition hover:border-sky hover:text-sky">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="flex h-11 w-11 items-center justify-center rounded-full border border-current/15 transition hover:border-sky hover:bg-sky hover:text-white" aria-label="Berikutnya"><x-glyph name="chevron-right" class="h-4 w-4" /></a>
        @else
            <span class="flex h-11 w-11 items-center justify-center rounded-full border border-current/10 opacity-30"><x-glyph name="chevron-right" class="h-4 w-4" /></span>
        @endif
    </nav>
@endif
