<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class UserSettingsController extends Controller
{
    public function show()
    {
        $user     = auth()->user();
        $userId   = $user->getAuthIdentifier();
        $supabase = app(SupabaseService::class);

        // Project count via Supabase REST API
        $projectRows  = $supabase->select('projects', 'id', ['user_id' => $userId]);
        $projectCount = count($projectRows);

        // Pricing plan (empty tables default to Free)
        $planId = $user->getAttribute('pricing_plan_id');
        $activePlanArr = null;
        $planFeatures  = [];

        if ($planId) {
            $plans = $supabase->select('pricing_plans', '*', ['id' => $planId]);
            if (!empty($plans)) {
                $activePlanArr = $plans[0];
                $planFeatures  = $supabase->select('plan_features', '*', ['pricing_plan_id' => $planId]);
            }
        }

        if (!$activePlanArr) {
            $free = $supabase->select('pricing_plans', '*', ['slug' => 'free']);
            $activePlanArr = !empty($free) ? $free[0] : ['name' => 'Free Plan', 'slug' => 'free', 'price' => 0];
        }

        $activePlan = (object) $activePlanArr;

        $maxProjects    = $this->featureLimit($planFeatures, 'max_projects');
        $maxEstimations = $this->featureLimit($planFeatures, 'max_estimations_per_project');

        return view('user.pages.settings', compact(
            'user', 'activePlan', 'projectCount', 'maxProjects', 'maxEstimations'
        ) + ['cities' => config('renovasim.cities', [])]);
    }

    public function updateProfile(Request $request)
    {
        $user     = auth()->user();
        $userId   = $user->getAuthIdentifier();
        $supabase = app(SupabaseService::class);

        $request->validate([
            'username'         => 'required|string|max:100',
            'first_name'       => 'nullable|string|max:100',
            'last_name'        => 'nullable|string|max:100',
            'phone'            => 'nullable|string|max:20',
            'default_location' => 'nullable|string|max:100',
            'avatar_base64'    => 'nullable|string',
        ]);

        // Manual unique username check via Supabase (replaces DB unique rule)
        $newUsername = $request->input('username');
        if ($newUsername !== $user->getAttribute('username')) {
            $existing = $supabase->select('users', 'id', ['username' => $newUsername]);
            if (!empty($existing) && ($existing[0]['id'] ?? null) != $userId) {
                return back()->withErrors(['username' => 'Username sudah digunakan.'])->withInput();
            }
        }

        $data = $request->only('username', 'first_name', 'last_name', 'phone', 'default_location');

        // Handle avatar base64 upload
        if ($request->filled('avatar_base64')) {
            $base64    = $request->input('avatar_base64');
            $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64));
            $filename  = 'avatars/' . $userId . '_' . time() . '.jpg';
            Storage::disk('public')->put($filename, $imageData);
            if ($user->getAttribute('avatar_path')) {
                Storage::disk('public')->delete($user->getAttribute('avatar_path'));
            }
            $data['avatar_path'] = $filename;
        }

        if ($request->boolean('remove_avatar') && $user->getAttribute('avatar_path')) {
            Storage::disk('public')->delete($user->getAttribute('avatar_path'));
            $data['avatar_path'] = null;
        }

        $supabase->update('users', $userId, $data);

        // Refresh the authenticated user's raw attributes
        $updated = $supabase->select('users', '*', ['id' => $userId]);
        if (!empty($updated)) {
            $refreshed = new \App\Models\User();
            $refreshed->setRawAttributes($updated[0]);
            $refreshed->exists = true;
            Auth::setUser($refreshed);
        }

        return back()->with('success_profile', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $user     = auth()->user();
        $userId   = $user->getAuthIdentifier();
        $supabase = app(SupabaseService::class);

        $request->validate([
            'current_password' => 'required|string',
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        if (!Hash::check($request->current_password, $user->getAuthPassword())) {
            return back()
                ->withErrors(['current_password' => 'Password saat ini tidak sesuai.'])
                ->with('tab', 'password');
        }

        $supabase->update('users', $userId, ['password' => Hash::make($request->password)]);

        return back()->with('success_password', 'Password berhasil diperbarui.')->with('tab', 'password');
    }

    private function featureLimit(array $features, string $key): ?int
    {
        foreach ($features as $f) {
            if (($f['feature_key'] ?? null) === $key) {
                $val = $f['feature_value'] ?? null;
                if ($val === 'unlimited' || $val === null) return null;
                if (is_numeric($val)) return (int) $val;
            }
        }
        return null;
    }
}
