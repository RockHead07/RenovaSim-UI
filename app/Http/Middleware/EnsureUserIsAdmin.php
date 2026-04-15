<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!in_array($request->user()?->role, ['admin', 'super_admin', 'owner'], true)) {
            abort(403, 'You do not have permission to access admin pages.');
        }

        return $next($request);
    }
}
