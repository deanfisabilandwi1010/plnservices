<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $guarded = ['id'];

    /** Settings already loaded during this request; a page calls setting() dozens of times. */
    private static ?array $loaded = null;

    /** @return array<string, string|null> */
    public static function allCached(): array
    {
        return static::$loaded ??= Cache::rememberForever('settings', fn () => static::pluck('value', 'key')->all());
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::allCached()[$key] ?? $default;
    }

    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        static::$loaded = null;
        Cache::forget('settings');
    }
}
