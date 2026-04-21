<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Find or create user
            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if ($user) {
                // Update Google data if not already set
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->getId(),
                        'google_email' => $googleUser->getEmail(),
                    ]);
                }
            } else {
                // Create new user
                $user = User::create([
                    'email' => $googleUser->getEmail(),
                    'username' => $this->generateUsername($googleUser->getEmail()),
                    'first_name' => $googleUser->user['given_name'] ?? '',
                    'last_name' => $googleUser->user['family_name'] ?? '',
                    'google_id' => $googleUser->getId(),
                    'google_email' => $googleUser->getEmail(),
                    'password' => bcrypt(uniqid()), // Random password for OAuth users
                    'account_status' => 'active',
                ]);
            }

            // Login the user
            Auth::login($user);

            // Redirect admin to admin dashboard, others to user dashboard
            if ($user->is_admin || $user->email === 'admin@gmail.com') {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Failed to login with Google: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique username from email
     */
    private function generateUsername($email): string
    {
        $username = explode('@', $email)[0];
        $originalUsername = $username;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }

        return $username;
    }
}
