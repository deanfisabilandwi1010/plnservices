@props(['src' => null, 'alt' => '', 'icon' => 'zap', 'seed' => 0])
{{-- Uploaded image, or a calm branded placeholder until one is uploaded. --}}
@php
    $url = media_url($src);
    $tones = [['#ebf5fc', '#3498db'], ['#eef3f8', '#2775ad'], ['#f3f8fc', '#3498db'], ['#e8f1f9', '#1f5f8f']];
    [$bg, $fg] = $tones[crc32((string) $seed) % count($tones)];
@endphp
<div {{ $attributes->merge(['class' => 'relative overflow-hidden bg-mist']) }}>
    @if ($url)
        <img src="{{ $url }}" alt="{{ $alt }}" loading="lazy" class="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.04]">
    @else
        <div class="absolute inset-0" style="background: {{ $bg }}; color: {{ $fg }}">
            <x-pylons class="absolute right-0 bottom-0 h-[85%] w-auto opacity-30" wire="{{ $fg }}" />
            <div class="absolute top-5 left-5 flex h-11 w-11 items-center justify-center rounded-md bg-white shadow-sm">
                <x-glyph :name="$icon" class="h-5 w-5" />
            </div>
        </div>
    @endif
    {{ $slot }}
</div>
