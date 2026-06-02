<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // READ + SEARCH + FILTER
    public function index(Request $request)
    {
        $query = User::query();

        // 🔍 SEARCH (username + email)
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        // 🎯 FILTER PLAN
        if ($request->filled('plan') && $request->plan !== 'All') {
            $query->where('plan', $request->plan);
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        $usersData = $users->map(fn ($user) => [
            'id'         => $user->id,
            'name'       => $user->username,
            'email'      => $user->email,
            'role'       => $user->role ?? 'user',
            'avatar_url' => $user->avatar_path ? asset('storage/' . $user->avatar_path) : null,
            'roleLabel'  => match ($user->role ?? 'user') {
                'admin'       => 'Admin',
                'super_admin' => 'Super Admin',
                'owner'       => 'Owner',
                default       => 'User',
            },
            'plan'   => $user->plan ?? 'Free',
            'joined' => $user->created_at->format('Y-m-d'),
            'status' => match ($user->account_status ?? 'active') {
                'inactive'  => 'Inactive',
                'suspended' => 'Suspended',
                default     => 'Active',
            },
        ])->values();

        return view('admin.users.index', compact('users', 'usersData'));
    }

    // JSON API for Users page (search/filter)
    public function api(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        if ($request->filled('plan') && $request->plan !== 'All') {
            $query->where('plan', $request->plan);
        }

        $users = $query->latest()->limit(200)->get();

        return response()->json(
            $users->map(fn ($user) => [
                'id'         => $user->id,
                'name'       => $user->username,
                'email'      => $user->email,
                'role'       => $user->role ?? 'user',
                'avatar_url' => $user->avatar_path ? asset('storage/' . $user->avatar_path) : null,
                'roleLabel'  => match ($user->role ?? 'user') {
                    'admin'       => 'Admin',
                    'super_admin' => 'Super Admin',
                    'owner'       => 'Owner',
                    default       => 'User',
                },
                'plan'   => $user->plan ?? 'Free',
                'joined' => optional($user->created_at)->format('Y-m-d'),
                'status' => match ($user->account_status ?? 'active') {
                    'inactive'  => 'Inactive',
                    'suspended' => 'Suspended',
                    default     => 'Active',
                },
            ])->values()
        );
    }

    // SHOW CREATE FORM
    public function create()
    {
        $projects = Project::query()->orderBy('name')->get(['id', 'name']);
        $timezones = \DateTimeZone::listIdentifiers();
        $languages = [
            'en' => 'English',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'pt' => 'Portuguese',
        ];

        return view('admin.users.create', compact('projects', 'timezones', 'languages'));
    }

    // CREATE
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:user,admin,super_admin,owner',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'avatar' => 'nullable|image|max:5120',
            'account_status' => 'required|in:active,suspended,inactive',
            'timezone' => 'nullable|timezone',
            'language' => 'nullable|string|max:10',
            'job_title' => 'nullable|string|max:255',
            'assigned_projects' => 'nullable|array',
            'assigned_projects.*' => 'integer|exists:projects,id',
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'username' => $request->username,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'avatar_path' => $avatarPath,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'account_status' => $request->account_status,
            'timezone' => $request->timezone,
            'language' => $request->language,
            'job_title' => $request->job_title,
            'plan' => 'Free',
        ]);

        $user->assignedProjects()->sync($request->input('assigned_projects', []));

        return redirect('/admin/users')->with('success', 'User created successfully');
    }

    // SHOW EDIT FORM
    public function edit(User $user)
    {
        $projects = Project::query()->orderBy('name')->get(['id', 'name']);
        $selectedProjectIds = $user->assignedProjects()->pluck('projects.id')->all();
        $timezones = \DateTimeZone::listIdentifiers();
        $languages = [
            'en' => 'English',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'pt' => 'Portuguese',
        ];
        $pricingPlans = \App\Models\PricingPlan::where('is_active', true)->get();

        return view('admin.users.edit', compact('user', 'projects', 'selectedProjectIds', 'timezones', 'languages', 'pricingPlans'));
    }

    // UPDATE
    public function update(Request $request, User $user)
    {
        $request->validate([
            'username' => 'required|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:user,admin,super_admin,owner',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'avatar' => 'nullable|image|max:5120',
            'remove_avatar' => 'nullable|boolean',
            'account_status' => 'required|in:active,suspended,inactive',
            'timezone' => 'nullable|timezone',
            'language' => 'nullable|string|max:10',
            'job_title' => 'nullable|string|max:255',
            'assigned_projects' => 'nullable|array',
            'assigned_projects.*' => 'integer|exists:projects,id',
        ]);

        $data = $request->only([
            'username',
            'email',
            'role',
            'first_name',
            'last_name',
            'phone',
            'account_status',
            'timezone',
            'language',
            'job_title',
        ]);

        $removeAvatar = (bool) $request->boolean('remove_avatar');
        if ($removeAvatar && $user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $data['avatar_path'] = null;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->filled('pricing_plan_id')) {
            $plan = \App\Models\PricingPlan::find($request->input('pricing_plan_id'));
            if ($plan) {
                $data['pricing_plan_id'] = $plan->id;
                $data['plan'] = $plan->name;
            }
        }

        $user->update($data);
        $user->assignedProjects()->sync($request->input('assigned_projects', []));

        return redirect('/admin/users')->with('success', 'User updated successfully');
    }

    // DELETE
    public function destroy(User $user)
    {
        $user->delete();

        return redirect('/admin/users')->with('success', 'User deleted successfully');
    }
}