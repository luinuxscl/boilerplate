<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('app.supported_locales', ['en']);

        $locale = $this->resolveLocale($request, $supported);

        App::setLocale($locale);

        return $next($request);
    }

    /**
     * Resolve the locale in order of priority:
     * 1. Authenticated user's stored locale
     * 2. Session locale (set by language switcher)
     * 3. App default locale
     */
    private function resolveLocale(Request $request, array $supported): string
    {
        if ($request->user() && in_array($request->user()->locale, $supported)) {
            return $request->user()->locale;
        }

        $sessionLocale = $request->session()->get('locale');

        if ($sessionLocale && in_array($sessionLocale, $supported)) {
            return $sessionLocale;
        }

        return config('app.locale', 'en');
    }
}
