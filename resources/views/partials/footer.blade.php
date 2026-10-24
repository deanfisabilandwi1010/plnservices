@php
    $socials = collect(['instagram' => 'instagram', 'facebook' => 'facebook', 'youtube' => 'youtube', 'linkedin' => 'linkedin'])
        ->map(fn ($icon, $key) => ['icon' => $icon, 'url' => setting($key)])
        ->filter(fn ($s) => filled($s['url']));
@endphp

<footer class="border-t border-line bg-white">
    <div class="container-x grid gap-12 py-16 md:grid-cols-2 lg:grid-cols-12">
        <div class="lg:col-span-5">
            <img src="{{ asset('images/logo.png') }}" alt="PLN Services" class="h-12 w-auto">
            <p class="mt-5 max-w-sm text-sm leading-relaxed">{{ setting('footer_note') }}</p>
            @if ($socials->isNotEmpty())
                <div class="mt-6 flex gap-2.5">
                    @foreach ($socials as $name => $social)
                        <a href="{{ $social['url'] }}" target="_blank" rel="noopener" class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-brand text-brand transition hover:bg-brand hover:text-white" aria-label="{{ ucfirst($name) }}">
                            <x-glyph :name="$social['icon']" class="h-4 w-4" />
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="lg:col-span-3">
            <h3 class="mb-5 text-base font-bold">Tautan</h3>
            <ul class="grid grid-cols-2 gap-3 text-sm lg:grid-cols-1">
                @foreach (['Tentang Kami' => route('about'), 'Layanan' => route('services'), 'Tata Kelola' => route('governance'), 'Pengadaan' => route('procurement'), 'Berita' => route('news'), 'Kontak Kami' => route('contact')] as $label => $url)
                    <li><a href="{{ $url }}" class="inline-flex items-center gap-1.5 transition hover:text-brand"><x-glyph name="chevron-right" class="h-3.5 w-3.5 text-brand" />{{ $label }}</a></li>
                @endforeach
            </ul>
        </div>

        <div class="lg:col-span-4">
            <h3 class="mb-5 text-base font-bold">Kantor Pusat</h3>
            <ul class="space-y-4 text-sm">
                <li class="flex gap-3"><x-glyph name="map-pin" class="h-5 w-5 shrink-0 text-brand" />{{ setting('address') }}</li>
                @if (setting('email'))
                    <li class="flex gap-3"><x-glyph name="mail" class="h-5 w-5 shrink-0 text-brand" /><a href="mailto:{{ setting('email') }}" class="hover:text-brand">{{ setting('email') }}</a></li>
                @endif
                @if (setting('phone'))
                    <li class="flex gap-3"><x-glyph name="phone" class="h-5 w-5 shrink-0 text-brand" />Telp {{ setting('phone') }}</li>
                @endif
            </ul>
        </div>
    </div>

    <div class="border-t border-line">
        <div class="container-x py-6 text-center text-sm">
            © Copyright {{ now()->year }} <span class="font-semibold text-ink">{{ setting('company_name', 'PT PLN Services') }}</span>. All Rights Reserved
        </div>
    </div>
</footer>
