<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $service = app(\App\Services\SupabaseService::class);

        // Check if user already exists
        $existing = $service->select('users', '*', ['email' => $request->email]);
        if (!empty($existing)) {
            throw ValidationException::withMessages([
                'email' => 'Email sudah terdaftar.',
            ]);
        }

        // Create user via Supabase API
        $userData = [
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'is_admin' => false,
        ];

        $result = $service->insert('users', $userData);
        
        if (!$result) {
            throw ValidationException::withMessages([
                'email' => 'Gagal membuat akun. Silakan coba lagi.',
            ]);
        }

        // Store user in session
        $user = is_array($result) ? $result[0] : (array) $result;
        $request->session()->put('auth_user', $user);

        event(new Registered($user));

        return redirect(route('dashboard', absolute: false));
    }
}
