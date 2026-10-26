<x-layouts.site title="Layanan">
    <x-page-hero eyebrow="Layanan" title="Layanan PLN Services" subtitle="Dari pembangkit, jaringan, hingga pelanggan — layanan terintegrasi PLN Services dirancang untuk keandalan, keselamatan, dan efisiensi." :crumbs="['Layanan' => null]" />

    <div class="relative" data-guide>
        <div class="guide-line hidden lg:block" aria-hidden="true"><span data-guide-fill></span><span data-guide-head></span></div>

        {{-- Services: list on the right, hover preview on the left --}}
        <section class="py-20 md:py-28">
            <div class="container-x" data-preview>
                <x-split-heading lead="Dari hulu<br>ke hilir" title="Layanan kami" />

                <div class="mt-12 grid items-start gap-12 lg:grid-cols-2 lg:gap-x-24">
                    <div class="lg:sticky lg:top-32">
                        <p class="max-w-md text-[1.02rem] leading-[1.85]" data-reveal>{{ setting('overview_text') }}</p>

                        <div class="preview-stack mt-10 hidden max-w-md lg:grid" aria-hidden="true">
                            @foreach ($services as $service)
                                <div class="{{ $loop->first ? 'is-active' : '' }} relative rounded-[10px] bg-mist p-8" data-preview-pane="{{ $loop->index }}">
                                    <span class="icon-box"><x-glyph :name="$service->icon" class="h-6 w-6" /></span>
                                    <p class="mt-6 font-display text-xl font-bold text-ink">{{ $service->title }}</p>
                                    <p class="mt-2 text-sm leading-relaxed">{{ $service->summary }}</p>
                                    <a href="{{ route('services.show', $service) }}" class="link-arrow mt-5" tabindex="-1">Lihat detail <x-glyph name="arrow-right" class="h-4 w-4" /></a>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <nav class="link-list" data-reveal="stagger" aria-label="Daftar layanan">
                        @foreach ($services as $service)
                            <a href="{{ route('services.show', $service) }}" data-preview-key="{{ $loop->index }}">
                                <span class="flex items-baseline gap-4">
                                    <span class="font-sans text-xs font-bold text-subtle tabular-nums">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span>
                                        {{ $service->title }}
                                        <span class="mt-1 block font-sans text-sm font-normal text-body lg:hidden">{{ $service->summary }}</span>
                                    </span>
                                </span>
                                <x-glyph name="arrow-right" class="ll-arrow h-5 w-5" />
                            </a>
                        @endforeach
                    </nav>
                </div>
            </div>
        </section>

        {{-- Method --}}
        <section class="bg-mist py-20 md:py-28">
            <div class="container-x">
                <x-split-heading lead="…dengan proses<br>yang terukur" title="Metode kerja" />

                <div class="mt-12 grid items-start gap-12 lg:grid-cols-2 lg:gap-x-24">
                    <x-line-art variant="connect" class="w-full max-w-[440px]" />

                    <div class="border-t border-line" data-accordion data-reveal>
                        @foreach ([['search', 'Asesmen', 'Memahami kebutuhan, kondisi lapangan, dan target keandalan.'], ['pencil-ruler', 'Perencanaan', 'Merancang solusi teknis, jadwal, dan manajemen risiko K3.'], ['hard-hat', 'Pelaksanaan', 'Tenaga bersertifikat bekerja dengan standar mutu PLN Group.'], ['activity', 'Pemantauan', 'Evaluasi kinerja berkelanjutan berbasis data.']] as $i => [$icon, $title, $text])
                            <div class="acc-item {{ $i === 0 ? 'is-open' : '' }}" data-acc-item>
                                <button type="button" class="flex w-full items-center gap-5 py-5 text-left" data-acc-toggle aria-expanded="{{ $i === 0 ? 'true' : 'false' }}">
                                    <span class="acc-num font-display text-sm font-bold text-subtle tabular-nums transition-colors">0{{ $i + 1 }}</span>
                                    <span class="acc-title flex-1 font-display text-2xl font-bold text-ink transition-colors">{{ $title }}</span>
                                    <span class="acc-plus text-brand"></span>
                                </button>
                                <div class="acc-body">
                                    <div>
                                        <div class="flex items-start gap-4 pb-6 pl-10">
                                            <span class="icon-box !h-11 !w-11"><x-glyph :name="$icon" class="h-5 w-5" /></span>
                                            <p class="pt-2.5 leading-relaxed">{{ $text }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    </div>

    <x-cta-band title="Ada kebutuhan layanan?">
        <a href="{{ route('portfolio') }}" class="btn btn-outline-light">Lihat Portofolio</a>
    </x-cta-band>
</x-layouts.site>
