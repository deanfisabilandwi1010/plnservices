<x-layouts.admin :title="$config['label']">
    <div>
        <p class="text-sm text-muted">Setup Konten /</p>
        <h1 class="flex items-center gap-3 text-2xl font-bold"><x-glyph :name="$config['icon']" class="h-6 w-6 text-primary" />{{ $config['label'] }}</h1>
    </div>

    @if ($section === 'analytics')
        <div class="grid gap-6 md:grid-cols-3">
            <div class="card p-6">
                <p class="text-sm text-muted">Status Tracking</p>
                @if (filled($values['ga_measurement_id'] ?? null))
                    <p class="mt-2 flex items-center gap-2 text-lg font-semibold text-emerald-600"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Aktif · {{ $values['ga_measurement_id'] }}</p>
                @else
                    <p class="mt-2 flex items-center gap-2 text-lg font-semibold text-amber-600"><span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span> Belum dikonfigurasi</p>
                @endif
            </div>
            <div class="card p-6 md:col-span-2">
                <p class="text-sm text-muted">Cara mengaktifkan</p>
                <p class="mt-2 text-sm leading-relaxed">Buat properti GA4 di <a href="https://analytics.google.com" target="_blank" class="text-primary hover:underline">analytics.google.com</a>, salin <b>Measurement ID</b> (format <code>G-XXXXXXX</code>), lalu tempel di bawah. Tag akan otomatis terpasang di semua halaman publik.</p>
                @if (filled($values['ga_dashboard_url'] ?? null))
                    <a href="{{ $values['ga_dashboard_url'] }}" target="_blank" class="btn btn-light mt-4 !py-2 text-xs"><x-glyph name="external-link" class="h-4 w-4" /> Buka Laporan</a>
                @endif
            </div>
        </div>
    @endif

    <form action="{{ route('admin.settings.update', $section) }}" method="post" enctype="multipart/form-data" class="card p-6 md:p-8">
        @csrf @method('PUT')
        <div class="grid gap-6 md:grid-cols-2">
            @foreach ($config['fields'] as $name => $field)
                @include('admin.partials.field', ['name' => $name, 'field' => $field + ['rows' => 4], 'value' => old($name, $values[$name] ?? null)])
            @endforeach
        </div>
        <div class="mt-8 flex justify-end gap-3 border-t border-line pt-6">
            <a href="{{ route('home') }}" target="_blank" class="btn btn-ghost"><x-glyph name="eye" class="h-4 w-4" /> Pratinjau Web</a>
            <button class="btn btn-primary"><x-glyph name="save" class="h-4 w-4" /> Simpan Perubahan</button>
        </div>
    </form>
</x-layouts.admin>
