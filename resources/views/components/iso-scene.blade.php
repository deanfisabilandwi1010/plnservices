{{--
    Animated isometric energy scene (original artwork, pure SVG):
    power plant with cooling towers, substation, transmission line,
    PLN Services offices, wind turbines, solar farm and a river.
    Motion is CSS-only (see "Isometric scene" in resources/css/app.css).
--}}
@php
    $S = 20; // px per world unit

    // World (x, y, z) → screen (isometric).
    $p = fn ($x, $y, $z = 0) => [round(($x - $y) * 0.866 * $S, 1), round(($x + $y) * 0.5 * $S - $z * $S, 1)];
    $pts = fn (...$ps) => implode(' ', array_map(fn ($q) => $q[0].','.$q[1], $ps));

    // Extruded box; $c = [top, left face, right face].
    $box = function ($x, $y, $w, $d, $h, $z, $c) use ($p, $pts) {
        $top = $pts($p($x, $y, $z + $h), $p($x + $w, $y, $z + $h), $p($x + $w, $y + $d, $z + $h), $p($x, $y + $d, $z + $h));
        $left = $pts($p($x, $y + $d, $z), $p($x + $w, $y + $d, $z), $p($x + $w, $y + $d, $z + $h), $p($x, $y + $d, $z + $h));
        $right = $pts($p($x + $w, $y, $z), $p($x + $w, $y + $d, $z), $p($x + $w, $y + $d, $z + $h), $p($x + $w, $y, $z + $h));

        return "<polygon points=\"{$left}\" fill=\"{$c[1]}\"/><polygon points=\"{$right}\" fill=\"{$c[2]}\"/><polygon points=\"{$top}\" fill=\"{$c[0]}\"/>";
    };

    // Window grid on the two visible faces of a box.
    $windows = function ($x, $y, $w, $d, $h, $z, $color, $lit) use ($p, $pts) {
        $out = '';
        $rows = max(1, (int) floor($h / 0.55));
        for ($r = 0; $r < $rows; $r++) {
            $z0 = $z + 0.25 + $r * 0.55;
            for ($i = 0.25; $i + 0.35 <= $w; $i += 0.6) { // left face (y + d)
                $cls = ($r + (int) ($i * 10)) % 5 === 0 ? ' class="iso-window" style="animation-delay:-'.(($r * 7 + $i * 13) % 5).'s"' : '';
                $out .= '<polygon'.$cls.' points="'.$pts($p($x + $i, $y + $d, $z0), $p($x + $i + 0.35, $y + $d, $z0), $p($x + $i + 0.35, $y + $d, $z0 + 0.3), $p($x + $i, $y + $d, $z0 + 0.3)).'" fill="'.$color.'"/>';
            }
            for ($i = 0.25; $i + 0.35 <= $d; $i += 0.6) { // right face (x + w)
                $out .= '<polygon points="'.$pts($p($x + $w, $y + $i, $z0), $p($x + $w, $y + $i + 0.35, $z0), $p($x + $w, $y + $i + 0.35, $z0 + 0.3), $p($x + $w, $y + $i, $z0 + 0.3)).'" fill="'.$lit.'"/>';
            }
        }

        return $out;
    };

    // Hyperboloid cooling tower with rising steam.
    $coolingTower = function ($cx, $cy, $rb, $h, $delay) use ($p, $S) {
        [$bx, $by] = $p($cx, $cy, 0);
        $rx = fn ($r) => round(1.2247 * $r * $S, 1);
        $ry = fn ($r) => round(0.7071 * $r * $S, 1);
        $rw = $rb * 0.66;
        $rt = $rb * 0.74;
        $H = $h * $S;
        $path = 'M'.($bx - $rx($rb)).','.$by
            .' C'.($bx - $rx($rw)).','.($by - 0.35 * $H).' '.($bx - $rx($rw)).','.($by - 0.75 * $H).' '.($bx - $rx($rt)).','.($by - $H)
            .' L'.($bx + $rx($rt)).','.($by - $H)
            .' C'.($bx + $rx($rw)).','.($by - 0.75 * $H).' '.($bx + $rx($rw)).','.($by - 0.35 * $H).' '.($bx + $rx($rb)).','.$by
            .' A'.$rx($rb).' '.$ry($rb).' 0 0 1 '.($bx - $rx($rb)).','.$by.' Z';
        $smoke = '';
        foreach ([0, 1, 2, 3] as $k) {
            $smoke .= '<circle class="iso-smoke" cx="'.($bx + ($k % 2 ? 4 : -4)).'" cy="'.($by - $H - 6).'" r="'.(9 + $k * 2).'" fill="#fff" style="animation-delay:-'.($delay + $k * 1.1).'s"/>';
        }

        return '<ellipse cx="'.$bx.'" cy="'.$by.'" rx="'.($rx($rb) + 4).'" ry="'.($ry($rb) + 3).'" fill="#000" opacity=".08"/>'
            .'<path d="'.$path.'" fill="url(#iso-tower)"/>'
            .'<ellipse cx="'.$bx.'" cy="'.($by - $H).'" rx="'.$rx($rt).'" ry="'.$ry($rt).'" fill="#c9d2d8"/>'
            .'<ellipse cx="'.$bx.'" cy="'.($by - $H + 1).'" rx="'.($rx($rt) - 3).'" ry="'.($ry($rt) - 2).'" fill="#7d8a94"/>'
            .'<g class="iso-steam">'.$smoke.'</g>';
    };

    // Lattice transmission tower drawn upright at a ground point; returns [svg, arm ends].
    $pylon = function ($x, $y, $h) use ($p) {
        [$bx, $by] = $p($x, $y, 0);
        $t = $by - $h;
        $lines = [
            [$bx - 9, $by, $bx - 2, $t + 6], [$bx + 9, $by, $bx + 2, $t + 6],
            [$bx - 9, $by, $bx + 6, $by - $h * 0.35], [$bx + 9, $by, $bx - 6, $by - $h * 0.35],
            [$bx - 6, $by - $h * 0.35, $bx + 4, $by - $h * 0.62], [$bx + 6, $by - $h * 0.35, $bx - 4, $by - $h * 0.62],
            [$bx - 2, $t + 6, $bx, $t], [$bx + 2, $t + 6, $bx, $t],
            [$bx - 18, $by - $h * 0.72, $bx + 18, $by - $h * 0.72], [$bx - 13, $by - $h * 0.88, $bx + 13, $by - $h * 0.88],
        ];
        $svg = '<ellipse cx="'.$bx.'" cy="'.$by.'" rx="12" ry="5" fill="#000" opacity=".08"/>';
        foreach ($lines as [$x1, $y1, $x2, $y2]) {
            $svg .= '<line x1="'.$x1.'" y1="'.$y1.'" x2="'.$x2.'" y2="'.$y2.'" stroke="#6b7c8a" stroke-width="1.3" stroke-linecap="round"/>';
        }

        return [$svg, [[$bx - 18, $by - $h * 0.72 + 3], [$bx + 18, $by - $h * 0.72 + 3], [$bx - 13, $by - $h * 0.88 + 3], [$bx + 13, $by - $h * 0.88 + 3]]];
    };

    $wire = function ($a, $b) {
        $mx = ($a[0] + $b[0]) / 2;
        $my = max($a[1], $b[1]) + 12;
        $d = "M{$a[0]},{$a[1]} Q{$mx},{$my} {$b[0]},{$b[1]}";

        return '<path d="'.$d.'" fill="none" stroke="#9fb1bf" stroke-width="1"/><path class="iso-pulse" d="'.$d.'" fill="none" stroke="#3498db" stroke-width="1.8" stroke-linecap="round"/>';
    };

    $tree = function ($x, $y, $s = 1) use ($p) {
        [$bx, $by] = $p($x, $y, 0);

        return '<ellipse cx="'.$bx.'" cy="'.$by.'" rx="'.(7 * $s).'" ry="'.(3 * $s).'" fill="#000" opacity=".1"/>'
            .'<line x1="'.$bx.'" y1="'.$by.'" x2="'.$bx.'" y2="'.($by - 10 * $s).'" stroke="#8a6a4a" stroke-width="2"/>'
            .'<circle cx="'.$bx.'" cy="'.($by - 15 * $s).'" r="'.(8 * $s).'" fill="#7cc242"/>'
            .'<circle cx="'.($bx - 2 * $s).'" cy="'.($by - 17 * $s).'" r="'.(4 * $s).'" fill="#9bd35f"/>';
    };

    $turbine = function ($x, $y, $h, $delay) use ($p) {
        [$bx, $by] = $p($x, $y, 0);
        $hy = $by - $h;
        $blade = 'M0,0 C3,-4 3,-22 0,-30 C-2,-22 -2,-4 0,0 Z';

        return '<ellipse cx="'.$bx.'" cy="'.$by.'" rx="6" ry="2.5" fill="#000" opacity=".1"/>'
            .'<polygon points="'.($bx - 2.2).','.$by.' '.($bx + 2.2).','.$by.' '.($bx + 1).','.$hy.' '.($bx - 1).','.$hy.'" fill="#e8eef2"/>'
            .'<polygon points="'.($bx + 0.4).','.$by.' '.($bx + 2.2).','.$by.' '.($bx + 1).','.$hy.'" fill="#c5d0d8"/>'
            .'<g class="iso-blades" style="transform-origin:'.$bx.'px '.$hy.'px;animation-delay:-'.$delay.'s"><g transform="translate('.$bx.' '.$hy.')">'
            .'<path d="'.$blade.'" fill="#fff" stroke="#c5d0d8" stroke-width=".6"/>'
            .'<path d="'.$blade.'" fill="#fff" stroke="#c5d0d8" stroke-width=".6" transform="rotate(120)"/>'
            .'<path d="'.$blade.'" fill="#fff" stroke="#c5d0d8" stroke-width=".6" transform="rotate(240)"/>'
            .'</g></g><circle cx="'.$bx.'" cy="'.$hy.'" r="2.6" fill="#3498db"/>';
    };

    // River: meandering band across the front of the platform.
    $riverY = fn ($x) => 10.2 + 0.16 * $x + 0.35 * sin($x * 0.75);
    $bankA = $bankB = $centre = [];
    for ($x = 0; $x <= 14.001; $x += 0.5) {
        $bankA[] = $p($x, $riverY($x) - 0.6, 0.01);
        $bankB[] = $p($x, $riverY($x) + 0.6, 0.01);
        $centre[] = $p($x, $riverY($x), 0.02);
    }
    $river = $pts(...array_merge($bankA, array_reverse($bankB)));
    $riverLine = $pts(...$centre);

    $pylons = [$pylon(2.4, 4.6, 66), $pylon(1.6, 8.0, 70), $pylon(4.2, 12.9, 74)];
