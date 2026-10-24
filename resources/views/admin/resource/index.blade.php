<x-layouts.admin :title="$config['label']">
    @php $readonly = $config['readonly'] ?? false; @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm text-muted">{{ isset($config['scope']) ? 'Master Data' : 'Konten' }} /</p>
            <h1 class="flex items-center gap-3 text-2xl font-bold"><x-glyph :name="$config['icon']" class="h-6 w-6 text-primary" />{{ $config['label'] }}</h1>
        </div>
        @unless ($readonly)
            <a href="{{ route('admin.resource.create', $module) }}" class="btn btn-primary"><x-glyph name="plus" class="h-4 w-4" /> Tambah {{ $config['label'] }}</a>
        @endunless
    </div>

    <div class="card overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line p-5">
            <form method="get" class="relative w-full max-w-sm">
                <x-glyph name="search" class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted" />
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari {{ strtolower($config['label']) }}…" class="input !pl-10">
            </form>
            <p class="text-sm text-muted">{{ $items->total() }} data</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-canvas">
                    <tr>
                        @foreach ($config['columns'] as $column)
                            <th class="th">{{ $column['label'] }}</th>
                        @endforeach
                        <th class="th text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($items as $item)
                        <tr class="transition hover:bg-canvas/50 {{ ($item->is_read ?? true) ? '' : 'bg-primary-50/40 font-semibold' }}">
                            @foreach ($config['columns'] as $key => $column)
                                @php $value = $item->{$key}; @endphp
                                <td class="td">
                                    @switch($column['type'])
                                        @case('image')
                                            @if ($url = media_url($value))
                                                <img src="{{ $url }}" alt="" class="h-11 w-16 rounded-lg object-cover">
                                            @else
                                                <span class="flex h-11 w-16 items-center justify-center rounded-lg bg-canvas text-muted"><x-glyph name="image" class="h-4 w-4" /></span>
                                            @endif
                                            @break
                                        @case('icon')
                                            <span class="flex h-10 w-10 items-center justify-center rounded-md bg-primary-50 text-primary"><x-glyph :name="$value" class="h-5 w-5" /></span>
                                            @break
                                        @case('badge')
                                            <span class="badge bg-primary-50 text-primary">{{ $value }}</span>
                                            @break
                                        @case('status')
                                            @php
                                                $green = ['published', 'Dibuka', 'Selesai'];
                                                $amber = ['draft', 'Evaluasi', 'Pengumuman Pemenang'];
                                            @endphp
                                            <span class="badge {{ in_array($value, $green) ? 'bg-emerald-50 text-emerald-700' : (in_array($value, $amber) ? 'bg-amber-50 text-amber-700' : 'bg-canvas text-muted') }}">{{ $value }}</span>
                                            @break
                                        @case('date')
                                            <span class="whitespace-nowrap text-muted">{{ $value?->translatedFormat('d M Y') ?? '—' }}</span>
                                            @break
                                        @case('bool')
                                            @if ($value)
                                                <x-glyph name="circle-check" class="h-5 w-5 text-emerald-500" />
                                            @else
                                                <x-glyph name="circle" class="h-5 w-5 text-muted/40" />
                                            @endif
                                            @break
                                        @case('file')
                                            @if ($url = media_url($value))
                                                <a href="{{ $url }}" target="_blank" class="inline-flex items-center gap-1.5 text-primary hover:underline"><x-glyph name="file-text" class="h-4 w-4" /> Lihat</a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                            @break
                                        @default
                                            <span class="{{ $loop->first || $key === 'title' || $key === 'name' ? 'font-semibold text-ink' : 'text-ink/80' }}">{{ \Illuminate\Support\Str::limit((string) $value, 60) }}</span>
                                    @endswitch
                                </td>
                            @endforeach
                            <td class="td">
                                <div class="flex justify-end gap-1">
                                    @if ($readonly)
                                        <a href="{{ route('admin.resource.show', [$module, $item->id]) }}" class="btn btn-ghost !p-2" title="Lihat"><x-glyph name="eye" class="h-4 w-4" /></a>
                                    @else
                                        <a href="{{ route('admin.resource.edit', [$module, $item->id]) }}" class="btn btn-ghost !p-2 hover:!text-primary" title="Edit"><x-glyph name="pencil" class="h-4 w-4" /></a>
                                    @endif
                                    <form action="{{ route('admin.resource.destroy', [$module, $item->id]) }}" method="post" data-confirm="Hapus data ini? Tindakan ini tidak bisa dibatalkan.">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-ghost !p-2 hover:!bg-rose-50 hover:!text-rose-600" title="Hapus"><x-glyph name="trash-2" class="h-4 w-4" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($config['columns']) + 1 }}" class="px-5 py-16 text-center text-muted">
                                <x-glyph :name="$config['icon']" class="mx-auto mb-3 h-10 w-10 text-muted/40" />
                                Belum ada data{{ request('q') ? ' yang cocok' : '' }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($items->hasPages())
            <div class="border-t border-line p-5 text-ink">{{ $items->links() }}</div>
        @endif
    </div>
</x-layouts.admin>
