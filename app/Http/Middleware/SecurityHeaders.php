<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security headers on every response: Content-Security-Policy, Strict-Transport-Security
 * and X-Content-Type-Options.
 *
 * Inline <script> tags carry the per-request nonce (Vite::cspNonce()); @vite adds it to its
 * own tags. Inline style="" attributes and the @font-face block from @fonts stay allowed
 * ('unsafe-inline' in style-src), so the look of the site is unchanged.
 *
 * public/.htaccess sends the same HSTS and nosniff values for static files that never reach PHP.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = Vite::useCspNonce();

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        if ($this->wantsCsp($response)) {
            $response->headers->set('Content-Security-Policy', $this->policy($nonce, $request->isSecure()));
        }

        return $response;
    }

    private function wantsCsp(Response $response): bool
    {
        if (! config('cms.csp')) {
            return false;
        }

        // `npm run dev`: scripts come from the Vite dev server on another port.
        if (Vite::isRunningHot()) {
            return false;
        }

        // Laravel's debug error page relies on inline scripts of its own.
        if (config('app.debug') && property_exists($response, 'exception') && $response->exception) {
            return false;
        }

        return true;
    }

    private function policy(string $nonce, bool $secure): string
    {
        // Google Analytics (optional, setting ga_measurement_id).
        $analytics = 'https://*.google-analytics.com https://*.analytics.google.com https://*.googletagmanager.com';

        return implode('; ', array_filter([
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' https://www.googletagmanager.com",
            "style-src 'self' 'unsafe-inline'",
            // https: covers map tiles (arcgisonline), analytics beacons and images in news content.
            "img-src 'self' data: blob: https:",
            "font-src 'self' data:",
            "connect-src 'self' {$analytics}",
            "frame-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            // Only over HTTPS: on http://localhost it would send the page's own assets to https.
            $secure ? 'upgrade-insecure-requests' : null,
        ]));
    }
}
