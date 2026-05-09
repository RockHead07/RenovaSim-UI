<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class UpdateLastActiveAt
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = Auth::user();
        if ($user instanceof User && Schema::hasColumn('users', 'last_active_at')) {
            $updates = ['last_active_at' => now()];

            $accountStatus = (string) $user->getAttribute('account_status');
            if ($accountStatus !== 'suspended') {
                $updates['account_status'] = 'active';
            }

            $user->forceFill($updates)->save();
        }

        return $response;
    }
}
