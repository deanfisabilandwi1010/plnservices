@props(['lead', 'title' => null, 'node' => true])
{{-- Big statement on the left, smaller title on the right; the node sits on the page's guide line. --}}
<div {{ $attributes->merge(['class' => 'relative grid gap-5 lg:grid-cols-2 lg:gap-x-24']) }}>
    @if ($node)
        <span class="guide-node top-5 hidden lg:block" data-guide-node></span>
    @endif
    <p class="split-lead" data-split>{!! $lead !!}</p>
    @if ($title)
        <h2 class="split-title lg:pt-3" data-reveal="fade" data-delay="0.15">{{ $title }}</h2>
    @elseif ($slot->isNotEmpty())
        <div>{{ $slot }}</div>
    @endif
</div>
