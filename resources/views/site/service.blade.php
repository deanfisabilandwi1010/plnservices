<x-layouts.site :title="$service->title" :description="$service->summary">
    <x-page-hero eyebrow="Layanan" :title="$service->title" :subtitle="$service->summary" :crumbs="['Layanan' => route('services'), $service->title => null]" />

    <section class="bg-white py-20">
        <div class="container-x grid gap-12 lg:grid-cols-12">
            <div class="lg:col-span-8">
                @if (media_url($service->image))
                    <x-cover :src="$service->image" :alt="$service->title" class="mb-10 aspect-video rounded-lg" />
                @endif
                <div class="prose-pln" data-reveal>{!! $service->description !!}</div>
            </div>
            <aside class="lg:col-span-4">
                <div class="space-y-5 lg:sticky lg:top-40">
                    <div class="rounded-lg bg-brand p-7 text-white" data-reveal>
                        <h3 class="text-xl font-semibold !text-white">Butuh layanan ini?</h3>
                        <p class="mt-2 text-sm leading-relaxed text-white/75">Tim kami akan membantu merumuskan lingkup pekerjaan sesuai kebutuhan Anda.</p>
                        <a href="{{ route('contact', ['topik' => 'Kemitraan & Bisnis']) }}" class="btn btn-accent mt-5 w-full">Konsultasikan</a>
                    </div>
                    <div class="card p-5" data-reveal>
                        <p class="mb-2 px-2 text-xs font-bold tracking-[0.14em] text-subtle uppercase">Layanan lainnya</p>
                        @foreach ($others as $other)
                            <a href="{{ route('services.show', $other) }}" class="group flex items-center gap-3 rounded-md p-2 transition hover:bg-mist">
                                <span class="icon-box !h-9 !w-9"><x-glyph :name="$other->icon" class="h-4 w-4" /></span>
                                <span class="flex-1 text-sm font-semibold text-ink group-hover:text-brand">{{ $other->title }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </aside>
        </div>
    </section>
</x-layouts.site>
