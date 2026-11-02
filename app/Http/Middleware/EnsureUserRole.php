<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage in routes:  ->middleware(EnsureUserRole::class.':client')
 * Also rejects deactivated users on the routes it protects.
 */
class EnsureUserRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_unless(
            $user && $user->is_active !== false && in_array($user->role->value, $roles, true),
            403
        );

        return $next($request);
    }
}
