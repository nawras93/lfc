<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPublicLocale
{
    private const SUPPORTED_LOCALES = ['en', 'ar'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->query('lang');

        if (! in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $locale = $request->session()->get('public_locale', config('app.locale'));
        }

        if (! in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $locale = config('app.fallback_locale', 'en');
        }

        app()->setLocale($locale);
        $request->session()->put('public_locale', $locale);

        return $next($request);
    }
}
