<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle incoming request and configure application locale.
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale', config('app.locale', 'en'));

        if (in_array($locale, ['en', 'ne'])) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
