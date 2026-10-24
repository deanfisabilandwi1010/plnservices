<x-layouts.site :title="$portfolio->title" :description="$portfolio->summary">
    <x-page-hero :eyebrow="$portfolio->category" :title="$portfolio->title" :subtitle="$portfolio->summary" :crumbs="['Portofolio' => route('portfolio'), $portfolio->title => null]">
        <dl class="mt-10 grid max-w-2xl grid-cols-3 divide-x divide-line rounded-lg border border-line bg-white" data-reveal data-delay="0.3">
            @foreach (['Klien' => $portfolio->client, 'Lokasi' => $portfolio->location, 'Tahun' => $portfolio->year] as $label => $value)
                <div class="px-5 py-4">
                    <dt class="text-xs text-subtle">{{ $label }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-ink">{{ $value ?: '—' }}</dd>
                </div>
            @endforeach
        </dl>
    </x-page-hero>

    <section class="bg-white py-20">
        <div class="container-x">
            <x-cover :src="$portfolio->image" :alt="$portfolio->title" :seed="$portfolio->id" icon="briefcase" class="aspect-[21/9] rounded-lg" data-reveal />
            <div class="prose-pln mx-auto mt-14 max-w-3xl" data-reveal>{!! $portfolio->description !!}</div>
        </div>
    </section>

    @if ($others->isNotEmpty())
        <section class="border-t border-line bg-mist py-20">
            <div class="container-x">
                <h2 class="mb-10 text-2xl font-bold" data-split>Proyek lainnya</h2>
                <div class="grid gap-6 md:grid-cols-3" data-reveal="stagger">
                    @foreach ($others as $other)
                        @include('site.partials.portfolio-card', ['portfolio' => $other])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.site>
