@props(['slides'])
{{--
    Home hero graphic: two coloured circles; their intersection (the "lens") frames a project photo.
    resources/js/site/hero.js positions both circles every frame from an angle, a half-distance and a
    radius, so they can orbit, shrink to dots and pop back open while the photo stays upright.
    Open state (viewBox 760×600): R = 210, centres at 380 ∓ 76.
--}}
@php
    [$cx, $cy, $r, $half] = [380, 300, 210, 76];
    $photoH = 460; // taller than the lens so the photo can drift without showing edges
@endphp
<svg viewBox="0 0 760 600" {{ $attributes->merge(['class' => 'block h-auto w-full overflow-visible']) }} aria-hidden="true" data-hv>
    <defs>
        <clipPath id="hv-clip-a"><circle data-hv="a" cx="{{ $cx - $half }}" cy="{{ $cy }}" r="{{ $r }}" /></clipPath>
        <clipPath id="hv-clip-b"><circle data-hv="b" cx="{{ $cx + $half }}" cy="{{ $cy }}" r="{{ $r }}" /></clipPath>
    </defs>

    <g data-hv-rig>
        <circle data-hv="a" data-hv-fill="a" cx="{{ $cx - $half }}" cy="{{ $cy }}" r="{{ $r }}" fill="{{ $slides[0]['colors'][0] }}" />
        <circle data-hv="b" data-hv-fill="b" cx="{{ $cx + $half }}" cy="{{ $cy }}" r="{{ $r }}" fill="{{ $slides[0]['colors'][1] }}" />
        <g clip-path="url(#hv-clip-a)">
            <g clip-path="url(#hv-clip-b)">
                <g data-hv-lens>
                    <rect x="{{ $cx - $r - 40 }}" y="{{ $cy - $r - 40 }}" width="{{ 2 * $r + 80 }}" height="{{ 2 * $r + 80 }}" fill="#1f5f8f" />
                    <g data-hv-photo>
                        @foreach ($slides as $i => $slide)
                            @php
                                [$w, $h] = getimagesize(public_path("images/hero/{$slide['img']}.jpg"));
                                $photoW = $photoH * $w / $h;
                                $x = min($cx - $r - 30, max($cx + $r + 30 - $photoW, $cx - $slide['focal'] * $photoW));
                            @endphp
                            <image href="{{ asset("images/hero/{$slide['img']}.jpg") }}" x="{{ round($x) }}" y="{{ $cy - $photoH / 2 }}" width="{{ round($photoW) }}" height="{{ $photoH }}" preserveAspectRatio="none" opacity="{{ $i === 0 ? 1 : 0 }}" data-hv-img />
                        @endforeach
                    </g>
                </g>
            </g>
        </g>
    </g>

    {{-- Labels with leader lines to the circle edges (at 225° and 45°) --}}
    <g class="font-display text-[15px] font-bold tracking-[0.14em] uppercase" fill="#222">
        <g data-hv-label>
            <path d="M{{ $cx - $half - 149 }} {{ $cy - 149 }} L{{ $cx - $half - 190 }} {{ $cy - 190 }}" stroke="#222" stroke-width="1.5" data-hv-line />
            <text x="{{ $cx - $half - 196 }}" y="{{ $cy - 200 }}" text-anchor="end" data-hv-text>{{ $slides[0]['a'] }}</text>
        </g>
        <g data-hv-label>
            <path d="M{{ $cx + $half + 149 }} {{ $cy + 149 }} L{{ $cx + $half + 190 }} {{ $cy + 190 }}" stroke="#222" stroke-width="1.5" data-hv-line />
            <text x="{{ $cx + $half + 196 }}" y="{{ $cy + 210 }}" data-hv-text>{{ $slides[0]['b'] }}</text>
        </g>
    </g>
</svg>
