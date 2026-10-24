<x-layouts.site :title="$post->title" :description="$post->excerptText(160)">
    <div class="fixed inset-x-0 top-0 z-[61] h-[3px] origin-left bg-brand" style="transform: scaleX(0)" data-reading-progress></div>

    <x-page-hero :eyebrow="$post->category" :title="$post->title" :crumbs="['Berita' => route('news'), \Illuminate\Support\Str::limit($post->title, 40) => null]">
        <div class="mt-6 flex flex-wrap items-center gap-5 text-sm" data-reveal data-delay="0.2">
            <span class="flex items-center gap-2"><x-glyph name="calendar" class="h-4 w-4 text-brand" />{{ $post->published_at?->translatedFormat('l, d F Y') }}</span>
            <span class="flex items-center gap-2"><x-glyph name="clock" class="h-4 w-4 text-brand" />{{ $post->readingMinutes() }} menit baca</span>
        </div>
    </x-page-hero>

    <article class="bg-white py-16" data-article>
        <div class="container-x">
            <x-cover :src="$post->image" :alt="$post->title" :seed="$post->id" icon="newspaper" class="mx-auto aspect-[21/9] max-w-5xl rounded-lg" />
            <div class="mx-auto mt-12 grid max-w-5xl gap-10 lg:grid-cols-[1fr_180px]">
                <div class="prose-pln" data-reveal>
                    @if ($post->excerpt)
                        <p class="text-xl leading-relaxed font-medium text-ink">{{ $post->excerpt }}</p>
                    @endif
                    {!! $post->content !!}
                </div>
                <aside>
                    <div class="lg:sticky lg:top-40">
                        <p class="mb-3 text-xs font-bold tracking-[0.14em] text-subtle uppercase">Bagikan</p>
                        @php $share = urlencode(request()->url()); $text = urlencode($post->title); @endphp
                        <div class="flex gap-2 lg:flex-col">
                            <a href="https://wa.me/?text={{ $text }}%20{{ $share }}" target="_blank" rel="noopener" class="btn btn-outline !justify-start !px-3 !py-2" aria-label="Bagikan ke WhatsApp"><x-glyph name="message-circle" class="h-4 w-4" /><span class="hidden lg:inline">WhatsApp</span></a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $share }}" target="_blank" rel="noopener" class="btn btn-outline !justify-start !px-3 !py-2" aria-label="Bagikan ke LinkedIn"><x-glyph name="linkedin" class="h-4 w-4" /><span class="hidden lg:inline">LinkedIn</span></a>
                            <button type="button" onclick="navigator.clipboard.writeText(location.href); this.querySelector('span').textContent = 'Tersalin'" class="btn btn-outline !justify-start !px-3 !py-2" aria-label="Salin tautan"><x-glyph name="link" class="h-4 w-4" /><span class="hidden lg:inline">Salin tautan</span></button>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="border-t border-line bg-mist py-20">
            <div class="container-x">
                <h2 class="mb-10 text-2xl font-bold" data-split>Berita terkait</h2>
                <div class="grid gap-6 md:grid-cols-3" data-reveal="stagger">
                    @foreach ($related as $item)
                        @include('site.partials.post-card', ['post' => $item])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.site>
