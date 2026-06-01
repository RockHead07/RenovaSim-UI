<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();
        $role    = $user?->getAttribute('role');
        $isAdmin = $user && (
            $user->getAttribute('is_admin') ||
            in_array($role, ['admin', 'owner', 'super_admin'], true) ||
            $user->getAttribute('email') === 'admin@gmail.com'
        );

        if ($isAdmin) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }

        return redirect()->intended('/user/dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
