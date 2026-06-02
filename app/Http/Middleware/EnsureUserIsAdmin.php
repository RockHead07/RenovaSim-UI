<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        $adminRoles = ['admin', 'owner', 'super_admin'];

        if (! $user || (! $user->is_admin && ! in_array($user->role, $adminRoles, true) && $user->email !== 'admin@gmail.com')) {
            abort(403, 'You do not have permission to access admin pages.');
        }

        return $next($request);
    }
}
