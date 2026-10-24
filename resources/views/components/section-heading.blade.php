@props(['title', 'subtitle' => null, 'dark' => false])
{{-- Centred "—— TITLE ——" heading used across the corporate site. --}}
<div {{ $attributes->merge(['class' => 'mx-auto max-w-2xl text-center']) }}>
    <h2 class="section-title {{ $dark ? '!text-white before:!bg-white after:!bg-white' : '' }}" data-reveal="fade">{{ $title }}</h2>
    @if ($subtitle)
        <p class="mt-4 text-[15px] leading-relaxed {{ $dark ? 'text-white/80' : '' }}" data-reveal data-delay="0.1">{{ $subtitle }}</p>
    @endif
</div>
