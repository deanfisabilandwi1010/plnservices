<x-layouts.admin title="Dashboard">
    @php
        $max = max(1, max($chart['posts']), max($chart['feedbacks']));
    @endphp

    {{-- Page header --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Dashboard</h1>
            <p class="mt-1 text-sm text-muted">Selamat datang, {{ auth()->user()->name }} · {{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.resource.create', 'portfolios') }}" class="btn btn-light"><x-glyph name="plus" class="h-4 w-4" /> Portfolio</a>
            <a href="{{ route('admin.resource.create', 'posts') }}" class="btn btn-primary"><x-glyph name="plus" class="h-4 w-4" /> Buat Berita</a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            <a href="{{ route('admin.resource.index', $stat['module']) }}" class="card group p-5 transition-colors hover:border-primary/40">
                <div class="flex items-center justify-between">
                    <p class="text-[13px] text-muted">{{ $stat['label'] }}</p>
                    <x-glyph :name="$stat['icon']" class="h-[18px] w-[18px] text-muted group-hover:text-primary" />
                </div>
                <p class="mt-2 font-display text-[1.75rem] leading-none font-bold">{{ number_format($stat['value'], 0, ',', '.') }}</p>
                <p class="mt-2 text-xs text-muted">{{ $stat['note'] }}</p>
            </a>
        @endforeach
    </div>

    {{-- Activity chart --}}
    <section class="card p-5">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-bold">Aktivitas 6 bulan terakhir</h2>
                <p class="text-xs text-muted">Berita dipublikasi dan feedback masuk per bulan</p>
            </div>
            <div class="flex gap-4 text-xs text-muted">
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-primary"></span>Berita</span>
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-primary-100"></span>Feedback</span>
            </div>
        </div>
        <div class="mt-6 grid h-48 grid-cols-6 items-end gap-4 border-b border-line md:gap-8">
            @foreach ($chart['labels'] as $i => $label)
                <div class="flex h-full items-end justify-center gap-1">
                    @foreach ([['posts', 'bg-primary'], ['feedbacks', 'bg-primary-100']] as [$key, $color])
                        <div class="group relative w-full max-w-6 rounded-t-sm {{ $color }}" style="height: {{ max(2, $chart[$key][$i] / $max * 100) }}%">
                            <span class="absolute -top-6 left-1/2 -translate-x-1/2 rounded bg-ink px-1.5 py-0.5 text-[11px] text-white opacity-0 group-hover:opacity-100">{{ $chart[$key][$i] }}</span>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
        <div class="mt-2 grid grid-cols-6 gap-4 text-center text-xs text-muted md:gap-8">
            @foreach ($chart['labels'] as $label)<span>{{ $label }}</span>@endforeach
        </div>
    </section>

    {{-- Latest --}}
    <div class="grid gap-4 xl:grid-cols-2">
        <section class="card">
            <div class="flex items-center justify-between border-b border-line px-5 py-3.5">
                <h2 class="text-base font-bold">Berita terbaru</h2>
                <a href="{{ route('admin.resource.index', 'posts') }}" class="text-[13px] font-semibold text-primary hover:underline">Lihat semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead><tr class="border-b border-line"><th class="th !pl-5">Judul</th><th class="th">Tanggal</th><th class="th">Status</th></tr></thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($latestPosts as $post)
                            <tr class="hover:bg-canvas">
                                <td class="td !pl-5"><a href="{{ route('admin.resource.edit', ['posts', $post->id]) }}" class="hover:text-primary">{{ \Illuminate\Support\Str::limit($post->title, 46) }}</a></td>
                                <td class="td whitespace-nowrap text-muted">{{ $post->published_at?->translatedFormat('d M Y') ?? '—' }}</td>
                                <td class="td"><span class="badge {{ $post->status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-canvas text-muted' }}">{{ $post->status === 'published' ? 'Terbit' : 'Draft' }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card">
            <div class="flex items-center justify-between border-b border-line px-5 py-3.5">
                <h2 class="text-base font-bold">Feedback terbaru</h2>
                <a href="{{ route('admin.resource.index', 'feedbacks') }}" class="text-[13px] font-semibold text-primary hover:underline">Lihat semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead><tr class="border-b border-line"><th class="th !pl-5">Nama</th><th class="th">Subjek</th><th class="th">Tanggal</th></tr></thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($latestFeedbacks as $feedback)
                            <tr class="hover:bg-canvas">
                                <td class="td !pl-5 {{ $feedback->is_read ? '' : 'font-semibold' }}">
                                    @unless ($feedback->is_read)<span class="mr-1.5 inline-block h-1.5 w-1.5 rounded-full bg-primary align-middle"></span>@endunless{{ $feedback->name }}
                                </td>
                                <td class="td"><a href="{{ route('admin.resource.show', ['feedbacks', $feedback->id]) }}" class="hover:text-primary">{{ \Illuminate\Support\Str::limit($feedback->subject, 34) }}</a></td>
                                <td class="td whitespace-nowrap text-muted">{{ $feedback->created_at->translatedFormat('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-layouts.admin>
