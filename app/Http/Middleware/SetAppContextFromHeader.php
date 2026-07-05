<?php

namespace App\Http\Middleware;

use App\Enums\AppKey;
use App\Support\AppContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetAppContextFromHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $app = AppKey::tryFrom((string) $request->header('X-App-Key')) ?? AppKey::AppTwo;
        $context = app(AppContext::class);
        $context->setCurrent($app);

        try {
            return $next($request);
        } finally {
            $context->clear();
        }
    }
}
