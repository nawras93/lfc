<?php

namespace App\Http\Middleware;

use App\Enums\AppKey;
use App\Support\AppContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetAppContextFromHeader
{
    public function handle(Request $request, Closure $next, ?string $default = null): Response
    {
        $fallback = AppKey::tryFrom((string) $default) ?? AppKey::AppTwo;
        $app = AppKey::tryFrom((string) $request->header('X-App-Key')) ?? $fallback;
        $context = app(AppContext::class);
        $context->setCurrent($app);

        try {
            return $next($request);
        } finally {
            $context->clear();
        }
    }
}
