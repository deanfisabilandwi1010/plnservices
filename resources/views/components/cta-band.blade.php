@props(['title' => 'Mari bergerak bersama.', 'text' => 'Ceritakan kebutuhan Anda — tim PLN Services siap membantu merumuskan solusi ketenagalistrikan yang tepat.'])
<section class="relative overflow-hidden bg-brand py-20 text-white md:py-28">
    <x-line-art variant="network" class="pointer-events-none absolute top-1/2 right-0 hidden h-[120%] w-auto -translate-y-1/2 !text-white/45 lg:block" />
    <div class="container-x relative">
        <p class="split-lead max-w-3xl !text-white" data-split>{{ $title }}</p>
        <p class="mt-5 max-w-xl text-[1.05rem] leading-relaxed text-white/85" data-reveal>{{ $text }}</p>
        <div class="mt-9 flex flex-wrap gap-3" data-reveal data-delay="0.1">
            <a href="{{ route('contact') }}" class="btn btn-accent">Hubungi Kami <x-glyph name="arrow-right" class="h-4 w-4" /></a>
            {{ $slot }}
        </div>
    </div>
</section>
