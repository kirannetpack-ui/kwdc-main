<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLaunchReady
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('kwdc.launch_ready') && ! ($request->isMethod('GET') && $request->is(
            '/', 'up', 'ping', 'privacy-policy', 'terms-of-service', 'compliance',
            'manifest.json', 'offline.html'
        ))) {
            return response()->view('errors.setup', [], 503)
                ->header('Cache-Control', 'no-store')
                ->header('Retry-After', '3600');
        }

        return $next($request);
    }
}
