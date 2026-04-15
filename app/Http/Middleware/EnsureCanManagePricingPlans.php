<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanManagePricingPlans
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!in_array($request->user()?->role, ['super_admin', 'owner'], true)) {
            abort(403, 'Only Super Admin and Owner can manage pricing plans.');
        }

        return $next($request);
    }
}
