<x-layouts.site title="Tata Kelola">
    <x-page-hero eyebrow="Tata Kelola" title="Transparan, akuntabel, dan berkelanjutan" subtitle="Dokumen tata kelola perusahaan: Rapat Umum Pemegang Saham, Anggaran Dasar, dan kebijakan Environmental, Social & Governance." :crumbs="['Tentang Kami' => route('about'), 'Tata Kelola' => null]" />

    <x-section-nav title="Tata Kelola" :items="collect($groups)->map(fn ($g) => $g['label'].' ('.$g['items']->count().')')->all()" cta="Ajukan Pertanyaan" :cta-url="route('contact', ['topik' => 'Informasi Umum'])" />

    <section class="bg-white py-20">
        <div class="container-x space-y-20">
            @foreach ($groups as $key => $group)
                <div id="{{ $key }}" class="scroll-mt-[136px] grid gap-8 lg:grid-cols-12">
                    <div class="lg:col-span-4">
                        <span class="icon-box"><x-glyph :name="$group['icon']" class="h-6 w-6" /></span>
                        <h2 class="mt-5 text-3xl font-bold" data-reveal="fade">{{ $group['label'] }}</h2>
                        <p class="mt-2 text-sm text-subtle">{{ $group['items']->count() }} dokumen</p>
                    </div>
                    <div class="border-t border-line lg:col-span-8" data-reveal="stagger">
                        @forelse ($group['items'] as $doc)
                            <div class="group flex flex-col gap-4 border-b border-line py-6 transition-[padding] duration-500 hover:pl-3 sm:flex-row sm:items-center">
                                <span class="font-display text-sm font-bold text-subtle tabular-nums sm:w-14">{{ $doc->year }}</span>
                                <div class="flex-1">
                                    <h3 class="font-semibold transition-colors group-hover:text-brand">{{ $doc->title }}</h3>
                                    <p class="mt-0.5 text-sm">{{ $doc->description }}</p>
                                </div>
                                @if ($file = media_url($doc->file))
                                    <a href="{{ $file }}" target="_blank" rel="noopener" class="btn btn-outline !py-2"><x-glyph name="download" class="h-4 w-4" /> Unduh</a>
                                @else
                                    <span class="text-xs text-subtle">Segera tersedia</span>
                                @endif
                            </div>
                        @empty
                            <p class="py-8 text-subtle">Belum ada dokumen.</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</x-layouts.site>
