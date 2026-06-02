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
        ) + [
            'cities'     => config('renovasim.cities', []),
            'avatar_url' => $user->avatar_url,
        ]);
    }

    public function updateProfile(Request $request)
    {
        \Log::info('updateProfile called', [
            'has_avatar_base64'    => $request->filled('avatar_base64'),
            'avatar_base64_length' => strlen($request->input('avatar_base64', '')),
            'disk'                 => config('filesystems.default'),
            'all_keys'             => array_keys($request->all()),
        ]);

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
            $filename  = $user->id . '_' . time() . '.jpg';
            $disk      = config('filesystems.default', 'public');

            // Silently try to delete old avatar — it may not exist on this disk
            if ($user->avatar_path) {
                try {
                    Storage::disk($disk)->delete($user->avatar_path);
                } catch (\Exception $e) {
                    \Log::warning('Old avatar delete skipped', ['path' => $user->avatar_path, 'reason' => $e->getMessage()]);
                }
            }

            try {
                Storage::disk($disk)->put($filename, $imageData, 'public');
                $data['avatar_path'] = $filename;
                \Log::info('Avatar uploaded successfully', ['disk' => $disk, 'path' => $filename]);
            } catch (\Exception $e) {
                \Log::error('Avatar upload failed', ['error' => $e->getMessage(), 'disk' => $disk]);
                return back()->withErrors(['avatar' => 'Gagal upload foto: ' . $e->getMessage()]);
            }
        }

        if ($request->boolean('remove_avatar') && $user->avatar_path) {
            $disk = config('filesystems.default', 'public');
            try {
                Storage::disk($disk)->delete($user->avatar_path);
            } catch (\Exception $e) {
                \Log::warning('Avatar delete on remove skipped', ['path' => $user->avatar_path, 'reason' => $e->getMessage()]);
            }
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
