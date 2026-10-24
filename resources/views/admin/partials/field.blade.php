{{-- One form control, driven by a field definition from config/cms.php --}}
@php
    $type = $field['type'];
    $id = 'f_'.$name;
    $full = ($field['width'] ?? null) === 'full' || in_array($type, ['editor'], true);
    $required = str_contains($field['rules'] ?? '', 'required');

    if ($value instanceof \Carbon\CarbonInterface) {
        $value = $type === 'datetime' ? $value->format('Y-m-d\TH:i') : $value->format('Y-m-d');
    }

    $options = $field['options'] ?? [];
    if ($options && array_is_list($options)) {
        $options = array_combine($options, $options);
    }

    $iconChoices = ['zap', 'factory', 'utility-pole', 'plug-zap', 'hard-hat', 'sun', 'wind', 'anchor', 'cpu', 'shield-check', 'cable', 'battery-charging', 'lightbulb', 'gauge', 'activity', 'map-pin', 'map', 'users', 'smile', 'building-2', 'briefcase', 'wrench', 'leaf', 'sprout', 'award', 'rocket', 'target', 'globe', 'network', 'radio-tower', 'truck', 'ship', 'handshake', 'headset', 'chart-line', 'trending-up', 'clock', 'calendar', 'star', 'heart-handshake'];
@endphp

<div class="{{ $full ? 'md:col-span-2' : '' }}">
    @if ($type !== 'toggle')
        <label for="{{ $id }}" class="label">{{ $field['label'] }} @if ($required)<span class="text-rose-500">*</span>@endif</label>
    @endif

    @switch($type)
        @case('textarea')
            <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $field['rows'] ?? 4 }}" class="input" @required($required)>{{ $value }}</textarea>
            @break

        @case('editor')
            <input type="hidden" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}">
            <div data-editor data-input="{{ $id }}"></div>
            @break

        @case('select')
            <select id="{{ $id }}" name="{{ $name }}" class="input" @required($required)>
                @unless ($required)<option value="">— Pilih —</option>@endunless
                @foreach ($options as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                @endforeach
            </select>
            @break

        @case('toggle')
            <label class="flex cursor-pointer items-center gap-3 rounded-md border border-line px-4 py-3">
                <input type="hidden" name="{{ $name }}" value="0">
                <input type="checkbox" name="{{ $name }}" value="1" class="peer sr-only" @checked($value)>
                <span class="relative h-6 w-11 rounded-full bg-line transition after:absolute after:top-0.5 after:left-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:bg-primary peer-checked:after:translate-x-5"></span>
                <span class="text-sm font-semibold">{{ $field['label'] }}</span>
            </label>
            @break

        @case('image')
        @case('file')
            @php $url = media_url($value); @endphp
            <div data-upload class="rounded-md border border-dashed border-line p-4 transition hover:border-primary">
                <div class="flex items-center gap-4">
                    @if ($type === 'image')
                        <div class="relative h-20 w-28 shrink-0 overflow-hidden rounded-lg bg-canvas">
                            <img data-upload-preview src="{{ $url }}" alt="" class="{{ $url ? '' : 'hidden' }} h-full w-full object-cover">
                            <span data-upload-empty class="{{ $url ? 'hidden' : '' }} absolute inset-0 flex items-center justify-center text-muted"><x-glyph name="image-plus" class="h-6 w-6" /></span>
                        </div>
                    @else
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary"><x-glyph name="file-up" class="h-6 w-6" /></span>
                    @endif
                    <div class="min-w-0 flex-1">
                        <label class="btn btn-light cursor-pointer !py-2 text-xs">
                            <x-glyph name="upload" class="h-4 w-4" /> Pilih {{ $type === 'image' ? 'Gambar' : 'File' }}
                            <input type="file" id="{{ $id }}" name="{{ $name }}" class="sr-only" @if ($type === 'image') accept="image/*" @endif>
                        </label>
                        <p class="mt-1.5 truncate text-xs text-muted" data-upload-name>
                            @if ($url)
                                <a href="{{ $url }}" target="_blank" class="text-primary hover:underline">{{ basename($value) }}</a>
                            @else
                                {{ $type === 'image' ? 'JPG/PNG/WebP, maks. 4 MB' : 'PDF/DOC/XLS, maks. 20 MB' }}
                            @endif
                        </p>
                        @if ($url)
                            <label class="mt-1 inline-flex items-center gap-1.5 text-xs text-rose-600"><input type="checkbox" name="{{ $name }}_remove" value="1" class="rounded"> Hapus file</label>
                        @endif
                    </div>
                </div>
            </div>
            @break

        @case('icon')
            <div data-icon-field>
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-primary-50 text-primary" data-icon-current><x-glyph :name="$value ?: 'zap'" class="h-5 w-5" /></span>
                    <input type="text" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}" class="input" placeholder="mis. zap">
                </div>
                <div class="mt-2 grid grid-cols-10 gap-1.5 rounded-md bg-canvas p-2" data-icon-picker="{{ $id }}">
                    @foreach ($iconChoices as $choice)
                        <button type="button" data-icon="{{ $choice }}" title="{{ $choice }}" class="flex aspect-square items-center justify-center rounded-lg border border-transparent bg-white text-ink/70 transition hover:scale-110 hover:text-primary">
                            <x-glyph :name="$choice" class="h-4 w-4" />
                        </button>
                    @endforeach
                </div>
                <p class="mt-1 text-xs text-muted">Pilih ikon atau ketik nama ikon dari <a href="https://lucide.dev/icons" target="_blank" class="text-primary hover:underline">lucide.dev</a>.</p>
            </div>
            @break

        @default
            @php
                $inputType = match ($type) {
                    'email' => 'email',
                    'number' => 'number',
                    'date' => 'date',
                    'datetime' => 'datetime-local',
                    'password' => 'password',
                    default => 'text',
                };
            @endphp
            <input type="{{ $inputType }}" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}" class="input"
                   @if ($type === 'number') step="{{ $field['step'] ?? '1' }}" @endif
                   @if ($type === 'password') autocomplete="new-password" @endif
                   @required($required)>
    @endswitch

    @if ($field['help'] ?? null)
        <p class="mt-1.5 text-xs text-muted">{{ $field['help'] }}</p>
    @endif
    @error($name)
        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
    @enderror
</div>
