<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Generic CRUD for every module described in config/cms.php.
 */
class ResourceController extends Controller
{
    public function index(Request $request, string $module): View
    {
        $config = $this->module($module);
        $query = $this->query($config);

        if ($search = $request->query('q')) {
            $query->where(function (Builder $q) use ($config, $search) {
                foreach ($config['search'] ?? [] as $column) {
                    $q->orWhereLike($column, "%{$search}%");
                }
            });
        }

        [$column, $direction] = $config['order'] ?? ['id', 'desc'];

        return view('admin.resource.index', [
            'module' => $module,
            'config' => $config,
            'items' => $query->orderBy($column, $direction)->paginate(12)->withQueryString(),
        ]);
    }

    public function create(string $module): View
    {
        $config = $this->module($module);
        abort_if($config['readonly'] ?? false, 404);

        return view('admin.resource.form', [
            'module' => $module,
            'config' => $config,
            'item' => new $config['model'],
        ]);
    }

    public function store(Request $request, string $module): RedirectResponse
    {
        $config = $this->module($module);
        abort_if($config['readonly'] ?? false, 404);

        $item = new $config['model'];
        $item->fill($this->payload($request, $config, $item) + ($config['scope'] ?? []));
        $item->save();

        return redirect()->route('admin.resource.index', $module)
            ->with('success', $config['label'].' berhasil ditambahkan.');
    }

    public function show(string $module, int $id): View
    {
        $config = $this->module($module);
        $item = $this->query($config)->findOrFail($id);

        if (array_key_exists('is_read', $item->getAttributes()) && ! $item->is_read) {
            $item->update(['is_read' => true]);
        }

        return view('admin.resource.show', compact('module', 'config', 'item'));
    }

    public function edit(string $module, int $id): View
    {
        $config = $this->module($module);
        abort_if($config['readonly'] ?? false, 404);

        return view('admin.resource.form', [
            'module' => $module,
            'config' => $config,
            'item' => $this->query($config)->findOrFail($id),
        ]);
    }

    public function update(Request $request, string $module, int $id): RedirectResponse
    {
        $config = $this->module($module);
        abort_if($config['readonly'] ?? false, 404);

        $item = $this->query($config)->findOrFail($id);
        $item->fill($this->payload($request, $config, $item))->save();

        return redirect()->route('admin.resource.index', $module)
            ->with('success', $config['label'].' berhasil diperbarui.');
    }

    public function destroy(Request $request, string $module, int $id): RedirectResponse
    {
        $config = $this->module($module);
        $item = $this->query($config)->findOrFail($id);

        if ($module === 'users' && $item->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        foreach ($config['fields'] as $key => $field) {
            if (in_array($field['type'], ['image', 'file'], true) && $item->{$key}) {
                media_disk()->delete($item->{$key});
            }
        }

        $item->delete();

        return back()->with('success', $config['label'].' berhasil dihapus.');
    }

    /** Image uploads from the rich-text editor. */
    public function editorUpload(Request $request): JsonResponse
    {
        $request->validate(['image' => 'required|image|max:4096']);

        return response()->json([
            'url' => media_url($request->file('image')->store('editor', config('cms.media_disk'))),
        ]);
    }

    private function module(string $module): array
    {
        return config("cms.modules.{$module}") ?? abort(404);
    }

    private function query(array $config): Builder
    {
        return $config['model']::query()->where($config['scope'] ?? []);
    }

    /** Validate the request against the module's field rules and build attributes. */
    private function payload(Request $request, array $config, Model $item): array
    {
        $rules = [];
        foreach ($config['fields'] as $key => $field) {
            $rule = str_replace('{id}', (string) ($item->getKey() ?? 'NULL'), $field['rules'] ?? 'nullable');

            if ($field['type'] === 'password' && ! $item->exists) {
                $rule = str_replace('nullable', 'required', $rule);
            }

            $rules[$key] = $rule;
        }

        $validated = $request->validate($rules);
        $data = [];

        foreach ($config['fields'] as $key => $field) {
            switch ($field['type']) {
                case 'toggle':
                    $data[$key] = $request->boolean($key);
                    break;

                case 'password':
                    if (filled($validated[$key] ?? null)) {
                        $data[$key] = $validated[$key];
                    }
                    break;

                case 'image':
                case 'file':
                    if ($request->boolean("{$key}_remove") && $item->{$key}) {
                        media_disk()->delete($item->{$key});
                        $data[$key] = null;
                    }
                    if ($request->hasFile($key)) {
                        if ($item->{$key}) {
                            media_disk()->delete($item->{$key});
                        }
                        $folder = Str::slug(class_basename($config['model']));
                        $data[$key] = $request->file($key)->store($folder, config('cms.media_disk'));
                    }
                    break;

                case 'number':
                    $data[$key] = ($validated[$key] ?? null) === null ? ($key === 'sort_order' ? 0 : null) : $validated[$key];
                    break;

                default:
                    $data[$key] = $validated[$key] ?? null;
            }
        }

        if ($from = $config['slug_from'] ?? null) {
            $data['slug'] = $this->uniqueSlug($config, $data[$from], $item);
        }

        if ($config['model'] === \App\Models\Post::class && ($data['status'] ?? null) === 'published' && empty($data['published_at'])) {
            $data['published_at'] = $item->published_at ?? now();
        }

        return $data;
    }

    private function uniqueSlug(array $config, string $source, Model $item): string
    {
        $base = Str::slug($source) ?: Str::random(8);
        $slug = $base;
        $i = 2;

        while ($config['model']::where('slug', $slug)->whereKeyNot($item->getKey())->exists()) {
            $slug = "{$base}-".$i++;
        }

        return $slug;
    }
}
