<x-layouts.site title="Layanan">
    <x-page-hero eyebrow="Layanan" title="Layanan PLN Services" subtitle="Dari pembangkit, jaringan, hingga pelanggan — layanan terintegrasi PLN Services dirancang untuk keandalan, keselamatan, dan efisiensi." :crumbs="['Layanan' => null]" />

    <section class="bg-white py-20 md:py-24">
        <div class="container-x">
            <x-section-heading title="Layanan Kami" />
            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4" data-reveal="stagger">
                @foreach ($services as $service)
                    <a href="{{ route('services.show', $service) }}" class="card service-card group flex flex-col items-center px-6 py-10 text-center hover:-translate-y-1.5">
                        <span class="icon-box"><x-glyph :name="$service->icon" class="h-6 w-6" /></span>
                        <h3 class="mt-6 text-lg leading-snug font-bold transition-colors">{{ $service->title }}</h3>
                        <p class="mt-3 text-sm leading-relaxed transition-colors">{{ $service->summary }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="border-t border-line bg-mist py-20">
        <div class="container-x">
            <x-section-heading title="Metode Kerja" subtitle="Proses yang terukur dari awal hingga akhir." />
            <ol class="mt-12 grid gap-px overflow-hidden rounded-lg border border-line bg-line md:grid-cols-4" data-reveal="stagger">
                @foreach ([['search', 'Asesmen', 'Memahami kebutuhan, kondisi lapangan, dan target keandalan.'], ['pencil-ruler', 'Perencanaan', 'Merancang solusi teknis, jadwal, dan manajemen risiko K3.'], ['hard-hat', 'Pelaksanaan', 'Tenaga bersertifikat bekerja dengan standar mutu PLN Group.'], ['activity', 'Pemantauan', 'Evaluasi kinerja berkelanjutan berbasis data.']] as $i => [$icon, $title, $text])
                    <li class="bg-white p-7">
                        <div class="flex items-center justify-between">
                            <span class="icon-box"><x-glyph :name="$icon" class="h-5 w-5" /></span>
                            <span class="text-sm font-bold text-subtle tabular-nums">0{{ $i + 1 }}</span>
                        </div>
                        <h3 class="mt-6 font-semibold">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>
</x-layouts.site>
