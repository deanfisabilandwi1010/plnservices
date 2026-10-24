<x-layouts.site title="Hubungi Kami">
    <x-page-hero eyebrow="Hubungi Kami" title="Tinggalkan pesan untuk PLN Services" subtitle="Sampaikan pertanyaan, peluang kerja sama, atau masukan Anda. Setiap pesan diberi nomor referensi dan diteruskan ke unit yang menanganinya." :crumbs="['Hubungi Kami' => null]" />

    <section class="bg-white py-16 md:py-20">
        <div class="container-x grid gap-10 lg:grid-cols-12">
            <div class="lg:col-span-8">
                @if ($sent = session('sent'))
                    {{-- Confirmation --}}
                    <div class="rounded-lg border border-line" data-reveal>
                        <div class="border-b border-line p-8 md:p-10">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600"><x-glyph name="circle-check" class="h-6 w-6" /></span>
                            <h2 class="mt-6 text-2xl font-bold">Terima kasih, {{ $sent['name'] }}. Pesan Anda telah kami terima.</h2>
                            <p class="mt-2">Konfirmasi dan tanggapan akan dikirim ke <span class="font-semibold text-ink">{{ $sent['email'] }}</span>.</p>

                            <div class="mt-6 inline-flex flex-wrap items-center gap-x-6 gap-y-2 rounded-md bg-mist px-5 py-4">
                                <div>
                                    <p class="text-xs text-subtle">Nomor referensi</p>
                                    <p class="font-mono text-lg font-bold text-brand">{{ $sent['reference'] }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-subtle">Diteruskan ke</p>
                                    <p class="font-semibold text-ink">{{ $sent['unit'] }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-8 md:p-10">
                            <h3 class="font-semibold">Langkah selanjutnya</h3>
                            <ol class="mt-4 space-y-3 text-[15px]">
                                <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-brand-50 text-xs font-bold text-brand">1</span>Pesan diverifikasi dan diteruskan ke unit terkait.</li>
                                <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-brand-50 text-xs font-bold text-brand">2</span>Tim kami menanggapi melalui email paling lambat 2 hari kerja.</li>
                                <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-brand-50 text-xs font-bold text-brand">3</span>Sebutkan nomor referensi bila menghubungi kami kembali.</li>
                            </ol>
                            <div class="mt-8 flex flex-wrap gap-3">
                                <a href="{{ route('contact') }}" class="btn btn-outline">Kirim pesan lain</a>
                                <a href="{{ route('home') }}" class="btn btn-primary">Kembali ke beranda</a>
                            </div>
                        </div>
                    </div>
                @else
                    <form action="{{ route('contact.send') }}" method="post" class="rounded-lg border border-line" novalidate data-reveal>
                        @csrf

                        @if ($errors->any())
                            <div class="flex gap-3 border-b border-red-100 bg-red-50 px-8 py-4 text-sm text-spark" role="alert">
                                <x-glyph name="circle-alert" class="mt-0.5 h-4 w-4 shrink-0" />
                                Mohon periksa kembali isian yang ditandai.
                            </div>
                        @endif

                        <fieldset class="border-b border-line p-8 md:p-10">
                            <legend class="sr-only">Data pengirim</legend>
                            <p class="mb-6 flex items-center gap-3 font-semibold text-ink"><span class="flex h-7 w-7 items-center justify-center rounded-md bg-brand text-xs font-bold text-white">1</span>Data pengirim</p>
                            <div class="grid gap-5 md:grid-cols-2">
                                @foreach (['name' => ['Nama lengkap', 'text', true, 'name'], 'company' => ['Instansi / perusahaan', 'text', false, 'organization'], 'email' => ['Email', 'email', true, 'email'], 'phone' => ['Nomor telepon', 'tel', false, 'tel']] as $field => [$label, $type, $required, $autocomplete])
                                    <div>
                                        <label for="c-{{ $field }}" class="field-label">{{ $label }} @if ($required)<span class="text-spark">*</span>@else<span class="font-normal text-subtle">(opsional)</span>@endif</label>
                                        <input id="c-{{ $field }}" type="{{ $type }}" name="{{ $field }}" value="{{ old($field) }}" autocomplete="{{ $autocomplete }}" class="field @error($field) !border-spark @enderror" @required($required) @error($field) aria-invalid="true" aria-describedby="e-{{ $field }}" @enderror>
                                        @error($field)<p id="e-{{ $field }}" class="mt-1.5 text-sm text-spark">{{ $message }}</p>@enderror
                                    </div>
                                @endforeach
                            </div>
                        </fieldset>

                        <fieldset class="p-8 md:p-10">
                            <legend class="sr-only">Isi pesan</legend>
                            <p class="mb-6 flex items-center gap-3 font-semibold text-ink"><span class="flex h-7 w-7 items-center justify-center rounded-md bg-brand text-xs font-bold text-white">2</span>Isi pesan</p>

                            <p class="field-label">Kategori pesan <span class="text-spark">*</span></p>
                            @php $selected = old('category', $topic ?? 'Informasi Umum'); @endphp
                            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($categories as $category => $unit)
                                    <label class="flex cursor-pointer items-start gap-3 rounded-md border border-line p-3.5 transition hover:border-brand/50 has-[:checked]:border-brand has-[:checked]:bg-brand-50">
                                        <input type="radio" name="category" value="{{ $category }}" class="mt-1 accent-[#3498db]" @checked($selected === $category)>
                                        <span>
                                            <span class="block text-sm font-semibold text-ink">{{ $category }}</span>
                                            <span class="block text-xs text-subtle">{{ $unit }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('category')<p class="mt-1.5 text-sm text-spark">{{ $message }}</p>@enderror

                            <div class="mt-6">
                                <label for="c-subject" class="field-label">Subjek <span class="text-spark">*</span></label>
                                <input id="c-subject" type="text" name="subject" value="{{ old('subject') }}" class="field @error('subject') !border-spark @enderror" required>
                                @error('subject')<p class="mt-1.5 text-sm text-spark">{{ $message }}</p>@enderror
                            </div>

                            <div class="mt-5">
                                <div class="flex items-baseline justify-between">
                                    <label for="c-message" class="field-label">Pesan <span class="text-spark">*</span></label>
                                    <span class="text-xs text-subtle"><span data-char-count="c-message">0</span>/5000</span>
                                </div>
                                <textarea id="c-message" name="message" rows="6" maxlength="5000" class="field resize-y @error('message') !border-spark @enderror" placeholder="Jelaskan kebutuhan atau pertanyaan Anda secara singkat dan jelas." required>{{ old('message') }}</textarea>
                                @error('message')<p class="mt-1.5 text-sm text-spark">{{ $message }}</p>@enderror
                            </div>

                            <label class="mt-6 flex items-start gap-3 text-sm">
                                <input type="checkbox" name="consent" value="1" class="mt-0.5 h-4 w-4 accent-[#3498db]" @checked(old('consent'))>
                                <span>Saya menyetujui data yang saya berikan digunakan oleh PLN Services untuk menindaklanjuti pesan ini. <span class="text-spark">*</span></span>
                            </label>
                            @error('consent')<p class="mt-1.5 text-sm text-spark">{{ $message }}</p>@enderror

                            <div class="mt-8 flex flex-col gap-4 border-t border-line pt-6 sm:flex-row sm:items-center sm:justify-between">
                                <p class="flex items-center gap-2 text-sm text-subtle"><x-glyph name="clock" class="h-4 w-4" />Tanggapan paling lambat 2 hari kerja.</p>
                                <button class="btn btn-primary px-7">Kirim Pesan <x-glyph name="send" class="h-4 w-4" /></button>
                            </div>
                        </fieldset>
                    </form>
                @endif
            </div>

            <aside class="space-y-5 lg:col-span-4">
                <div class="rounded-lg bg-brand p-7 text-white" data-reveal>
                    <h2 class="text-lg font-semibold !text-white">Kontak langsung</h2>
                    <ul class="mt-5 space-y-4 text-sm">
                        <li class="flex gap-3"><x-glyph name="map-pin" class="mt-0.5 h-4 w-4 shrink-0 text-volt" /><span><span class="block text-white/60">Kantor pusat</span>{{ setting('address') }}</span></li>
                        @if (setting('phone'))
                            <li class="flex gap-3"><x-glyph name="phone" class="mt-0.5 h-4 w-4 shrink-0 text-volt" /><span><span class="block text-white/60">Telepon</span><a href="tel:{{ preg_replace('/[^0-9+]/', '', (string) setting('phone')) }}" class="hover:underline">{{ setting('phone') }}</a></span></li>
                        @endif
                        @if (setting('email'))
                            <li class="flex gap-3"><x-glyph name="mail" class="mt-0.5 h-4 w-4 shrink-0 text-volt" /><span><span class="block text-white/60">Email</span><a href="mailto:{{ setting('email') }}" class="hover:underline">{{ setting('email') }}</a></span></li>
                        @endif
                        @if (setting('call_center'))
                            <li class="flex gap-3"><x-glyph name="headset" class="mt-0.5 h-4 w-4 shrink-0 text-volt" /><span><span class="block text-white/60">Gangguan listrik 24 jam</span>Call Center {{ setting('call_center') }}</span></li>
                        @endif
                    </ul>
                </div>

                <div class="card p-7" data-reveal>
                    <h2 class="font-semibold">Jam layanan</h2>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between"><dt>Senin – Jumat</dt><dd class="font-semibold text-ink">08.00 – 16.00 WIB</dd></div>
                        <div class="flex justify-between"><dt>Sabtu, Minggu & libur</dt><dd class="font-semibold text-ink">Tutup</dd></div>
                    </dl>
                    <p class="mt-4 border-t border-line pt-4 text-xs leading-relaxed text-subtle">Untuk gangguan listrik di lokasi Anda, hubungi Call Center {{ setting('call_center', 'PLN 123') }} atau aplikasi PLN Mobile.</p>
                </div>

                @if ($offices->isNotEmpty())
                    <div class="card p-7" data-reveal>
                        <h2 class="font-semibold">Kantor region</h2>
                        <ul class="mt-4 space-y-3 text-sm">
                            @foreach ($offices as $office)
                                <li><span class="block font-semibold text-ink">{{ $office->name }}</span>{{ $office->address }}</li>
                            @endforeach
                        </ul>
                        <a href="{{ route('network') }}" class="link-arrow mt-5">Semua kantor <x-glyph name="arrow-right" class="h-4 w-4" /></a>
                    </div>
                @endif
            </aside>
        </div>
    </section>
</x-layouts.site>
