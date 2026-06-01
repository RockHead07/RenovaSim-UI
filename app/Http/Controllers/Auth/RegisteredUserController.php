<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $service = app(\App\Services\SupabaseService::class);

        // Check if email already exists
        $existing = $service->select('users', 'id', ['email' => $request->email]);
        if (!empty($existing)) {
            throw ValidationException::withMessages([
                'email' => 'Email sudah terdaftar.',
            ]);
        }

        // Insert user into Supabase
        $result = $service->insert('users', [
            'username' => $request->username,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'user',
            'is_admin' => false,
        ]);

        if (!$result) {
            throw ValidationException::withMessages([
                'email' => 'Gagal membuat akun. Silakan coba lagi.',
            ]);
        }

        $userData = is_array($result) ? ($result[0] ?? $result) : (array) $result;

        // Hydrate a User model and log in
        $user = new User();
        $user->setRawAttributes($userData);
        $user->exists = true;

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
