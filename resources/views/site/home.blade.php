<x-layouts.site>
    {{-- ===================== HERO ===================== --}}
    <section class="relative overflow-hidden bg-white pt-[80px]">
        <div class="pointer-events-none absolute top-0 right-0 h-full w-1/2 bg-gradient-to-l from-brand-50/70 to-transparent"></div>
        <div class="container-x relative grid min-h-[calc(100svh-80px)] items-center gap-10 py-14 lg:grid-cols-2">
            <div>
                <p class="kicker mb-5" data-reveal="fade">{{ setting('hero_eyebrow') }}</p>
                <h1 class="text-[2.6rem] leading-[1.12] font-bold md:text-[3.25rem]" data-split>{{ setting('hero_title') }}</h1>
                <p class="mt-5 max-w-xl font-display text-xl leading-relaxed text-[#555] md:text-[1.4rem]" data-reveal data-delay="0.2">{{ setting('hero_subtitle') }}</p>
                <div class="mt-9 flex flex-wrap gap-3" data-reveal data-delay="0.35">
                    <a href="#tentang" class="btn btn-outline">Get Started</a>
                    <a href="{{ route('services') }}" class="btn btn-primary">Layanan Kami</a>
                </div>
            </div>

            {{-- Animated isometric energy scene --}}
            <div class="mx-auto w-full max-w-[600px]" data-reveal="scale" data-delay="0.15">
                <x-iso-scene class="h-auto w-full" />
            </div>
        </div>
    </section>

    {{-- ===================== TENTANG KAMI ===================== --}}
    <section id="tentang" class="scroll-mt-20 bg-white py-20 md:py-24">
        <div class="container-x">
            <x-section-heading title="Tentang Kami" />
            <div class="mt-12 grid gap-8 text-justify text-[15px] leading-[1.85] md:grid-cols-2 md:gap-12">
                <div data-reveal="left">
                    <p>{{ setting('about_intro') }}</p>
                    <p class="mt-4">{{ setting('overview_text') }}</p>
                </div>
                <div data-reveal="right">
                    <p>{{ setting('about_body') }}</p>
                    <p class="mt-4">{{ setting('about_commitment') }}</p>
                    <a href="{{ route('about') }}" class="btn btn-outline mt-7">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== JARINGAN DALAM ANGKA (scrollytelling) ===================== --}}
    <div class="bg-white pt-4">
        <x-section-heading title="Jaringan dalam Angka" subtitle="Gulir perlahan untuk melihat bagaimana unit layanan kami tersebar." />
    </div>
    @include('site.partials.unit-story', ['workAreas' => $workAreas])

    @if ($statistics->isNotEmpty())
        <section class="bg-white pb-20 md:pb-24">
            <div class="container-x grid grid-cols-2 gap-y-10 border-y border-line py-10 lg:grid-cols-4" data-reveal="stagger">
                @foreach ($statistics as $stat)
                    @php $decimals = fmod((float) $stat->value, 1) ? 1 : 0; @endphp
                    <div class="flex items-start gap-4 px-2">
                        <x-glyph :name="$stat->icon" class="mt-1 h-8 w-8 shrink-0 text-brand" stroke-width="1.5" />
                        <div>
                            <p class="font-display text-3xl font-bold text-ink">{{ $stat->prefix }}<span data-counter="{{ (float) $stat->value }}" data-decimals="{{ $decimals }}">{{ number_format((float) $stat->value, $decimals, ',', '.') }}</span>{{ $stat->suffix }}</p>
                            <p class="mt-1 font-display text-sm font-semibold text-[#555]">{{ $stat->label }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ===================== LAYANAN (carousel) ===================== --}}
    <section class="bg-white py-20 md:py-24">
        <div class="container-x">
            <x-section-heading title="Layanan" subtitle="Layanan ketenagalistrikan terintegrasi dari pembangkit, jaringan, hingga pelanggan." />

            <div class="relative mt-12" data-carousel data-autoplay="5000" data-reveal>
                <div class="carousel-track -mx-3 flex snap-x snap-mandatory overflow-x-auto px-3 pt-2 pb-8" data-carousel-track>
                    @foreach ($services as $service)
                        <div class="w-full shrink-0 snap-start px-3 sm:w-1/2 lg:w-1/4">
                            <a href="{{ route('services.show', $service) }}" class="card service-card group flex h-full min-h-[340px] flex-col items-center px-6 py-10 text-center hover:-translate-y-1.5">
                                <span class="icon-box"><x-glyph :name="$service->icon" class="h-6 w-6" /></span>
                                <h3 class="mt-6 text-lg leading-snug font-bold transition-colors">{{ $service->title }}</h3>
                                <p class="mt-3 line-clamp-5 text-sm leading-relaxed transition-colors">{{ $service->summary }}</p>
                            </a>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="absolute top-[42%] -left-2 hidden h-11 w-11 items-center justify-center rounded-full bg-brand text-white shadow-lg transition hover:bg-brand-700 md:flex lg:-left-5" data-carousel-prev aria-label="Sebelumnya"><x-glyph name="chevron-left" class="h-5 w-5" /></button>
                <button type="button" class="absolute top-[42%] -right-2 hidden h-11 w-11 items-center justify-center rounded-full bg-brand text-white shadow-lg transition hover:bg-brand-700 md:flex lg:-right-5" data-carousel-next aria-label="Berikutnya"><x-glyph name="chevron-right" class="h-5 w-5" /></button>
                <div class="flex justify-center gap-2" data-carousel-dots></div>
            </div>
        </div>
    </section>

    {{-- ===================== TATA NILAI band ===================== --}}
    <div class="overflow-hidden bg-mist py-6" aria-label="Tata nilai AKHLAK">
        <div class="marquee-track flex w-max items-center gap-14 whitespace-nowrap">
            @foreach (range(1, 3) as $_)
                @foreach (lines(setting('values')) as $value)
                    <span class="flex items-center gap-3 font-display text-lg font-bold text-brand/80"><x-glyph name="zap" class="h-4 w-4 fill-volt text-volt" />{{ $value }}</span>
                @endforeach
            @endforeach
        </div>
    </div>

    {{-- ===================== BERITA (carousel) ===================== --}}
    @if ($posts->isNotEmpty())
        <section class="bg-white py-20 md:py-24">
            <div class="container-x">
                <x-section-heading title="Berita Terbaru" />
                <div class="relative mt-12" data-carousel data-reveal>
                    <div class="carousel-track -mx-3 flex snap-x snap-mandatory overflow-x-auto px-3 pt-2 pb-8" data-carousel-track>
                        @foreach ($posts as $post)
                            <div class="w-full shrink-0 snap-start px-3 sm:w-1/2 lg:w-1/3">
                                @include('site.partials.post-card', ['post' => $post])
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="absolute top-[38%] -left-2 hidden h-11 w-11 items-center justify-center rounded-full bg-brand text-white shadow-lg transition hover:bg-brand-700 md:flex lg:-left-5" data-carousel-prev aria-label="Sebelumnya"><x-glyph name="chevron-left" class="h-5 w-5" /></button>
                    <button type="button" class="absolute top-[38%] -right-2 hidden h-11 w-11 items-center justify-center rounded-full bg-brand text-white shadow-lg transition hover:bg-brand-700 md:flex lg:-right-5" data-carousel-next aria-label="Berikutnya"><x-glyph name="chevron-right" class="h-5 w-5" /></button>
                    <div class="flex justify-center gap-2" data-carousel-dots></div>
                </div>
                <div class="mt-4 text-center"><a href="{{ route('news') }}" class="btn btn-outline">Lihat Semua Berita</a></div>
            </div>
        </section>
    @endif

    {{-- ===================== PORTOFOLIO ===================== --}}
    @if ($portfolios->isNotEmpty())
        <section class="bg-white pb-20 md:pb-24">
            <div class="container-x">
                <x-section-heading title="Portofolio" />
                <div class="mt-8 flex flex-wrap justify-center gap-1" data-filter-group="#home-portfolio" data-reveal="fade">
                    <button type="button" data-filter="*" class="chip is-active">All</button>
                    @foreach ($portfolios->pluck('category')->filter()->unique() as $category)
                        <button type="button" data-filter="{{ $category }}" class="chip">{{ $category }}</button>
                    @endforeach
                </div>
                <div id="home-portfolio" class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($portfolios as $portfolio)
                        @include('site.partials.portfolio-card', ['portfolio' => $portfolio])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ===================== JEJAK PROYEK & KABAR (interactive timeline) ===================== --}}
    @include('site.partials.timeline', ['timeline' => $timeline])

    {{-- ===================== PROFIL DIREKSI ===================== --}}
    @if ($direksi->isNotEmpty())
        <section class="bg-mist py-20 md:py-24">
            <div class="container-x">
                <x-section-heading title="Profil Direksi" />
                @php $cols = [1 => 'lg:grid-cols-1', 2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3'][$direksi->count()] ?? 'lg:grid-cols-4'; @endphp
                <div class="mt-12 grid gap-6 sm:grid-cols-2 {{ $cols }}" data-reveal="stagger">
                    @foreach ($direksi as $person)
                        @include('site.partials.person-card', ['person' => $person])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ===================== HUBUNGI KAMI ===================== --}}
    <section class="bg-white py-20 md:py-24">
        <div class="container-x">
            <x-section-heading title="Hubungi Kami" />
            <div class="mt-12 grid gap-10 lg:grid-cols-12">
                <div class="lg:col-span-4" data-reveal="left">
                    <h3 class="text-2xl leading-snug font-bold">{{ setting('company_name', 'PT PLN Services') }}</h3>
                    <p class="mt-3 text-sm leading-relaxed">{{ setting('hero_subtitle') }}</p>
                    <div class="mt-6 space-y-4 text-sm">
                        <p class="flex gap-3"><x-glyph name="map-pin" class="h-5 w-5 shrink-0 text-brand" />{{ setting('address') }}</p>
                        @if (setting('email'))<p class="flex gap-3"><x-glyph name="mail" class="h-5 w-5 shrink-0 text-brand" /><a href="mailto:{{ setting('email') }}" class="hover:text-brand">{{ setting('email') }}</a></p>@endif
                        @if (setting('phone'))<p class="flex gap-3"><x-glyph name="phone" class="h-5 w-5 shrink-0 text-brand" />Telp {{ setting('phone') }}</p>@endif
                        <p class="flex gap-3"><x-glyph name="clock" class="h-5 w-5 shrink-0 text-brand" />Senin–Jumat, 08.00–16.00 WIB</p>
                    </div>
                </div>

                <form action="{{ route('contact.send') }}" method="post" class="grid gap-4 sm:grid-cols-2 lg:col-span-8" data-reveal="right">
                    @csrf
                    <label class="sr-only" for="h-name">Nama</label>
                    <input id="h-name" name="name" value="{{ old('name') }}" placeholder="Nama lengkap" class="field" required autocomplete="name">
                    <label class="sr-only" for="h-email">Email</label>
                    <input id="h-email" type="email" name="email" value="{{ old('email') }}" placeholder="Email" class="field" required autocomplete="email">
                    <label class="sr-only" for="h-category">Kategori pesan</label>
                    <select id="h-category" name="category" class="field" required>
                        @foreach ($categories as $category => $unit)
                            <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                    <label class="sr-only" for="h-subject">Subjek</label>
                    <input id="h-subject" name="subject" value="{{ old('subject') }}" placeholder="Subjek" class="field" required>
                    <label class="sr-only" for="h-message">Pesan</label>
                    <textarea id="h-message" name="message" rows="5" placeholder="Pesan" class="field resize-y sm:col-span-2" required>{{ old('message') }}</textarea>
                    <label class="flex items-start gap-3 text-sm sm:col-span-2">
                        <input type="checkbox" name="consent" value="1" class="mt-0.5 h-4 w-4 accent-[#3498db]" required>
                        <span>Saya menyetujui data ini digunakan untuk menindaklanjuti pesan saya.</span>
                    </label>
                    @if ($errors->any())
                        <p class="text-sm text-spark sm:col-span-2">Mohon lengkapi isian. <a href="{{ route('contact') }}" class="underline">Buka formulir lengkap</a>.</p>
                    @endif
                    <div class="text-center sm:col-span-2 sm:text-right">
                        <button class="btn btn-primary">Kirim Pesan</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</x-layouts.site>
