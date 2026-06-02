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
                $updates = ['last_active_at' => now()];

                $accountStatus = (string) ($user->getAttribute('account_status') ?? '');
                if ($accountStatus !== 'suspended') {
                    $updates['account_status'] = 'active';
                }

                \App\Models\User::where('id', $user->getAuthIdentifier())->update($updates);
            } catch (\Throwable) {
                // Non-critical — don't break the response if tracking fails
            }
        }

        return $response;
    }
}
