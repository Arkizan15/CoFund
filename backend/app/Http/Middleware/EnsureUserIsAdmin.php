<?php

namespace App\Http\Middleware;

use App\Enums\RoleEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * In production: requires authentication and admin role.
     * In other environments: allows free access for development convenience.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('production')) {
            if (! $request->user()) {
                return redirect()->route('login');
            }

            if ($request->user()->role !== RoleEnum::ADMIN) {
                abort(403, 'Akses ditolak. Hanya admin yang dapat mengakses halaman ini.');
            }
        }

        return $next($request);
    }
}
