<?php

use App\Models\Setting;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

if (! function_exists('setting')) {
    function setting(string $key, ?string $default = null): ?string
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('media_disk')) {
    /** Disk that holds uploaded media (config cms.media_disk / MEDIA_DISK). */
    function media_disk(): FilesystemAdapter
    {
        return Storage::disk(config('cms.media_disk'));
    }
}

if (! function_exists('media_url')) {
    /** Public URL for an uploaded file, or null when there is none. */
    function media_url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        $disk = media_disk();

        // Local disks are cheap to check; for cloud storage (R2/S3) skip the
        // per-file API call and trust the stored path.
        if (config('filesystems.disks.'.config('cms.media_disk').'.driver') === 'local' && ! $disk->exists($path)) {
            return null;
        }

        return $disk->url($path);
    }
}

if (! function_exists('lines')) {
    /** Split a multi-line setting into a clean array. */
    function lines(?string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $text))));
    }
}
