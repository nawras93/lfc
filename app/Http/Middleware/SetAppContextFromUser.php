<?php

namespace App\Http\Middleware;

use App\Models\ParentAccount;
use App\Support\AppContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Scopes a request to the app its authenticated account belongs to, so models using
 * ScopedToApp filter themselves instead of each controller re-deriving the app.
 *
 * Staff (App\Models\User) belong to no app — they scan for whichever app they are
 * serving — so they leave the context inert and their routes scope another way:
 * /staff/fixtures via SetAppContextFromHeader, /scan via an explicit fixture-vs-account
 * check (scoping it would filter the Fixture and ParentAccount lookups it needs).
 */
class SetAppContextFromUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof ParentAccount) {
            return $next($request);
        }

        $context = app(AppContext::class);
        $context->setCurrent($user->app);

        try {
            return $next($request);
        } finally {
            $context->clear();
        }
    }
}
