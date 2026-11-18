<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->active) {
            abort(403);
        }

        $current = $user->role->value;
        if ($current !== UserRole::Admin->value && ! in_array($current, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
