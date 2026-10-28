<article class="card card-hover group h-full overflow-hidden text-center">
    <div class="relative aspect-[4/4.2] overflow-hidden bg-gradient-to-b from-brand-50 to-white">
        @if ($photo = media_url($person->photo))
            <img src="{{ $photo }}" alt="{{ $person->name }}" class="h-full w-full object-cover object-top transition duration-700 group-hover:scale-105">
        @else
            <div class="flex h-full items-end justify-center text-brand/30">
                <x-glyph name="user-round" class="h-[72%] w-[72%]" stroke-width="1" />
            </div>
        @endif
    </div>
    <div class="px-3 py-5 sm:px-5 sm:py-6">
        <h3 class="text-[15px] leading-snug font-bold sm:text-lg">{{ $person->name }}</h3>
        <p class="mt-1.5 text-[11px] leading-snug tracking-wide text-subtle uppercase sm:text-xs">{{ $person->position }}</p>
    </div>
</article>
