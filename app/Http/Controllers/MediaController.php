<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves uploaded media from the media disk through the app (config cms.media_proxy).
 *
 * Used when the bucket is private or only reachable over plain HTTP (e.g. the company
 * MinIO): the browser gets the file over the site's own HTTPS origin, and the long
 * cache lifetime lets the CDN answer repeat requests. Stored paths are unique
 * (hashed names from Laravel's store()), so caching them forever is safe.
 */
class MediaController extends Controller
{
    public function __invoke(string $path): StreamedResponse
    {
        abort_if(str_contains($path, '..'), 404);

        $disk = media_disk();
        abort_unless($disk->exists($path), 404);

        return $disk->response($path, null, [
            'Cache-Control' => 'public, max-age=31536000, s-maxage=31536000, immutable',
        ]);
    }
}
