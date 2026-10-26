@props(['variant' => 'network', 'mode' => 'scroll'])
{{--
    Line drawing of dots being connected, drawn piece by piece by resources/js/site/line-art.js —
    scrubbed with the scroll (mode="scroll") or played once on load (mode="load").
    Items are listed in drawing order: ['dot', x, y, r, accent?] · ['ring', x, y, r] · ['line', x1, y1, x2, y2] · ['path', d, flow?]
--}}
@php
    $items = [];

    if ($variant === 'network') {
        $n = ['A' => [60, 250], 'B' => [130, 170], 'C' => [205, 235], 'D' => [165, 85], 'E' => [265, 145], 'F' => [305, 255],
            'G' => [345, 70], 'H' => [395, 175], 'I' => [445, 105], 'J' => [425, 285], 'K' => [235, 320], 'L' => [95, 320]];
        $dot = fn ($k, $r = 4.5, $accent = false) => ['dot', ...$n[$k], $r, $accent];
        $line = fn ($a, $b) => ['line', ...$n[$a], ...$n[$b]];
        $items = [
            $dot('A', 6), $line('A', 'B'), $line('A', 'L'), $dot('B'), $dot('L'),
            $line('B', 'D'), $line('B', 'C'), $line('L', 'K'), $dot('D'), $dot('C', 6), $dot('K'),
            $line('D', 'E'), $line('C', 'E'), $line('C', 'F'), $line('K', 'F'), $dot('E', 6), $dot('F'),
            $line('E', 'G'), $line('E', 'H'), $line('F', 'J'), $dot('G'), $dot('H'), $dot('J'),
            $line('G', 'I'), $line('H', 'I'), $line('H', 'J'), $dot('I', 7, true),
        ];
    } elseif ($variant === 'together') {
        $items = [['dot', 240, 180, 9, false], ['ring', 240, 180, 46]];
        foreach (range(0, 7) as $i) {
            $a = deg2rad($i * 45 + 22.5);
            [$c, $s] = [cos($a), sin($a)];
            $items[] = ['line', 240 + 52 * $c, 180 + 52 * $s, 240 + 175 * $c, 180 + 135 * $s];
            $items[] = ['ring', 240 + 190 * $c, 180 + 148 * $s, 12];
            $items[] = ['dot', 240 + 190 * $c, 180 + 148 * $s, 4, $i === 6];
        }
    } elseif ($variant === 'pulse') {
        $d = 'M14 200 H150 L170 200 L188 130 L210 270 L232 95 L252 235 L266 200 H452';
        $items = [['dot', 14, 200, 6, false], ['path', $d, false], ['ring', 466, 200, 14], ['dot', 466, 200, 6, true], ['path', $d, true]];
    } elseif ($variant === 'connect') {
        $xs = [80, 187, 293, 400];
        $points = [];
        foreach ([90, 180, 270] as $row => $y) {
            foreach ($row % 2 ? array_reverse($xs) : $xs as $x) {
                $points[] = [$x, $y];
            }
        }
        foreach ($points as $i => [$x, $y]) {
            $last = $i === count($points) - 1;
            $items[] = ['dot', $x, $y, $last ? 7 : 5, $last];
            if (! $last) {
                $items[] = ['line', $x, $y, ...$points[$i + 1]];
            }
        }
    }
@endphp
<svg viewBox="0 0 480 360" fill="none" {{ $attributes->merge(['class' => 'line-art text-brand']) }} data-line-art="{{ $mode }}" aria-hidden="true">
    @foreach ($items as $item)
        @switch($item[0])
            @case('dot')
                <circle data-la cx="{{ round($item[1], 1) }}" cy="{{ round($item[2], 1) }}" r="{{ $item[3] }}" fill="{{ $item[4] ? '#ffde00' : 'currentColor' }}" @if ($item[4]) stroke="currentColor" stroke-width="1.5" class="la-accent" @endif />
                @break
            @case('ring')
                <circle data-la cx="{{ round($item[1], 1) }}" cy="{{ round($item[2], 1) }}" r="{{ $item[3] }}" stroke="currentColor" stroke-width="1.5" />
                @break
            @case('line')
                <path data-la d="M{{ round($item[1], 1) }} {{ round($item[2], 1) }} L{{ round($item[3], 1) }} {{ round($item[4], 1) }}" stroke="currentColor" stroke-opacity=".55" stroke-width="1.5" />
                @break
            @case('path')
                @if ($item[2])
                    <path data-la-flow d="{{ $item[1] }}" stroke="#ffde00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="pylon-wire" />
                @else
                    <path data-la d="{{ $item[1] }}" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                @endif
                @break
        @endswitch
    @endforeach
</svg>
