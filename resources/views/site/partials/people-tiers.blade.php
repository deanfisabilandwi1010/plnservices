{{--
    Board in two tiers: the first $lead people (by "Urutan") centred on top — e.g. Komisaris Utama,
    or Direktur Utama + Wakil — and everyone else in a centred row below.
--}}
@php
    $top = $people->take($lead);
    $rest = $people->slice($lead)->values();
@endphp

<div class="mt-12">
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
