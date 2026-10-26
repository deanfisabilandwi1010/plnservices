{{--
    Scrollytelling: one square per service unit. The squares stay pinned while
    narrative cards scroll over them; each card re-arranges the squares
    (see resources/js/site/unit-story.js).
--}}
@php
    $palette = ['#3498db', '#173f5f', '#f5b700', '#5dade2', '#7f8c9a', '#2775ad', '#e67e22', '#16a085'];
    $areas = $workAreas->filter(fn ($a) => $a->units > 0)->values()->map(fn ($a, $i) => [
        'name' => $a->name,
        'provinces' => $a->provinces,
        'units' => (int) $a->units,
        'color' => $palette[$i % count($palette)],
    ]);
    $total = $areas->sum('units');
    $top = $areas->sortByDesc('units')->first();
@endphp

@if ($total > 0)
    <section class="relative bg-white" data-unit-story data-areas="{{ $areas->toJson() }}">
        {{-- Pinned stage --}}
        <div class="pointer-events-none sticky top-0 z-10 h-svh overflow-hidden">
            <div class="absolute inset-x-0 top-[80px] bottom-0" data-us-stage aria-hidden="true"></div>

            <div class="pointer-events-none absolute z-20 w-64 rounded-md px-4 py-3 text-sm text-white opacity-0 shadow-xl transition-opacity duration-150" data-us-tip role="tooltip"></div>

            <div class="absolute inset-x-0 bottom-5 z-10 flex gap-2 overflow-x-auto px-4 pb-1 opacity-0 transition-opacity duration-500 [scrollbar-width:none] sm:bottom-6 sm:flex-wrap sm:justify-center sm:overflow-visible" data-us-legend data-lenis-prevent>
                @foreach ($areas as $i => $area)
                    <button type="button" data-us-filter="{{ $i }}" aria-pressed="true" class="flex shrink-0 items-center gap-2 rounded-full border border-line bg-white px-3.5 py-1.5 text-[13px] text-ink shadow-sm transition hover:border-brand aria-[pressed=false]:text-subtle aria-[pressed=false]:line-through">
                        <span class="h-2.5 w-2.5 rounded-[2px]" style="background: {{ $area['color'] }}"></span>{{ $area['name'] }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Narrative steps --}}
        @php
            $steps = [
                ['Setiap kotak mewakili satu unit layanan PLN Services.', 'Saat ini terdapat '.number_format($total, 0, ',', '.').' unit yang melayani pelanggan di seluruh Indonesia.'],
                ['Unit-unit tersebut tersebar di '.$areas->count().' wilayah kerja.', 'Dari Sumatera hingga Papua, setiap wilayah memiliki tim yang siap merespons kebutuhan energi.'],
                ['Dari barat ke timur nusantara.', 'Jelajahi sendiri: klik nama wilayah pada legenda untuk menyorotnya, dan arahkan kursor ke kotak untuk melihat cakupan provinsinya.'],
                [$top['name'].' memiliki unit terbanyak.', 'Sebanyak '.$top['units'].' unit layanan beroperasi di wilayah ini — '.round($top['units'] / $total * 100).'% dari seluruh jaringan.'],
            ];
        @endphp
        <div class="pointer-events-none relative z-20 -mt-[100svh]">
            @foreach ($steps as $i => [$lead, $text])
                <div class="pointer-events-none flex items-start justify-center px-5 pt-[104px] {{ $loop->last ? 'h-[115svh]' : 'h-[85svh]' }}" data-us-step="{{ $i }}">
                    <div class="max-w-lg rounded-lg border border-dashed border-ink/40 bg-white px-6 py-5 shadow-[0_8px_30px_-12px_rgb(0_0_0/0.15)]">
                        <p class="font-display text-lg leading-snug font-bold text-ink md:text-xl">{{ $lead }}</p>
                        <p class="mt-1.5 text-[15px] leading-relaxed">{{ $text }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Accessible summary of what the graphic shows --}}
        <ul class="sr-only">
            @foreach ($areas as $area)
                <li>{{ $area['name'] }}: {{ $area['units'] }} unit ({{ $area['provinces'] }})</li>
            @endforeach
        </ul>
    </section>
@endif
