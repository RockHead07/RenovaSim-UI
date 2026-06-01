<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastActiveAt
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = Auth::user();
        if ($user) {
            try {
                $id = $user->getAuthIdentifier();
                $updates = ['last_active_at' => now()->toISOString()];

                $accountStatus = (string) ($user->getAttribute('account_status') ?? '');
                if ($accountStatus !== 'suspended') {
                    $updates['account_status'] = 'active';
                }

                app(\App\Services\SupabaseService::class)->update('users', $id, $updates);
            } catch (\Throwable) {
                // Non-critical — don't break the response if tracking fails
            }
        }

        return $response;
    }
}
