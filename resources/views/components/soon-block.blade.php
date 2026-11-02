@props(['title'])
{{-- Soft launch: stands in for a closed section. Empty placeholder cards behind two crossed
     hazard tapes; the real content is never rendered. Behaviour: site/soon-block.js --}}
<div {{ $attributes->merge(['class' => 'soon-x']) }} role="img" aria-label="{{ $title }}: Coming Soon" data-soon-x data-cursor-label="Coming Soon">
    <div class="soon-x-ghost" data-soon-ghost aria-hidden="true">
        @foreach (range(1, 3) as $_)
            <div class="soon-x-card"><span></span><i></i><i></i></div>
        @endforeach
    </div>

    @foreach (['', 'soon-x-tape-back'] as $variant)
        <div class="soon-x-tape {{ $variant }}" data-soon-tape aria-hidden="true">
            <div class="soon-x-band" data-soon-band>
                <div class="soon-x-text" data-soon-text>
                    @foreach (range(1, 24) as $_)
                        <span>Coming Soon</span>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
