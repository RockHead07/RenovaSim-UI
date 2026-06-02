<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class UserSettingsController extends Controller
{
    public function show()
    {
        $user         = auth()->user();
        $projectCount = Project::where('user_id', $user->id)->count();
        $activePlan   = $user->activePlan()->load('features');

        $maxProjects    = $this->featureLimit($activePlan->features, 'max_projects');
        $maxEstimations = $this->featureLimit($activePlan->features, 'max_estimations_per_project');

        return view('user.pages.settings', compact(
            'user', 'activePlan', 'projectCount', 'maxProjects', 'maxEstimations'
        ) + ['cities' => config('renovasim.cities', [])]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'username'         => 'required|string|max:100',
            'first_name'       => 'nullable|string|max:100',
            'last_name'        => 'nullable|string|max:100',
            'phone'            => 'nullable|string|max:20',
            'default_location' => 'nullable|string|max:100',
            'avatar_base64'    => 'nullable|string',
        ]);

        if ($request->input('username') !== $user->username) {
            if (\App\Models\User::where('username', $request->input('username'))->where('id', '!=', $user->id)->exists()) {
                return back()->withErrors(['username' => 'Username sudah digunakan.'])->withInput();
            }
        }

        $data = $request->only('username', 'first_name', 'last_name', 'phone', 'default_location');

        if ($request->filled('avatar_base64')) {
            $base64    = $request->input('avatar_base64');
            $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64));
            $filename  = 'avatars/' . $user->id . '_' . time() . '.jpg';
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

        $user->update($data);
        $user->refresh();
        Auth::setUser($user);

        return back()->with('success_profile', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => 'required|string',
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Password saat ini tidak sesuai.'])
                ->with('tab', 'password');
        }

        $user->update(['password' => $request->password]);

        return back()->with('success_password', 'Password berhasil diperbarui.')->with('tab', 'password');
    }

    private function featureLimit($features, string $key): ?int
    {
        foreach ($features as $f) {
            if ($f->feature_key === $key) {
                $val = $f->feature_value ?? null;
                if ($val === 'unlimited' || $val === null) return null;
                if (is_numeric($val)) return (int) $val;
            }
        }
        return null;
    }
}
