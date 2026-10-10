<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Allow the request only if the user has one of the given roles.
     * Usage: ->middleware('role:admin') or ->middleware('role:admin,instructor')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        $allowedRoles = collect($roles)
            ->flatMap(fn ($r) => explode(',', (string) $r))
            ->map(fn ($r) => trim($r))
            ->filter()
            ->all();

        if (! $user || ! in_array($user->role, $allowedRoles, true)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
