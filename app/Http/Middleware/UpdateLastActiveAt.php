<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastActiveAt
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = Auth::user();
        if ($user && Schema::hasColumn('users', 'last_active_at')) {
            $updates = ['last_active_at' => now()];

            if ($user->account_status !== 'suspended') {
                $updates['account_status'] = 'active';
            }

            $user->forceFill($updates)->save();
        }

        return $response;
    }
}
