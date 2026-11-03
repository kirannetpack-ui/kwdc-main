<?php

namespace App\Http\Middleware;

use Closure;

class ForceHttps
{
    public function handle($request, Closure $next)
    {
        $forwardedProto = $request->headers->get('X-Forwarded-Proto');

        if (!$request->secure() && $forwardedProto !== 'https' && config('app.env') === 'production') {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
