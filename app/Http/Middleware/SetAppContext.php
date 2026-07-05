<?php

namespace App\Http\Middleware;

use App\Enums\AppKey;
use App\Support\AppContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetAppContext
{
    public function handle(Request $request, Closure $next, string $app): Response
    {
        app(AppContext::class)->setCurrent(AppKey::from($app));

        return $next($request);
    }
}
