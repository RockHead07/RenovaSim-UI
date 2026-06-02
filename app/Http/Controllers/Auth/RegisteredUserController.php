<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if (User::where('email', $request->email)->exists()) {
            throw ValidationException::withMessages(['email' => 'Email sudah terdaftar.']);
        }

        if (User::where('username', $request->username)->exists()) {
            throw ValidationException::withMessages(['username' => 'Username sudah digunakan.']);
        }

        $user = User::create([
            'username' => $request->username,
            'email'    => $request->email,
            'password' => $request->password,
            'role'     => 'user',
            'is_admin' => false,
        ]);

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
