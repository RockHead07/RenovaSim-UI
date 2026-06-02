<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $plan   = $request->input('plan', 'All');

        $query = User::query();

        if ($search) {
            $query->where(fn($q) => $q->where('username', 'ilike', "%{$search}%")->orWhere('email', 'ilike', "%{$search}%"));
        }
        if ($plan !== 'All' && $plan !== '') {
            $query->where('plan', $plan);
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        $usersData = collect($users->items())->map(fn($u) => [
            'id'            => $u->id,
            'name'          => $u->username ?? '',
            'email'         => $u->email ?? '',
            'role'          => $u->role ?? 'user',
            'avatar_url'    => $u->avatar_url,
            'roleLabel'     => match ($u->role ?? 'user') {
                'admin'       => 'Admin',
                'super_admin' => 'Super Admin',
                'owner'       => 'Owner',
                default       => 'User',
            },
            'plan'          => $u->plan ?? 'Free',
            'joined'        => $u->created_at ? Carbon::parse($u->created_at)->format('Y-m-d') : '',
            'status'        => match ($u->account_status ?? 'active') {
                'inactive'  => 'Inactive',
                'suspended' => 'Suspended',
                default     => 'Active',
            },
            'is_online'     => $u->is_online,
            'online_status' => $u->online_status,
            'last_active'   => $u->last_active_at
                ? Carbon::parse($u->last_active_at)->diffForHumans()
                : 'Never',
        ])->values();

        return view('admin.users.index', compact('users', 'usersData'));
    }

    public function api(Request $request)
    {
        $search = $request->input('search', '');
        $plan   = $request->input('plan', 'All');

        $query = User::query();

        if ($search) {
            $query->where(fn($q) => $q->where('username', 'ilike', "%{$search}%")->orWhere('email', 'ilike', "%{$search}%"));
        }
        if ($plan !== 'All' && $plan !== '') {
            $query->where('plan', $plan);
        }

        return response()->json($query->latest()->get()->map(fn($u) => [
            'id'            => $u->id,
            'name'          => $u->username ?? '',
            'email'         => $u->email ?? '',
            'role'          => $u->role ?? 'user',
            'avatar_url'    => $u->avatar_url,
            'roleLabel'     => match ($u->role ?? 'user') {
                'admin' => 'Admin', 'super_admin' => 'Super Admin', 'owner' => 'Owner', default => 'User',
            },
            'plan'          => $u->plan ?? 'Free',
            'joined'        => $u->created_at ? Carbon::parse($u->created_at)->format('Y-m-d') : '',
            'status'        => match ($u->account_status ?? 'active') {
                'inactive' => 'Inactive', 'suspended' => 'Suspended', default => 'Active',
            },
            'is_online'     => $u->is_online,
            'online_status' => $u->online_status,
            'last_active'   => $u->last_active_at
                ? Carbon::parse($u->last_active_at)->diffForHumans()
                : 'Never',
        ])->values());
    }

    public function create()
    {
        $projects  = \App\Models\Project::orderBy('name')->get(['id', 'name']);
        $timezones = \DateTimeZone::listIdentifiers();
        $languages = ['en' => 'English', 'es' => 'Spanish', 'fr' => 'French', 'de' => 'German', 'pt' => 'Portuguese'];

        return view('admin.users.create', compact('projects', 'timezones', 'languages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username'       => 'required|string|max:100|unique:users',
            'email'          => 'required|email|max:255|unique:users',
            'password'       => 'required|min:6',
            'role'           => 'required|in:user,admin,super_admin,owner',
            'first_name'     => 'nullable|string|max:255',
            'last_name'      => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'avatar'         => 'nullable|image|max:5120',
            'account_status' => 'required|in:active,suspended,inactive',
        ]);

        $data = $request->only('username', 'first_name', 'last_name', 'email', 'phone', 'role', 'account_status', 'timezone', 'language', 'job_title');
        $data['password'] = $request->password;
        $data['plan']     = 'Free';

        if ($request->hasFile('avatar')) {
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        User::create($data);
        return redirect('/admin/users')->with('success', 'User created successfully.');
    }

    public function edit(int $user)
    {
        $userObj      = User::findOrFail($user);
        $projects     = \App\Models\Project::orderBy('name')->get(['id', 'name']);
        $timezones    = \DateTimeZone::listIdentifiers();
        $languages    = ['en' => 'English', 'es' => 'Spanish', 'fr' => 'French', 'de' => 'German', 'pt' => 'Portuguese'];
        $pricingPlans = PricingPlan::where('is_active', true)->get();
        $selectedProjectIds = [];

        return view('admin.users.edit', [
            'user'               => $userObj,
            'projects'           => $projects,
            'selectedProjectIds' => $selectedProjectIds,
            'timezones'          => $timezones,
            'languages'          => $languages,
            'pricingPlans'       => $pricingPlans,
        ]);
    }

    public function update(Request $request, int $user)
    {
        $userObj = User::findOrFail($user);

        $request->validate([
            'username'       => 'required|string|max:100',
            'email'          => 'required|email|max:255',
            'role'           => 'required|in:user,admin,super_admin,owner',
            'account_status' => 'required|in:active,suspended,inactive',
        ]);

        if (User::where('email', $request->email)->where('id', '!=', $user)->exists()) {
            return back()->withErrors(['email' => 'Email already taken.'])->withInput();
        }
        if (User::where('username', $request->username)->where('id', '!=', $user)->exists()) {
            return back()->withErrors(['username' => 'Username already taken.'])->withInput();
        }

        $data = $request->only('username', 'email', 'role', 'first_name', 'last_name', 'phone', 'account_status', 'timezone', 'language', 'job_title');

        if ($request->boolean('remove_avatar') && $userObj->avatar_path) {
            $disk = config('filesystems.default', 'public');
            try { Storage::disk($disk)->delete($userObj->avatar_path); } catch (\Exception $e) {}
            $data['avatar_path'] = null;
        } elseif ($request->filled('avatar_base64')) {
            $base64    = $request->input('avatar_base64');
            $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64));
            $filename  = $userObj->id . '_' . time() . '.jpg';
            $disk      = config('filesystems.default', 'public');
            if ($userObj->avatar_path) {
                try { Storage::disk($disk)->delete($userObj->avatar_path); } catch (\Exception $e) {}
            }
            try {
                Storage::disk($disk)->put($filename, $imageData, 'public');
                $data['avatar_path'] = $filename;
            } catch (\Exception $e) {
                \Log::error('Admin avatar upload failed', ['error' => $e->getMessage()]);
                return back()->withErrors(['avatar' => 'Failed to upload photo: ' . $e->getMessage()])->withInput();
            }
        } elseif ($request->hasFile('avatar')) {
            $disk = config('filesystems.default', 'public');
            if ($userObj->avatar_path) {
                try { Storage::disk($disk)->delete($userObj->avatar_path); } catch (\Exception $e) {}
            }
            $data['avatar_path'] = $request->file('avatar')->store('avatars', $disk);
        }

        if ($request->filled('pricing_plan_id')) {
            $plan = PricingPlan::find($request->input('pricing_plan_id'));
            if ($plan) {
                $data['pricing_plan_id'] = $plan->id;
                $data['plan']            = $plan->name;
            }
        }

        $userObj->update($data);
        return redirect('/admin/users')->with('success', 'User updated successfully.');
    }

    public function destroy(int $user)
    {
        $userObj = User::findOrFail($user);
        if ($userObj->avatar_path) Storage::disk('public')->delete($userObj->avatar_path);
        $userObj->delete();
        return redirect('/admin/users')->with('success', 'User deleted successfully.');
    }
}
