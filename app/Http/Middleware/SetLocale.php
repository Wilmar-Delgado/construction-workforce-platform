<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Session (manual switch)
        if (session()->has('locale')) {
            App::setLocale(session('locale'));
            return $next($request);
        }

        // 2. User preference (future-ready)
        if ($request->user() && $request->user()->language) {
            App::setLocale($request->user()->language);
            return $next($request);
        }

        // 3. Browser language
        $browserLocale = substr($request->header('Accept-Language'), 0, 2);

        if (in_array($browserLocale, ['en', 'fr'])) {
            App::setLocale($browserLocale);
        } else {
            App::setLocale(config('app.locale'));
        }

        // 🔵 TEMPORARY FORCE (for testing only)
        // App::setLocale('fr');

        return $next($request);
    }
}