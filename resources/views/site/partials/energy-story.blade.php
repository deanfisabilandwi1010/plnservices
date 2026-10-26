{{-- "Perjalanan Energi" scrollytelling — drawn and animated by resources/js/site/energy-story.js. Step texts are trusted markup. --}}
@php
    $steps = [
        ['Sumber', 'Energi bermula dari banyak sumber.', 'Setiap titik mewakili sumber energi — biru untuk pembangkit konvensional, kuning untuk energi baru terbarukan. <span class="hidden lg:inline">Arahkan kursor ke</span><span class="lg:hidden">Ketuk</span> titik untuk melihat jenisnya.'],
        ['Pembangkit', 'Kami mengoperasikan dan merawat pembangkitnya.', 'Dari PLTD hingga PLTS, keandalan setiap pembangkit dijaga tim operasi dan pemeliharaan PLN Services.<span class="hidden lg:inline"> Klik legenda untuk menyorot satu jenis.</span>'],
        ['Jaringan', 'Energi mengalir lewat transmisi dan distribusi.', 'Jaringan dipelihara dan dipantau agar listrik mengalir tanpa putus, dari gardu hingga ujung jaringan.'],
        ['Pelanggan', 'Hingga menyala di tempat pelanggan.', 'Rumah, industri, dan layanan publik di seluruh Indonesia menerima listrik yang andal, aman, dan efisien.'],
    ];
@endphp
<section id="energi" class="relative scroll-mt-[136px] bg-mist" data-energy-story>
    <div class="sticky top-0 h-svh overflow-hidden" data-es-pin>
        <canvas class="absolute inset-0 h-full w-full" data-es-canvas aria-hidden="true"></canvas>
        <div class="pointer-events-none absolute inset-0" data-es-labels aria-hidden="true"></div>

        <div class="pointer-events-none absolute top-0 left-0 z-20 w-60 rounded-md border-l-4 bg-white px-4 py-3 opacity-0 shadow-xl transition-opacity duration-150" data-es-tip role="tooltip">
            <p class="font-display text-lg font-bold text-ink"></p>
            <p class="text-[13px] leading-snug text-body"></p>
            <p class="mt-1.5 text-xs font-semibold text-subtle"></p>
        </div>

        <div class="absolute bottom-6 left-[42%] z-20 hidden gap-2 lg:flex" data-es-legend>
            <button type="button" data-es-filter="blue" aria-pressed="true" class="flex items-center gap-2 rounded-full border border-line bg-white px-3.5 py-1.5 text-[13px] text-ink shadow-sm transition hover:border-brand aria-[pressed=false]:text-subtle">
                <span class="h-2.5 w-2.5 rounded-full bg-brand"></span>Konvensional
            </button>
            <button type="button" data-es-filter="yellow" aria-pressed="true" class="flex items-center gap-2 rounded-full border border-line bg-white px-3.5 py-1.5 text-[13px] text-ink shadow-sm transition hover:border-brand aria-[pressed=false]:text-subtle">
                <span class="h-2.5 w-2.5 rounded-full bg-[#ffcd00] ring-1 ring-[#d6a000]"></span>Terbarukan
            </button>
        </div>
    </div>

    {{-- Narrative cards scroll over the pinned canvas --}}
    <div class="pointer-events-none relative z-10 -mt-[100svh]">
        @foreach ($steps as $i => [$kicker, $lead, $text])
            <div class="flex h-svh items-end pb-6 lg:items-center lg:pb-0" data-es-step>
                <div class="container-x">
                    <div class="pointer-events-auto max-w-sm rounded-[10px] bg-white p-5 shadow-[0_8px_30px_-12px_rgb(0_0_0/0.18)] md:p-7">
                        <p class="kicker">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }} · {{ $kicker }}</p>
                        <p class="mt-2 font-display text-lg leading-snug font-bold text-ink md:mt-3 md:text-2xl">{{ $lead }}</p>
                        <p class="mt-2 text-sm leading-relaxed md:text-[15px]">{!! $text !!}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <p class="sr-only">Ilustrasi perjalanan energi: dari sumber energi konvensional dan terbarukan, ke pembangkit, melalui jaringan transmisi dan distribusi, hingga ke pelanggan.</p>
</section>
