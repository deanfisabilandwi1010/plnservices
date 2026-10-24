<x-layouts.site title="Pengadaan">
    <x-page-hero eyebrow="Pengadaan" title="Informasi pengadaan barang dan jasa" subtitle="PLN Services menyelenggarakan pengadaan secara terbuka, adil, dan akuntabel. Mari tumbuh bersama sebagai mitra." :crumbs="['Informasi' => route('news'), 'Pengadaan' => null]" />

    @php
        $tones = ['Dibuka' => 'bg-emerald-50 text-emerald-700', 'Evaluasi' => 'bg-amber-50 text-amber-700', 'Pengumuman Pemenang' => 'bg-cyan-50 text-cyan', 'Selesai' => 'bg-mist text-subtle', 'Dibatalkan' => 'bg-red-50 text-spark'];
    @endphp

    <section class="bg-white py-16">
        <div class="container-x">
            <div class="mb-8 flex flex-wrap gap-2" data-reveal="fade">
                <a href="{{ route('procurement') }}" class="chip" @if (! $activeStatus) aria-current="page" @endif>Semua</a>
                @foreach ($statuses as $status)
                    <a href="{{ route('procurement', ['status' => $status]) }}" class="chip" @if ($activeStatus === $status) aria-current="page" @endif>{{ $status }}</a>
                @endforeach
            </div>

            <div class="overflow-x-auto rounded-lg border border-line" data-reveal>
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="bg-mist text-xs tracking-[0.08em] text-subtle uppercase">
                        <tr>
                            <th class="px-5 py-3.5 font-semibold">Paket Pengadaan</th>
                            <th class="px-5 py-3.5 font-semibold">Metode</th>
                            <th class="px-5 py-3.5 font-semibold">Batas Akhir</th>
                            <th class="px-5 py-3.5 font-semibold">Status</th>
                            <th class="px-5 py-3.5"><span class="sr-only">Dokumen</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($procurements as $item)
                            <tr class="transition hover:bg-mist/60">
                                <td class="px-5 py-4">
                                    <p class="font-mono text-xs text-subtle">{{ $item->number }}</p>
                                    <p class="mt-0.5 font-semibold text-ink">{{ $item->title }}</p>
                                </td>
                                <td class="px-5 py-4">{{ $item->method }}</td>
                                <td class="px-5 py-4 whitespace-nowrap">{{ $item->end_date?->translatedFormat('d M Y') ?? '—' }}</td>
                                <td class="px-5 py-4"><span class="rounded px-2 py-1 text-xs font-semibold whitespace-nowrap {{ $tones[$item->status] ?? $tones['Selesai'] }}">{{ $item->status }}</span></td>
                                <td class="px-5 py-4 text-right">
                                    @if ($doc = media_url($item->document))
                                        <a href="{{ $doc }}" target="_blank" rel="noopener" class="link-arrow"><x-glyph name="download" class="h-4 w-4" /> Dokumen</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-14 text-center text-subtle">Belum ada data pengadaan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-12">{{ $procurements->links() }}</div>
        </div>
    </section>
</x-layouts.site>
