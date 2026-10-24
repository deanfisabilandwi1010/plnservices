<x-layouts.site title="Tata Kelola">
    <x-page-hero eyebrow="Tata Kelola" title="Transparan, akuntabel, dan berkelanjutan" subtitle="Dokumen tata kelola perusahaan: Rapat Umum Pemegang Saham, Anggaran Dasar, dan kebijakan Environmental, Social & Governance." :crumbs="['Tentang Kami' => route('about'), 'Tata Kelola' => null]" />

    <section class="bg-white py-20">
        <div class="container-x grid gap-10 lg:grid-cols-12">
            <aside class="lg:col-span-3">
                <nav class="space-y-1 lg:sticky lg:top-40" aria-label="Kategori dokumen">
                    @foreach ($groups as $key => $group)
                        <a href="#{{ $key }}" class="flex items-center justify-between rounded-md px-4 py-3 text-sm font-semibold text-ink transition hover:bg-mist hover:text-brand">
                            <span class="flex items-center gap-3"><x-glyph :name="$group['icon']" class="h-4 w-4 text-brand" />{{ $group['label'] }}</span>
                            <span class="text-xs text-subtle">{{ $group['items']->count() }}</span>
                        </a>
                    @endforeach
                </nav>
            </aside>

            <div class="space-y-14 lg:col-span-9">
                @foreach ($groups as $key => $group)
                    <div id="{{ $key }}" class="scroll-mt-40">
                        <h2 class="mb-5 text-2xl font-bold" data-reveal="fade">{{ $group['label'] }}</h2>
                        <div class="divide-y divide-line rounded-lg border border-line" data-reveal="stagger">
                            @forelse ($group['items'] as $doc)
                                <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center">
                                    <span class="icon-box"><x-glyph name="file-text" class="h-5 w-5" /></span>
                                    <div class="flex-1">
                                        <h3 class="font-semibold">{{ $doc->title }}</h3>
                                        <p class="mt-0.5 text-sm">{{ $doc->description }}</p>
                                    </div>
                                    <span class="text-sm font-semibold text-subtle">{{ $doc->year }}</span>
                                    @if ($file = media_url($doc->file))
                                        <a href="{{ $file }}" target="_blank" rel="noopener" class="btn btn-outline !py-2"><x-glyph name="download" class="h-4 w-4" /> Unduh</a>
                                    @else
                                        <span class="text-xs text-subtle">Segera tersedia</span>
                                    @endif
                                </div>
                            @empty
                                <p class="p-8 text-center text-subtle">Belum ada dokumen.</p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.site>
