<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use League\MimeTypeDetection\ExtensionMimeTypeDetector;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves uploaded media from the media disk through the app (config cms.media_proxy).
 *
 * Used when the bucket is private or only reachable over plain HTTP (e.g. the company
 * MinIO): the browser gets the file over the site's own HTTPS origin, and the long
 * cache lifetime lets the CDN answer repeat requests. Stored paths are unique
 * (hashed names from Laravel's store()), so caching them forever is safe.
 *
 * The first request keeps a copy on the local disk; later ones never touch the bucket.
 */
class MediaController extends Controller
{
    private const ATTEMPTS = 3;

    public function __invoke(string $path): Response
    {
        abort_if(str_contains($path, '..'), 404);

        $cache = Storage::disk('local');
        $copy = 'media-cache/'.$path;
        $headers = ['Cache-Control' => 'public, max-age=31536000, s-maxage=31536000, immutable'];

        if ($cache->fileExists($copy)) {
            return $cache->response($copy, null, $headers);
        }

        $contents = $this->fetch($path);
        abort_if($contents === null, 404);

        // Read-only hosts (Vercel) can't keep the copy; they just send the file.
        if (! $cache->put($copy, $contents)) {
            $type = (new ExtensionMimeTypeDetector)->detectMimeTypeFromPath($path);

            return response($contents, 200, $headers + ['Content-Type' => $type ?? 'application/octet-stream']);
        }

        return $cache->response($copy, null, $headers);
    }

    /**
     * Read straight from the bucket, without an exists() check first: behind Cloudflare the
     * HEAD request that makes is refused now and then, while GETs go through. A failed read
     * and a missing file look the same here, so a missing file just takes a moment longer.
     */
    private function fetch(string $path): ?string
    {
        for ($attempt = 1; $attempt <= self::ATTEMPTS; $attempt++) {
            $contents = media_disk()->get($path);

            if ($contents !== null) {
                return $contents;
            }

            usleep(150_000);
        }

        return null;
    }
}
