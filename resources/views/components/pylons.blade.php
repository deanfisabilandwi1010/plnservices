@props(['stroke' => 'currentColor', 'wire' => '#ffde00'])
{{-- Line-art transmission towers with flowing conductors. --}}
@php
    $towers = [[150, 400, 300, 120], [400, 400, 210, 84], [560, 400, 140, 56]]; // [cx, baseY, height, width]

    $tower = function (float $cx, float $by, float $h, float $w): array {
        $top = $by - $h;
        $lx = fn ($t) => $cx - ($w / 2) * (1 - $t) - ($w * 0.08) * $t; // left leg x at height fraction t
        $rx = fn ($t) => 2 * $cx - $lx($t);
        $paths = [];
        $paths[] = sprintf('M%.1f %.1f L%.1f %.1f L%.1f %.1f', $lx(0), $by, $lx(0.86), $by - $h * 0.86, $cx, $top);
        $paths[] = sprintf('M%.1f %.1f L%.1f %.1f L%.1f %.1f', $rx(0), $by, $rx(0.86), $by - $h * 0.86, $cx, $top);
        // lattice
        $levels = [0, 0.18, 0.34, 0.48, 0.6, 0.7, 0.78, 0.86];
        for ($i = 0; $i < count($levels) - 1; $i++) {
            [$a, $b] = [$levels[$i], $levels[$i + 1]];
            $paths[] = sprintf('M%.1f %.1f L%.1f %.1f M%.1f %.1f L%.1f %.1f M%.1f %.1f L%.1f %.1f',
                $lx($a), $by - $h * $a, $rx($b), $by - $h * $b,
                $rx($a), $by - $h * $a, $lx($b), $by - $h * $b,
                $lx($b), $by - $h * $b, $rx($b), $by - $h * $b);
        }
        // cross-arms
        $arms = [[0.72, 0.95], [0.86, 0.7]];
        $ends = [];
        foreach ($arms as [$t, $span]) {
            $y = $by - $h * $t;
            $paths[] = sprintf('M%.1f %.1f L%.1f %.1f L%.1f %.1f M%.1f %.1f L%.1f %.1f L%.1f %.1f',
                $lx($t), $y, $cx - $w * $span, $y, $lx($t - 0.06), $by - $h * ($t - 0.06),
                $rx($t), $y, $cx + $w * $span, $y, $rx($t - 0.06), $by - $h * ($t - 0.06));
            $ends[] = [$cx - $w * $span, $y + 10];
            $ends[] = [$cx + $w * $span, $y + 10];
        }

        return [$paths, $ends];
    };

    $built = array_map(fn ($t) => $tower(...$t), $towers);
@endphp
<svg viewBox="0 0 640 400" fill="none" {{ $attributes }} aria-hidden="true">
    @foreach ($built as [$paths])
        @foreach ($paths as $d)
            <path d="{{ $d }}" stroke="{{ $stroke }}" stroke-width="1.2" stroke-linejoin="round" />
        @endforeach
    @endforeach

    {{-- Conductors: into the first tower, between towers, and out of frame --}}
    @foreach ($built as $i => [, $ends])
        @foreach ($ends as $k => [$x1, $y1])
            @php
                [$x2, $y2] = isset($built[$i + 1]) ? $built[$i + 1][1][$k] : [660, $y1 + 30];
                $sag = 24 - $i * 6;
                $d = sprintf('M%.1f %.1f Q%.1f %.1f %.1f %.1f', $x1, $y1, ($x1 + $x2) / 2, max($y1, $y2) + $sag, $x2, $y2);
            @endphp
            @if ($i === 0)
                <path d="M-20 {{ $y1 - 14 }} Q{{ ($x1 - 20) / 2 }} {{ $y1 + 22 }} {{ $x1 }} {{ $y1 }}" stroke="{{ $stroke }}" stroke-opacity=".45" stroke-width="1" />
            @endif
            <path d="{{ $d }}" stroke="{{ $stroke }}" stroke-opacity=".45" stroke-width="1" />
            <path class="pylon-wire" d="{{ $d }}" stroke="{{ $wire }}" stroke-width="1.6" />
        @endforeach
    @endforeach

    <line x1="0" y1="400" x2="640" y2="400" stroke="{{ $stroke }}" stroke-opacity=".5" stroke-width="1" />
</svg>
