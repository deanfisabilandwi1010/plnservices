<x-layouts.admin :title="$config['label']">
    <div>
        <a href="{{ route('admin.resource.index', $module) }}" class="inline-flex items-center gap-1 text-sm text-muted hover:text-primary"><x-glyph name="arrow-left" class="h-4 w-4" /> {{ $config['label'] }}</a>
        <h1 class="text-2xl font-bold">Detail {{ $config['label'] }}</h1>
    </div>

    <div class="card p-6 md:p-8">
        <dl class="grid gap-6 md:grid-cols-2">
            @foreach ($config['fields'] as $name => $field)
                <div class="{{ ($field['width'] ?? null) === 'full' ? 'md:col-span-2' : '' }}">
                    <dt class="text-xs font-semibold text-muted">{{ $field['label'] }}</dt>
                    <dd class="mt-1.5 {{ $field['type'] === 'textarea' ? 'rounded-md bg-canvas p-4 leading-relaxed whitespace-pre-line' : 'font-semibold' }}">
                        @if ($field['type'] === 'email' && $item->{$name})
                            <a href="mailto:{{ $item->{$name} }}" class="text-primary hover:underline">{{ $item->{$name} }}</a>
                        @else
                            {{ $item->{$name} ?: '—' }}
                        @endif
                    </dd>
                </div>
            @endforeach
            @if (method_exists($item, 'reference'))
                <div>
                    <dt class="text-xs font-semibold text-muted">Nomor Referensi</dt>
                    <dd class="mt-1.5 font-mono font-semibold text-primary">{{ $item->reference() }}</dd>
                </div>
            @endif
            <div>
                <dt class="text-xs font-semibold text-muted">Diterima</dt>
                <dd class="mt-1.5 font-semibold">{{ $item->created_at?->translatedFormat('l, d F Y H:i') }}</dd>
            </div>
        </dl>

        <div class="mt-8 flex justify-between gap-3 border-t border-line pt-6">
            <form action="{{ route('admin.resource.destroy', [$module, $item->id]) }}" method="post" data-confirm="Hapus data ini?">
                @csrf @method('DELETE')
                <button class="btn btn-danger"><x-glyph name="trash-2" class="h-4 w-4" /> Hapus</button>
            </form>
            @if ($item->email ?? null)
                <a href="mailto:{{ $item->email }}?subject={{ rawurlencode('Re: '.($item->subject ?? '').(method_exists($item, 'reference') ? ' ['.$item->reference().']' : '')) }}" class="btn btn-primary"><x-glyph name="reply" class="h-4 w-4" /> Balas via Email</a>
            @endif
        </div>
    </div>
</x-layouts.admin>
