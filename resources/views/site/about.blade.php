<x-layouts.site title="Tentang Kami">
    <x-page-hero eyebrow="Profil" :title="setting('about_title', 'Tentang PLN Services')" :crumbs="['Profil' => null, 'Tentang Kami' => null]" />

    {{-- Company profile --}}
    <section class="bg-white py-20 md:py-24">
        <div class="container-x grid items-center gap-12 lg:grid-cols-2">
            <div class="text-justify text-[15px] leading-[1.85]" data-reveal="left">
                <img src="{{ asset('images/logo.png') }}" alt="PLN Services" class="mb-8 h-14 w-auto">
                <p>{{ setting('about_intro') }}</p>
                <p class="mt-4">{{ setting('about_body') }}</p>
                <p class="mt-4">{{ setting('about_commitment') }}</p>
            </div>
            <div class="relative" data-reveal="right">
                @if ($image = media_url(setting('about_image')))
                    <img src="{{ $image }}" alt="PLN Services" class="aspect-[4/3] w-full rounded-[10px] object-cover shadow-[0_2px_20px_rgb(0_0_0/0.1)]">
                @else
                    <x-iso-scene class="mx-auto h-auto w-full max-w-[560px]" />
                @endif
            </div>
        </div>

        @if ($statistics->isNotEmpty())
            <div class="container-x mt-16">
                <div class="grid grid-cols-2 gap-6 rounded-[10px] bg-brand px-6 py-10 text-white md:grid-cols-4 md:px-10" data-reveal>
                    @foreach ($statistics as $stat)
                        @php $decimals = fmod((float) $stat->value, 1) ? 1 : 0; @endphp
                        <div class="text-center">
                            <p class="font-display text-3xl font-bold md:text-4xl">{{ $stat->prefix }}<span data-counter="{{ (float) $stat->value }}" data-decimals="{{ $decimals }}">{{ number_format((float) $stat->value, $decimals, ',', '.') }}</span>{{ $stat->suffix }}</p>
                            <p class="mt-1 text-sm text-white/80">{{ $stat->label }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    {{-- Vision & mission --}}
    <section id="visi-misi" class="scroll-mt-20 bg-mist py-20 md:py-24">
        <div class="container-x">
            <x-section-heading title="Visi & Misi" />
            <div class="mt-12 grid gap-6 lg:grid-cols-5">
                <div class="card p-8 lg:col-span-2" data-reveal="left">
                    <span class="icon-box"><x-glyph name="eye" class="h-6 w-6" /></span>
                    <h3 class="mt-5 text-xl font-bold">Visi</h3>
                    <p class="mt-3 leading-relaxed">{{ setting('vision') }}</p>
                </div>
                <div class="card p-8 lg:col-span-3" data-reveal="right">
                    <span class="icon-box"><x-glyph name="target" class="h-6 w-6" /></span>
                    <h3 class="mt-5 text-xl font-bold">Misi</h3>
                    <ol class="mt-4 space-y-3">
                        @foreach (lines(setting('mission')) as $i => $mission)
                            <li class="flex gap-3"><span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand text-xs font-bold text-white">{{ $i + 1 }}</span><span class="leading-relaxed">{{ $mission }}</span></li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </section>

    {{-- Values --}}
    <section id="tata-nilai" class="scroll-mt-20 bg-white py-20 md:py-24">
        <div class="container-x">
            <x-section-heading title="Tata Nilai" subtitle="Budaya kerja AKHLAK yang menjadi pedoman setiap insan PLN Services." />
            <div class="mt-12 grid grid-cols-2 gap-5 md:grid-cols-3 lg:grid-cols-6" data-reveal="stagger">
                @foreach (lines(setting('values')) as $value)
                    <div class="card service-card group px-4 py-8 text-center">
                        <span class="icon-box mx-auto font-display text-xl font-bold">{{ mb_substr($value, 0, 1) }}</span>
                        <h3 class="mt-4 font-bold transition-colors">{{ $value }}</h3>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Milestones --}}
    @if ($milestones->isNotEmpty())
        <section class="bg-mist py-20 md:py-24">
            <div class="container-x">
                <x-section-heading title="Perjalanan Kami" />
                <ol class="relative mx-auto mt-14 max-w-3xl border-l-2 border-brand/25">
                    @foreach ($milestones as $milestone)
                        <li class="relative pb-10 pl-10 last:pb-0" data-reveal>
                            <span class="absolute top-1 -left-[11px] h-5 w-5 rounded-full border-4 border-mist bg-brand"></span>
                            <p class="font-display text-sm font-bold text-brand">{{ $milestone->year }}</p>
                            <h3 class="mt-1 text-lg font-bold">{{ $milestone->title }}</h3>
                            <p class="mt-2 leading-relaxed">{{ $milestone->description }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif
</x-layouts.site>
