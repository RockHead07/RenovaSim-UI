<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class UserSettingsController extends Controller
{
    public function show()
    {
        return view('user.pages.settings', [
            'user'   => auth()->user(),
            'cities' => config('renovasim.cities'),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $service = app(\App\Services\SupabaseService::class);

        $request->validate([
            'username'         => 'required|string|max:100',
            'first_name'       => 'nullable|string|max:100',
            'last_name'        => 'nullable|string|max:100',
            'phone'            => 'nullable|string|max:20',
            'default_location' => 'nullable|string|max:100',
            'avatar_base64'    => 'nullable|string',
        ]);

        $data = $request->only('username', 'first_name', 'last_name', 'phone', 'default_location');

        // Handle cropped base64 avatar
        if ($request->filled('avatar_base64')) {
            $base64 = $request->input('avatar_base64');
            $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64));
            $filename = 'avatars/' . $user->id . '_' . time() . '.jpg';
            Storage::disk('public')->put($filename, $imageData);
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $data['avatar_path'] = $filename;
        }

        if ($request->boolean('remove_avatar') && $user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $data['avatar_path'] = null;
        }

        // Extract user ID safely from array/object
        $userId = is_array($user->getAttributes()) 
            ? ($user->getAttributes()['id'] ?? $user->id) 
            : $user->id;

        // Use SupabaseService to update database
        $service->update('users', $userId, $data);

        return back()->with('success_profile', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();
        $service = app(\App\Services\SupabaseService::class);

        $request->validate([
            'current_password' => 'required|string',
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.'])->with('tab', 'password');
        }

        // Extract user ID safely
        $userId = is_array($user->getAttributes()) 
            ? ($user->getAttributes()['id'] ?? $user->id) 
            : $user->id;

        // Use SupabaseService to update password
        $service->update('users', $userId, ['password' => Hash::make($request->password)]);

        return back()->with('success_password', 'Password berhasil diperbarui.')->with('tab', 'password');
    }
}
