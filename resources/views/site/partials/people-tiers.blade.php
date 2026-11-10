{{--
    Board in two tiers: the first $lead people (by "Urutan") centred on top — e.g. Komisaris Utama,
    or Direktur Utama + Wakil — and everyone else in a centred row below.
--}}
@php
    $top = $people->take($lead);
    $rest = $people->slice($lead)->values();
    $board = $people->first()?->type === 'komisaris' ? 'Dewan Komisaris' : 'Dewan Direksi';
@endphp

{{-- Each card opens its profile; the dialog steps through this board (data-person-group). --}}
<div class="mt-12" data-person-group="{{ $board }}">
    <div class="flex flex-wrap justify-center gap-6" data-reveal="stagger">
        @foreach ($top as $person)
            <div class="w-[calc(50%-12px)] sm:w-60 xl:w-64">
                @include('site.partials.person-card', ['person' => $person])
            </div>
        @endforeach
    </div>

    @if ($rest->isNotEmpty())
        <div class="mt-6 flex flex-wrap justify-center gap-6" data-reveal="stagger">
            @foreach ($rest as $person)
                <div class="w-[calc(50%-12px)] sm:w-52 xl:w-[208px]">
                    @include('site.partials.person-card', ['person' => $person])
                </div>
            @endforeach
        </div>
    @endif
</div>

@once
    @push('scripts')
        @include('site.partials.person-modal')
    @endpush
@endonce
