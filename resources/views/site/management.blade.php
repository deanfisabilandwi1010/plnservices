<x-layouts.site title="Manajemen">
    <x-page-hero eyebrow="Profil" title="Manajemen PLN Services" subtitle="Dewan Direksi dan Dewan Komisaris yang memimpin dan mengawasi jalannya perusahaan." :crumbs="['Profil' => null, 'Manajemen' => null]" />

    @foreach (['direksi' => ['Profil Direksi', $direksi], 'komisaris' => ['Profil Komisaris', $komisaris]] as $anchor => [$heading, $people])
        <section id="{{ $anchor }}" class="scroll-mt-20 {{ $loop->first ? 'bg-white' : 'bg-mist' }} py-20 md:py-24">
            <div class="container-x">
                <x-section-heading :title="$heading" />
                @php $cols = [1 => 'lg:grid-cols-1', 2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3'][$people->count()] ?? 'lg:grid-cols-4'; @endphp
                <div class="mt-12 grid gap-6 sm:grid-cols-2 {{ $cols }}" data-reveal="stagger">
                    @foreach ($people as $person)
                        @include('site.partials.person-card', ['person' => $person])
                    @endforeach
                </div>
            </div>
        </section>
    @endforeach
</x-layouts.site>
