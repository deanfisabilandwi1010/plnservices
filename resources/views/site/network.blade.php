<x-layouts.site title="Wilayah Kerja">
    <x-page-hero eyebrow="Wilayah Kerja" title="Satu jaringan layanan di seluruh Indonesia" subtitle="Pilih kantor pada daftar untuk melihat lokasinya di peta." :crumbs="['Wilayah Kerja' => null]" />

    <section class="bg-white py-16">
        <div class="container-x grid gap-6 lg:grid-cols-12">
            <div class="order-2 lg:order-1 lg:col-span-4">
                <div class="divide-y divide-line rounded-lg border border-line" data-reveal="stagger">
                    @foreach ($offices->filter(fn ($o) => $o->latitude && $o->longitude)->values() as $i => $office)
                        <button type="button" data-map-target="network-map" data-index="{{ $i }}" class="group flex w-full items-start gap-4 p-4 text-left transition hover:bg-mist">
                            <span class="icon-box !h-10 !w-10 {{ $office->type === 'Kantor Pusat' ? '!bg-brand !text-white' : '' }}">
                                <x-glyph :name="$office->type === 'Kantor Pusat' ? 'building-2' : 'map-pin'" class="h-4 w-4" />
                            </span>
                            <span class="flex-1">
                                <span class="block text-xs text-subtle">{{ $office->type }}</span>
                                <span class="block font-semibold text-ink group-hover:text-brand">{{ $office->name }}</span>
                                <span class="mt-0.5 block text-sm">{{ $office->address }}</span>
                                @if ($office->phone)<span class="block text-sm">{{ $office->phone }}</span>@endif
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="order-1 lg:order-2 lg:col-span-8" data-reveal>
                <div class="overflow-hidden rounded-lg border border-line lg:sticky lg:top-40">
                    <div id="network-map" class="h-[420px] lg:h-[calc(100svh-12rem)]" data-office-map
                         data-offices="{{ $offices->map->toMapArray()->values()->toJson() }}"></div>
                </div>
            </div>
        </div>
    </section>

    <section class="border-t border-line bg-mist py-20">
        <div class="container-x">
            <x-section-heading title="Wilayah Layanan" />
            <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3" data-reveal="stagger">
                @foreach ($workAreas as $area)
                    <div class="card p-7">
                        <div class="flex items-baseline justify-between gap-4">
                            <h3 class="text-xl font-semibold">{{ $area->name }}</h3>
                            <p class="text-right"><span class="text-2xl font-bold text-brand" data-counter="{{ $area->units }}">{{ $area->units }}</span> <span class="text-xs text-subtle">unit</span></p>
                        </div>
                        <p class="mt-3 text-sm leading-relaxed">{{ $area->provinces }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.site>