@endphp

<svg viewBox="-252 -60 504 360" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Ilustrasi jaringan energi PLN Services" {{ $attributes->merge(['class' => 'iso-scene']) }}>
    <defs>
        <linearGradient id="iso-tower" x1="0" x2="1">
            <stop offset="0" stop-color="#eef2f5" />
            <stop offset=".55" stop-color="#d3dbe1" />
            <stop offset="1" stop-color="#aab6bf" />
        </linearGradient>
    </defs>

    <g class="iso-float">
        {{-- Platform & paving --}}
        {!! $box(0, 0, 14, 14, 0.5, -0.5, ['#cfe8a9', '#9bc36d', '#86ad5c']) !!}
        {!! $box(0.6, 0.6, 12.8, 7.6, 0.06, 0, ['#edf2f6', '#cfd8df', '#c2ccd4']) !!}

        {{-- River --}}
        <polygon points="{{ $river }}" fill="#7fcdf0" />
        <polyline class="iso-river" points="{{ $riverLine }}" fill="none" stroke="#c9ecfb" stroke-width="2" stroke-linecap="round" />

        {{-- Substation --}}
        {!! $box(0.9, 0.9, 3.6, 2.6, 0.08, 0.06, ['#dfe6ec', '#bcc7d0', '#aeb9c3']) !!}
        @foreach ([[1.2, 1.2], [2.4, 1.2], [3.6, 1.2], [1.2, 2.4], [2.4, 2.4], [3.6, 2.4]] as [$tx, $ty])
            {!! $box($tx, $ty, 0.6, 0.5, 0.55, 0.14, ['#9fb3c2', '#7f95a6', '#6d8394']) !!}
        @endforeach

        {{-- Power plant --}}
        {!! $box(5.6, 1.0, 3.4, 2.6, 2.2, 0.06, ['#d9e1e7', '#b5c2cc', '#a3b1bc']) !!}
        {!! $windows(5.6, 1.0, 3.4, 2.6, 2.2, 0.06, '#8fa3b3', '#9db0bf') !!}
        {!! $box(6.2, 1.4, 1.4, 1.2, 0.6, 2.26, ['#c7d1d9', '#a6b4bf', '#97a6b2']) !!}
        {!! $coolingTower(11.7, 1.3, 1.25, 3.4, 0) !!}

        {{-- Chimney --}}
        @php [$cx, $cy] = $p(9.6, 4.4, 0); @endphp
        <rect x="{{ $cx - 5 }}" y="{{ $cy - 104 }}" width="10" height="104" fill="#e9eef2" />
        @foreach ([0, 2, 4] as $band)
            <rect x="{{ $cx - 5 }}" y="{{ $cy - 104 + $band * 9 }}" width="10" height="9" fill="#e5484d" />
        @endforeach
        <ellipse cx="{{ $cx }}" cy="{{ $cy - 104 }}" rx="5" ry="2" fill="#7d8a94" />
        <g class="iso-steam">
            @foreach ([0, 1, 2] as $k)
                <circle class="iso-smoke iso-smoke-sm" cx="{{ $cx }}" cy="{{ $cy - 110 }}" r="{{ 5 + $k }}" fill="#fff" style="animation-delay:-{{ 0.6 + $k * 1.2 }}s" />
            @endforeach
        </g>

        {!! $coolingTower(12.9, 3.3, 1.1, 3.0, 1.7) !!}

        {{-- Transmission line from the substation --}}
        @foreach ($pylons as [$svg])
            {!! $svg !!}
        @endforeach
        @foreach ([[0, 1], [1, 2]] as [$a, $b])
            @foreach ([0, 1, 2, 3] as $k)
                {!! $wire($pylons[$a][1][$k], $pylons[$b][1][$k]) !!}
            @endforeach
        @endforeach
        @php [$sx, $sy] = $p(2.2, 2.6, 0.7); @endphp
        @foreach ([0, 1] as $k)
            {!! $wire([$sx + ($k ? 6 : -6), $sy], $pylons[0][1][$k]) !!}
        @endforeach

        {{-- PLN Services offices --}}
        {!! $box(5.2, 5.0, 2.6, 2.2, 3.4, 0.06, ['#d6ecfa', '#3498db', '#2775ad']) !!}
        {!! $windows(5.2, 5.0, 2.6, 2.2, 3.4, 0.06, '#bfe3f8', '#8fc9ee') !!}
        {!! $box(5.6, 5.4, 1.0, 0.8, 0.5, 3.46, ['#ffde00', '#e6c700', '#d4b700']) !!}
        {!! $box(8.4, 5.8, 2.0, 1.8, 2.2, 0.06, ['#e6f2fb', '#5dade2', '#3e95cf']) !!}
        {!! $windows(8.4, 5.8, 2.0, 1.8, 2.2, 0.06, '#d6ecfa', '#b3daf3') !!}

        {{-- Wind turbines --}}
        {!! $turbine(13.0, 6.3, 92, 0) !!}
        {!! $turbine(13.1, 8.2, 80, 1.3) !!}

        {{-- Trees along the river --}}
        @foreach ([[4.0, 9.1, 0.9], [6.2, 9.4, 1], [7.4, 9.6, 0.8], [10.6, 10.0, 1], [2.6, 9.0, 0.8]] as [$tx, $ty, $ts])
            {!! $tree($tx, $ty, $ts) !!}
        @endforeach

        {{-- Solar farm on the near bank --}}
        @foreach ([0, 1, 2] as $row)
            @foreach ([0, 1, 2, 3] as $col)
                {!! $box(6.0 + $col * 1.1, 12.4 + $row * 0.55, 0.9, 0.35, 0.12, 0.25, ['#2f6fb0', '#22548a', '#1d4878']) !!}
            @endforeach
        @endforeach

        @foreach ([[11.2, 12.9, 1], [12.6, 13.2, 0.9], [1.6, 12.6, 0.9], [2.8, 13.3, 1]] as [$tx, $ty, $ts])
            {!! $tree($tx, $ty, $ts) !!}
        @endforeach
    </g>
</svg>
