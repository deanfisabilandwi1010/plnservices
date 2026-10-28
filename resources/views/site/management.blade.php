<x-layouts.site title="Manajemen">
    <x-page-hero eyebrow="Profil" title="Manajemen PLN Services" subtitle="Dewan Direksi dan Dewan Komisaris yang memimpin dan mengawasi jalannya perusahaan." :crumbs="['Profil' => null, 'Manajemen' => null]" />

    <x-section-nav title="Manajemen" :items="['direksi' => 'Dewan Direksi', 'komisaris' => 'Dewan Komisaris']" />

    @foreach (['direksi' => ['Profil Direksi', $direksi], 'komisaris' => ['Profil Komisaris', $komisaris]] as $anchor => [$heading, $people])
        <section id="{{ $anchor }}" class="scroll-mt-[136px] {{ $loop->first ? 'bg-white' : 'bg-mist' }} py-20 md:py-24">
            <div class="container-x">
                <x-section-heading :title="$heading" />
                {{-- Komisaris Utama alone on top; Direktur Utama and Wakil Direktur Utama side by side --}}
                @include('site.partials.people-tiers', ['people' => $people, 'lead' => $anchor === 'direksi' ? 2 : 1])
            </div>
        </section>
    @endforeach
</x-layouts.site>
