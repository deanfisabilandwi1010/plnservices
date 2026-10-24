<a href="{{ route('portfolio.show', $portfolio) }}" class="group relative block overflow-hidden rounded-[10px] shadow-[0_2px_20px_rgb(0_0_0/0.07)]" data-category="{{ $portfolio->category }}">
    <x-cover :src="$portfolio->image" :alt="$portfolio->title" :seed="$portfolio->id" icon="briefcase" class="aspect-[4/3]" />
    {{-- Caption slides up on hover, like the classic portfolio grid --}}
    <div class="absolute inset-x-0 bottom-0 translate-y-[calc(100%-4.25rem)] bg-white/95 p-5 backdrop-blur transition-transform duration-500 ease-[cubic-bezier(.16,1,.3,1)] group-hover:translate-y-0">
        <p class="font-display text-xs font-bold tracking-wide text-brand uppercase">{{ $portfolio->category }}</p>
        <h3 class="mt-1 truncate font-bold">{{ $portfolio->title }}</h3>
        <p class="mt-3 line-clamp-2 text-sm leading-relaxed">{{ $portfolio->summary }}</p>
        <p class="mt-3 flex items-center gap-1.5 text-xs text-subtle"><x-glyph name="map-pin" class="h-3.5 w-3.5" />{{ $portfolio->location }} · {{ $portfolio->year }}</p>
    </div>
</a>
