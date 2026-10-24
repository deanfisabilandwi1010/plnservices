<x-layouts.site title="Berita">
    <x-page-hero eyebrow="Berita & Siaran Pers" title="Kabar terbaru PLN Services" :crumbs="['Berita' => null]">
        <form action="{{ route('news') }}" method="get" class="mt-8 flex max-w-xl items-center gap-2 rounded-md border border-line bg-white p-1.5 pl-4" data-reveal data-delay="0.2" role="search">
            <x-glyph name="search" class="h-5 w-5 text-subtle" />
            <label for="news-q" class="sr-only">Cari berita</label>
            <input id="news-q" type="search" name="q" value="{{ request('q') }}" placeholder="Cari berita…" class="flex-1 bg-transparent py-2 text-ink outline-none placeholder:text-subtle">
            @if ($activeCategory)<input type="hidden" name="kategori" value="{{ $activeCategory }}">@endif
            <button class="btn btn-primary !py-2.5">Cari</button>
        </form>
    </x-page-hero>

    <section class="bg-white py-16">
        <div class="container-x">
            <div class="mb-10 flex flex-wrap gap-2" data-reveal="fade">
                <a href="{{ route('news', array_filter(['q' => request('q')])) }}" class="chip" @if (! $activeCategory) aria-current="page" @endif>Semua</a>
                @foreach ($categories as $category)
                    <a href="{{ route('news', array_filter(['kategori' => $category, 'q' => request('q')])) }}" class="chip" @if ($activeCategory === $category) aria-current="page" @endif>{{ $category }}</a>
                @endforeach
            </div>

            @if ($posts->isEmpty())
                <div class="rounded-lg border border-dashed border-line py-20 text-center text-subtle">Belum ada berita yang sesuai.</div>
            @else
                @php $featured = $posts->onFirstPage() && ! request('q') ? $posts->first() : null; @endphp
                @if ($featured)
                    <a href="{{ route('news.show', $featured) }}" class="card card-hover group mb-8 grid overflow-hidden lg:grid-cols-2" data-reveal>
                        <x-cover :src="$featured->image" :alt="$featured->title" :seed="$featured->id" icon="newspaper" class="aspect-[16/10] lg:aspect-auto lg:min-h-[360px]" />
                        <div class="flex flex-col justify-center p-8 md:p-10">
                            <p class="text-xs font-semibold text-subtle"><span class="text-brand">{{ $featured->category }}</span> · {{ $featured->published_at?->translatedFormat('d F Y') }}</p>
                            <h2 class="mt-3 text-2xl leading-snug font-bold group-hover:text-brand md:text-3xl">{{ $featured->title }}</h2>
                            <p class="mt-3 leading-relaxed">{{ $featured->excerptText(220) }}</p>
                            <span class="link-arrow mt-6">Baca artikel <x-glyph name="arrow-right" class="h-4 w-4" /></span>
                        </div>
                    </a>
                @endif

                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3" data-reveal="stagger">
                    @foreach ($posts as $post)
                        @continue($featured && $post->is($featured))
                        @include('site.partials.post-card', ['post' => $post])
                    @endforeach
                </div>

                <div class="mt-14">{{ $posts->links() }}</div>
            @endif
        </div>
    </section>
</x-layouts.site>
