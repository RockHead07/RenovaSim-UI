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
        
        // Check if user is admin via flag or admin email with password
        if (!$user || (!$user->is_admin && $user->email !== 'admin@gmail.com')) {
            abort(403, 'You do not have permission to access admin pages.');
        }

        return $next($request);
    }
}
