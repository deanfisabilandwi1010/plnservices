<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(string $section): View
    {
        $config = $this->section($section);

        return view('admin.settings', [
            'section' => $section,
            'config' => $config,
            'values' => Setting::allCached(),
        ]);
    }

    public function update(Request $request, string $section): RedirectResponse
    {
        $config = $this->section($section);
        $values = [];

        foreach ($config['fields'] as $key => $field) {
            if ($field['type'] === 'image') {
                $request->validate([$key => 'nullable|image|max:4096']);

                if ($request->boolean("{$key}_remove")) {
                    media_disk()->delete((string) Setting::get($key));
                    $values[$key] = null;
                }

                if ($request->hasFile($key)) {
                    media_disk()->delete((string) Setting::get($key));
                    $values[$key] = $request->file($key)->store('settings', config('cms.media_disk'));
                }

                continue;
            }

            $values[$key] = $request->input($key);
        }

        Setting::put($values);

        return back()->with('success', $config['label'].' berhasil disimpan.');
    }

    private function section(string $section): array
    {
        return config("cms.settings.{$section}") ?? abort(404);
    }
}
