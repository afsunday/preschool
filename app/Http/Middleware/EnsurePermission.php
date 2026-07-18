<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate a route on back-office permissions: `permission:content.resources`.
 * Passes if the user has ANY of the listed permissions (a super user has all).
 */
class EnsurePermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        foreach ($permissions as $permission) {
            if ($user?->hasPermission($permission)) {
                return $next($request);
            }
        }

        abort(403);
    }
}
