<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $allowedRoles = collect($roles)
            ->flatMap(fn (string $role): array => explode('|', $role))
            ->all();

        abort_unless(
            in_array($request->user()?->role, $allowedRoles, true),
            403,
            'Anda tidak memiliki akses untuk melakukan tindakan ini.'
        );

        return $next($request);
    }
}
