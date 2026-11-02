<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * During the soft launch (config cms.soft_launch) sections that aren't ready send
 * visitors to the Coming Soon page. The contact page stays reachable right after a
 * form submit, because that's where the "message sent" receipt is shown.
 */
class SoftLaunch
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe() && site_closed($request->path()) && ! $request->session()->has('sent')) {
            return redirect('/');
        }

        return $next($request);
    }
}
