<x-layouts.site title="Portofolio">
    <x-page-hero eyebrow="Portofolio" title="Proyek di seluruh Indonesia" subtitle="Pekerjaan yang mencerminkan keandalan, keselamatan, dan inovasi layanan PLN Services." :crumbs="['Portofolio' => null]" />

    <section class="bg-white py-20">
        <div class="container-x">
            <div class="mb-10 flex flex-wrap gap-2" data-filter-group="#portfolio-grid" data-reveal="fade">
                <button type="button" data-filter="*" class="chip is-active">Semua</button>
                @foreach ($categories as $category)
                    <button type="button" data-filter="{{ $category }}" class="chip">{{ $category }}</button>
                @endforeach
            </div>

            <div id="portfolio-grid" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($portfolios as $portfolio)
                    @include('site.partials.portfolio-card', ['portfolio' => $portfolio])
                @empty
                    <p class="col-span-full text-center text-subtle">Belum ada portofolio.</p>
                @endforelse
            </div>
        </div>
    </section>
</x-layouts.site>
