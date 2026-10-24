<a href="{{ route('news.show', $post) }}" class="card card-hover group flex h-full flex-col overflow-hidden">
    <x-cover :src="$post->image" :alt="$post->title" :seed="$post->id" icon="newspaper" class="aspect-[16/11]">
        <span class="absolute bottom-3 left-3 rounded-full bg-brand px-3 py-1 font-display text-xs font-semibold text-white">{{ $post->category }}</span>
    </x-cover>
    <div class="flex flex-1 flex-col p-6">
        <time class="text-xs text-subtle" datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->translatedFormat('d F Y') }}</time>
        <h3 class="mt-2 text-[1.05rem] leading-snug font-bold transition group-hover:text-brand">{{ $post->title }}</h3>
        <p class="mt-2 line-clamp-3 text-sm leading-relaxed">{{ $post->excerptText() }}</p>
        <span class="link-arrow mt-auto pt-5"><x-glyph name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" /> Read More</span>
    </div>
</a>
