<?php

namespace App\Http\Middleware;

use Closure;

class SecurityHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), geolocation=(self), microphone=(self), payment=(self)');
        $cspDirectives = [
            "default-src 'self'",
            "base-uri 'self'",
            "frame-ancestors 'none'",
            "form-action 'self' https://*.khalti.com https://*.esewa.com.np https://esewa.com.np https://*.stripe.com https://checkout.stripe.com",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com https://code.jquery.com https://js.pusher.com https://js.stripe.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com https://fonts.googleapis.com",
            "img-src 'self' data: blob: https://*.tile.openstreetmap.org https://flagcdn.com https://*.googleapis.com https://*.gstatic.com https://*.stripe.com",
            "font-src 'self' data: https://cdnjs.cloudflare.com https://fonts.gstatic.com",
            "connect-src 'self' ws: wss: https://*.openstreetmap.org https://nominatim.openstreetmap.org https://*.googleapis.com https://a.khalti.com https://*.khalti.com https://api.stripe.com",
            "frame-src 'self' https://js.stripe.com https://hooks.stripe.com https://checkout.stripe.com",
            "object-src 'none'",
        ];

        if ($request->isSecure() || app()->isProduction()) {
            $cspDirectives[] = "upgrade-insecure-requests";
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        $response->headers->set('Content-Security-Policy', implode('; ', $cspDirectives));
        
        return $response;
    }
}
