<x-layouts.site title="Tentang Kami">
    @php
        $segments = lines(setting('about_segments', "PLN Group\nPembangkit Listrik\nKawasan Industri\nGedung & Kawasan Komersial\nPemerintah Daerah\nBUMN & Swasta\nPelanggan Premium"));
        $highlights = lines(setting('about_highlights', "Menjaga *keandalan pasokan listrik* di seluruh Indonesia\n*Mempercepat transisi energi* melalui EBT dan layanan digital\nMembangun *talenta unggul* berbudaya AKHLAK"));
        $akhlak = [
            'Amanah' => 'Memegang teguh kepercayaan yang diberikan.',
            'Kompeten' => 'Terus belajar dan mengembangkan kapabilitas.',
            'Harmonis' => 'Saling peduli dan menghargai perbedaan.',
            'Loyal' => 'Berdedikasi dan mengutamakan kepentingan bangsa dan negara.',
            'Adaptif' => 'Terus berinovasi dan antusias dalam menggerakkan ataupun menghadapi perubahan.',
            'Kolaboratif' => 'Membangun kerja sama yang sinergis.',
        ];
        $sections = ['profil' => 'Profil', 'energi' => 'Perjalanan Energi', 'pelanggan' => 'Pelanggan', 'visi-misi' => 'Visi & Misi', 'tata-nilai' => 'Tata Nilai']
            + ($milestones->isNotEmpty() ? ['perjalanan' => 'Perjalanan'] : []);
    @endphp

    <x-page-hero eyebrow="Profil" :title="setting('about_title', 'Tentang PLN Services')" :crumbs="['Profil' => null, 'Tentang Kami' => null]" />

    <x-section-nav title="Tentang Kami" :items="$sections" cta="Hubungi Kami" :cta-url="route('contact')" />

    <div class="relative" data-guide>
        <div class="guide-line hidden lg:block" aria-hidden="true"><span data-guide-fill></span><span data-guide-head></span></div>

        {{-- Profile --}}
        <section id="profil" class="scroll-mt-[136px] py-20 md:py-28">
            <div class="container-x">
                <x-split-heading lead="Energi andal<br>untuk negeri" title="Penyedia jasa ketenagalistrikan terintegrasi" />

                <div class="mt-12 grid items-center gap-12 lg:grid-cols-2 lg:gap-x-24">
                    <div class="space-y-5 text-[1.02rem] leading-[1.85]" data-reveal>
                        <p class="text-lg leading-relaxed font-semibold text-ink">{{ setting('about_intro') }}</p>
                        <p>{{ setting('about_body') }}</p>
                    </div>
                    <div>
                        @if ($image = media_url(setting('about_image')))
                            <img src="{{ $image }}" alt="PLN Services" class="relative aspect-[4/3] w-full rounded-[10px] object-cover" data-clip>
                        @else
                            <x-line-art variant="together" class="mx-auto w-full max-w-[460px]" />
                        @endif
                    </div>
                </div>

                @if ($statistics->isNotEmpty())
                    <div class="relative mt-20 grid grid-cols-2 gap-6 rounded-[10px] bg-brand px-6 py-10 text-white md:grid-cols-4 md:px-10" data-reveal>
                        @foreach ($statistics as $stat)
                            @php $decimals = fmod((float) $stat->value, 1) ? 1 : 0; @endphp
                            <div class="text-center">
                                <p class="font-display text-3xl font-bold md:text-4xl">{{ $stat->prefix }}<span data-counter="{{ (float) $stat->value }}" data-decimals="{{ $decimals }}">{{ number_format((float) $stat->value, $decimals, ',', '.') }}</span>{{ $stat->suffix }}</p>
                                <p class="mt-1 text-sm text-white/80">{{ $stat->label }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        @include('site.partials.energy-story')

        {{-- Customers --}}
        <section id="pelanggan" class="scroll-mt-[136px] bg-mist py-20 md:py-28">
            <div class="container-x">
                <x-split-heading lead="Bersama" title="pelanggan kami" />

                <div class="mt-12 grid gap-12 lg:grid-cols-2 lg:gap-x-24">
                    <div class="space-y-4 text-[1.02rem] leading-[1.85]" data-reveal>
                        <p>{{ setting('about_segments_intro', 'Setiap pelanggan adalah mitra dalam menjaga nyala listrik Indonesia. Dari unit PLN Group hingga kawasan industri dan pemerintah daerah, kami hadir dengan layanan yang andal dan responsif.') }}</p>
                        <p>Punya kebutuhan serupa? Pilih segmen Anda dan sampaikan kebutuhannya kepada tim kami.</p>
                    </div>
                    <nav class="link-list" data-reveal="stagger" aria-label="Segmen pelanggan">
                        @foreach ($segments as $segment)
                            <a href="{{ route('contact', ['topik' => 'Kemitraan & Bisnis']) }}">{{ $segment }} <x-glyph name="arrow-right" class="ll-arrow h-5 w-5" /></a>
                        @endforeach
                    </nav>
                </div>
            </div>
        </section>

        {{-- Vision, mission, commitment --}}
        <section id="visi-misi" class="scroll-mt-[136px] py-20 md:py-28">
            <div class="container-x">
                <x-split-heading lead="…dengan menghubungkan setiap titik" title="Visi, Misi & Komitmen" />

                <div class="mt-12 grid items-start gap-12 lg:grid-cols-2 lg:gap-x-24">
                    <div>
                        <x-line-art variant="connect" class="w-full max-w-[440px]" />
                        <p class="mt-6 max-w-md leading-[1.85]" data-reveal>Kami menyatukan keahlian pembangkitan, jaringan, dan pelayanan pelanggan menjadi satu layanan yang utuh.</p>
                    </div>

                    <div class="border-t border-line" data-accordion data-reveal>
                        @foreach ([['Visi', setting('vision'), null], ['Misi', null, lines(setting('mission'))], ['Komitmen', setting('about_commitment'), null]] as $i => [$label, $text, $list])
                            <div class="acc-item {{ $i === 0 ? 'is-open' : '' }}" data-acc-item>
                                <button type="button" class="flex w-full items-center gap-5 py-5 text-left" data-acc-toggle aria-expanded="{{ $i === 0 ? 'true' : 'false' }}">
                                    <span class="acc-num font-display text-sm font-bold text-subtle tabular-nums transition-colors">0{{ $i + 1 }}</span>
                                    <span class="acc-title flex-1 font-display text-2xl font-bold text-ink transition-colors">{{ $label }}</span>
                                    <span class="acc-plus text-brand"></span>
                                </button>
                                <div class="acc-body">
                                    <div>
                                        @if ($list)
                                            <ol class="space-y-3 pb-6 pl-10">
                                                @foreach ($list as $n => $item)
                                                    <li class="flex gap-3 leading-relaxed"><span class="font-display font-bold text-brand">{{ $n + 1 }}.</span>{{ $item }}</li>
                                                @endforeach
                                            </ol>
                                        @else
                                            <p class="pb-6 pl-10 text-[1.02rem] leading-[1.85]">{{ $text }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- Highlights --}}
        <section class="bg-mist py-20 md:py-28">
            <div class="container-x">
                <x-split-heading lead="…untuk energi yang lebih baik">
                    <x-line-art variant="pulse" class="w-full max-w-[460px]" />
                </x-split-heading>

                <div class="mt-12 grid gap-5 md:grid-cols-3" data-reveal="stagger">
                    @foreach ($highlights as $line)
                        <div class="relative rounded-[10px] border-t-2 border-brand bg-white p-7 font-display text-xl leading-snug font-semibold text-ink">
                            {!! preg_replace('/\*(.+?)\*/', '<span class="mark">$1</span>', e($line)) !!}
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Values --}}
        <section id="tata-nilai" class="scroll-mt-[136px] py-20 md:py-28">
            <div class="container-x">
                <x-split-heading lead="Nilai yang<br>kami pegang" title="Tata nilai AKHLAK" />

                <div class="mt-12 grid gap-12 lg:grid-cols-2 lg:gap-x-24">
                    <p class="max-w-md text-[1.02rem] leading-[1.85]" data-reveal>Budaya kerja AKHLAK menjadi pedoman setiap insan PLN Services, dari ruang kendali hingga lapangan.</p>
                    <div class="link-list" data-reveal="stagger">
                        @foreach (lines(setting('values')) as $value)
                            <div class="group !items-start !justify-start !gap-5">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-50 font-display text-lg font-bold text-brand transition-colors duration-300 group-hover:bg-brand group-hover:text-white">{{ mb_substr($value, 0, 1) }}</span>
                                <span>
                                    <span class="block">{{ $value }}</span>
                                    @isset($akhlak[$value])
                                        <span class="mt-1 block font-sans text-sm font-normal text-body">{{ $akhlak[$value] }}</span>
                                    @endisset
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- Milestones, each one a node on the guide line --}}
        @if ($milestones->isNotEmpty())
            <section id="perjalanan" class="scroll-mt-[136px] bg-mist py-20 md:py-28">
                <div class="container-x">
                    <x-split-heading lead="Perjalanan<br>kami" title="Tonggak penting PLN Services" />

                    <ol class="mt-14 space-y-12">
                        @foreach ($milestones as $milestone)
                            <li class="relative grid gap-2 lg:grid-cols-2 lg:gap-x-24" data-reveal>
                                <span class="guide-node top-3 hidden lg:block" data-guide-node></span>
                                <p class="font-display text-4xl font-bold text-brand lg:text-right">{{ $milestone->year }}</p>
                                <div>
                                    <h3 class="text-xl font-bold">{{ $milestone->title }}</h3>
                                    <p class="mt-2 leading-relaxed">{{ $milestone->description }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>
        @endif
    </div>

    <x-cta-band>
        <a href="{{ route('services') }}" class="btn btn-outline-light">Lihat Layanan</a>
    </x-cta-band>
</x-layouts.site>
