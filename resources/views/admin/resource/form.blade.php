<x-layouts.admin :title="($item->exists ? 'Edit ' : 'Tambah ').$config['label']">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <a href="{{ route('admin.resource.index', $module) }}" class="inline-flex items-center gap-1 text-sm text-muted hover:text-primary"><x-glyph name="arrow-left" class="h-4 w-4" /> {{ $config['label'] }}</a>
            <h1 class="text-2xl font-bold">{{ $item->exists ? 'Edit' : 'Tambah' }} {{ $config['label'] }}</h1>
        </div>
    </div>

    <form action="{{ $item->exists ? route('admin.resource.update', [$module, $item->id]) : route('admin.resource.store', $module) }}" method="post" enctype="multipart/form-data" class="card p-6 md:p-8">
        @csrf
        @if ($item->exists) @method('PUT') @endif

        @if ($errors->any())
            <div class="mb-6 rounded-md bg-rose-50 p-4 text-sm text-rose-700 ring-1 ring-rose-200">
                <p class="font-semibold">Periksa kembali isian berikut:</p>
                <ul class="mt-1 list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="grid gap-6 md:grid-cols-2">
            @foreach ($config['fields'] as $name => $field)
                @include('admin.partials.field', ['name' => $name, 'field' => $field, 'value' => old($name, $field['type'] === 'password' ? null : $item->{$name})])
            @endforeach
        </div>

        <div class="mt-8 flex justify-end gap-3 border-t border-line pt-6">
            <a href="{{ route('admin.resource.index', $module) }}" class="btn btn-ghost">Batal</a>
            <button class="btn btn-primary"><x-glyph name="save" class="h-4 w-4" /> Simpan</button>
        </div>
    </form>
</x-layouts.admin>
