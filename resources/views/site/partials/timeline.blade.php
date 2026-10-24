{{--
    Interactive timeline: projects (left) and news (right) per year, with
    milestones annotated on the axis. Hover/focus a square for details,
    click it to open the item; legend buttons filter project categories.
--}}
@php
    $palette = ['#3498db', '#173f5f', '#f5b700', '#5dade2', '#16a085', '#e67e22', '#7f8c9a', '#2775ad'];
    $projectCategories = collect($timeline)->pluck('projects')->flatten(1)->pluck('category')->unique()->values();
    $colors = $projectCategories->mapWithKeys(fn ($c, $i) => [$c => $palette[$i % count($palette)]]);
    $newsColor = '#c2ccd6';
@endphp

@if (count($timeline))
    <section class="relative bg-white py-20 md:py-24" data-timeline>
        <div class="container-x">
            <x-section-heading title="Jejak Proyek & Kabar" />

            <div class="mx-auto mt-8 max-w-xl rounded-lg border border-dashed border-ink/40 px-6 py-5 text-center" data-reveal>
                <p class="text-[15px] leading-relaxed">Setiap kotak di kiri adalah <span class="font-semibold text-ink">proyek</span>, di kanan adalah <span class="font-semibold text-ink">berita</span>. Arahkan kursor ke kotak untuk melihat detailnya, klik untuk membukanya, atau saring proyek menurut kategori.</p>
            </div>

            <div class="mt-8 flex flex-wrap justify-center gap-2" data-reveal="fade">
                @foreach ($colors as $category => $color)
                    <button type="button" data-tl-filter="{{ $category }}" aria-pressed="true" class="flex items-center gap-2 rounded-full border border-line px-3.5 py-1.5 text-[13px] text-ink transition hover:border-brand aria-[pressed=false]:text-subtle aria-[pressed=false]:line-through">
                        <span class="h-2.5 w-2.5 rounded-[2px]" style="background: {{ $color }}"></span>{{ $category }}
                    </button>
                @endforeach
                <span class="flex items-center gap-2 px-3.5 py-1.5 text-[13px] text-subtle"><span class="h-2.5 w-2.5 rounded-[2px]" style="background: {{ $newsColor }}"></span>Berita</span>
            </div>

            <div class="relative mx-auto mt-12 max-w-4xl" data-tl-body>
                <div class="grid grid-cols-[1fr_auto_1fr] gap-4 pb-4 font-display text-xs font-bold tracking-[0.14em] text-subtle uppercase md:gap-8">
                    <span class="text-right">Proyek</span><span class="w-24 md:w-40"></span><span>Berita</span>
                </div>

                @foreach ($timeline as $row)
                    <div class="grid min-h-[78px] grid-cols-[1fr_auto_1fr] gap-4 md:gap-8" data-tl-row>
                        <div class="flex flex-wrap content-start justify-end gap-2 pt-1">
                            @foreach ($row['projects'] as $item)
                                <a href="{{ $item['url'] }}" class="block h-5 w-5 rounded-[3px] outline-2 outline-offset-2 outline-transparent transition-[opacity,outline-color] duration-200 hover:outline-ink/50 focus-visible:outline-ink"
                                   style="background: {{ $colors[$item['category']] }}" data-tl-mark data-category="{{ $item['category'] }}" data-color="{{ $colors[$item['category']] }}"
                                   data-title="{{ $item['title'] }}" data-meta="{{ $item['category'] }} · {{ $item['meta'] }}" aria-label="Proyek: {{ $item['title'] }}"></a>
                            @endforeach
                        </div>

                        <div class="relative flex w-24 flex-col items-center md:w-40">
                            <span class="absolute inset-y-0 left-1/2 w-px origin-top -translate-x-1/2 bg-ink/25" data-tl-axis></span>
                            <span class="relative z-10 flex items-center gap-2 bg-white py-1 font-display text-sm font-bold text-subtle">
                                <span class="h-2.5 w-2.5 rounded-full border-2 border-subtle bg-white"></span>{{ $row['year'] }}
                            </span>
                            @foreach ($row['milestones'] as $milestone)
                                <span class="relative z-10 mt-2 max-w-full rounded-md bg-mist px-2.5 py-1 text-center text-[11px] leading-snug font-semibold text-ink md:text-xs">{{ $milestone }}</span>
                            @endforeach
                        </div>

                        <div class="flex flex-wrap content-start gap-2 pt-1">
                            @foreach ($row['news'] as $item)
                                <a href="{{ $item['url'] }}" class="block h-5 w-5 rounded-[3px] outline-2 outline-offset-2 outline-transparent transition-[outline-color] duration-200 hover:outline-ink/50 focus-visible:outline-ink"
                                   style="background: {{ $newsColor }}" data-tl-mark data-color="#7f8c9a"
                                   data-title="{{ $item['title'] }}" data-meta="{{ $item['category'] }} · {{ $item['meta'] }}" aria-label="Berita: {{ $item['title'] }}"></a>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="pointer-events-none absolute top-0 left-0 z-20 w-64 rounded-md px-4 py-3 text-sm text-white opacity-0 shadow-xl transition-opacity duration-150" data-tl-tip role="tooltip"></div>
            </div>

            <details class="group mx-auto mt-10 max-w-4xl rounded-md bg-mist px-5 py-4 text-sm" data-reveal="fade">
                <summary class="flex cursor-pointer list-none items-center justify-between font-semibold text-ink">
                    Tentang data
                    <x-glyph name="chevron-down" class="h-4 w-4 transition-transform group-open:rotate-180" />
                </summary>
                <p class="mt-3 leading-relaxed">Data proyek, berita, dan milestone diambil langsung dari portal admin PLN Services (menu Portfolio, Berita, dan Milestone). Proyek dikelompokkan menurut tahun pengerjaan; berita menurut tanggal publikasi.</p>
            </details>
        </div>
    </section>
@endif
