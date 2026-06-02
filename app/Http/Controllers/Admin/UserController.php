<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SupabaseService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct(protected SupabaseService $supabase) {}

    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $plan   = $request->input('plan', 'All');

        $raw = $search
            ? $this->supabase->selectOr('users', '*', "username.ilike.*{$search}*,email.ilike.*{$search}*")
            : $this->supabase->select('users', '*');

        if ($plan !== 'All' && $plan !== '') {
            $raw = array_filter($raw, fn($u) => ($u['plan'] ?? 'Free') === $plan);
        }

        usort($raw, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        $raw = array_values($raw);

        $perPage     = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $slice       = array_slice($raw, ($currentPage - 1) * $perPage, $perPage);

        $users = new LengthAwarePaginator(
            collect($slice)->map(fn($u) => (object) $u),
            count($raw),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $usersData = collect($slice)->map(fn($u) => [
            'id'         => $u['id'],
            'name'       => $u['username'] ?? '',
            'email'      => $u['email'] ?? '',
            'role'       => $u['role'] ?? 'user',
            'avatar_url' => !empty($u['avatar_path']) ? asset('storage/' . $u['avatar_path']) : null,
            'roleLabel'  => match ($u['role'] ?? 'user') {
                'admin'       => 'Admin',
                'super_admin' => 'Super Admin',
                'owner'       => 'Owner',
                default       => 'User',
            },
            'plan'   => $u['plan'] ?? 'Free',
            'joined' => isset($u['created_at']) ? Carbon::parse($u['created_at'])->format('Y-m-d') : '',
            'status' => match ($u['account_status'] ?? 'active') {
                'inactive'  => 'Inactive',
                'suspended' => 'Suspended',
                default     => 'Active',
            },
        ])->values();

        return view('admin.users.index', compact('users', 'usersData'));
    }

    public function api(Request $request)
    {
        $search = $request->input('search', '');
        $plan   = $request->input('plan', 'All');

        $raw = $search
            ? $this->supabase->selectOr('users', '*', "username.ilike.*{$search}*,email.ilike.*{$search}*")
            : $this->supabase->select('users', '*');

        if ($plan !== 'All' && $plan !== '') {
            $raw = array_filter($raw, fn($u) => ($u['plan'] ?? 'Free') === $plan);
        }

        usort($raw, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

        return response()->json(collect(array_values($raw))->map(fn($u) => [
            'id'         => $u['id'],
            'name'       => $u['username'] ?? '',
            'email'      => $u['email'] ?? '',
            'role'       => $u['role'] ?? 'user',
            'avatar_url' => !empty($u['avatar_path']) ? asset('storage/' . $u['avatar_path']) : null,
            'roleLabel'  => match ($u['role'] ?? 'user') {
                'admin' => 'Admin', 'super_admin' => 'Super Admin', 'owner' => 'Owner', default => 'User',
            },
            'plan'   => $u['plan'] ?? 'Free',
            'joined' => isset($u['created_at']) ? Carbon::parse($u['created_at'])->format('Y-m-d') : '',
            'status' => match ($u['account_status'] ?? 'active') {
                'inactive' => 'Inactive', 'suspended' => 'Suspended', default => 'Active',
            },
        ])->values());
    }

    public function create()
    {
        $projectsRaw = $this->supabase->select('projects', 'id,name');
        usort($projectsRaw, fn($a, $b) => strcmp($a['name'] ?? '', $b['name'] ?? ''));
        $projects = collect($projectsRaw)->map(fn($p) => (object) $p);

        $timezones = \DateTimeZone::listIdentifiers();
        $languages = ['en' => 'English', 'es' => 'Spanish', 'fr' => 'French', 'de' => 'German', 'pt' => 'Portuguese'];

        return view('admin.users.create', compact('projects', 'timezones', 'languages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username'       => 'required|string|max:100',
            'email'          => 'required|email|max:255',
            'password'       => 'required|min:6',
            'role'           => 'required|in:user,admin,super_admin,owner',
            'first_name'     => 'nullable|string|max:255',
            'last_name'      => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'avatar'         => 'nullable|image|max:5120',
            'account_status' => 'required|in:active,suspended,inactive',
        ]);

        if (!empty($this->supabase->select('users', 'id', ['email' => $request->email]))) {
            return back()->withErrors(['email' => 'Email already taken.'])->withInput();
        }
        if (!empty($this->supabase->select('users', 'id', ['username' => $request->username]))) {
            return back()->withErrors(['username' => 'Username already taken.'])->withInput();
        }

        $data = $request->only('username', 'first_name', 'last_name', 'email', 'phone', 'role', 'account_status', 'timezone', 'language', 'job_title');
        $data['password'] = Hash::make($request->password);
        $data['plan'] = 'Free';

        if ($request->hasFile('avatar')) {
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        $this->supabase->insert('users', $data);
        return redirect('/admin/users')->with('success', 'User created successfully.');
    }

    public function edit(int $user)
    {
        $rows = $this->supabase->select('users', '*', ['id' => $user]);
        if (empty($rows)) abort(404);
        $userObj = (object) $rows[0];

        $projectsRaw = $this->supabase->select('projects', 'id,name');
        usort($projectsRaw, fn($a, $b) => strcmp($a['name'] ?? '', $b['name'] ?? ''));
        $projects = collect($projectsRaw)->map(fn($p) => (object) $p);

        $selectedProjectIds = [];
        $timezones    = \DateTimeZone::listIdentifiers();
        $languages    = ['en' => 'English', 'es' => 'Spanish', 'fr' => 'French', 'de' => 'German', 'pt' => 'Portuguese'];
        $pricingPlans = \App\Models\PricingPlan::where('is_active', true)->get();

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
        $rows = $this->supabase->select('users', '*', ['id' => $user]);
        if (empty($rows)) abort(404);
        $existing = $rows[0];

        $request->validate([
            'username'       => 'required|string|max:100',
            'email'          => 'required|email|max:255',
            'role'           => 'required|in:user,admin,super_admin,owner',
            'account_status' => 'required|in:active,suspended,inactive',
        ]);

        $emailRows = $this->supabase->select('users', 'id', ['email' => $request->email]);
        if (!empty($emailRows) && $emailRows[0]['id'] != $user) {
            return back()->withErrors(['email' => 'Email already taken.'])->withInput();
        }
        $unameRows = $this->supabase->select('users', 'id', ['username' => $request->username]);
        if (!empty($unameRows) && $unameRows[0]['id'] != $user) {
            return back()->withErrors(['username' => 'Username already taken.'])->withInput();
        }

        $data = $request->only('username', 'email', 'role', 'first_name', 'last_name', 'phone', 'account_status', 'timezone', 'language', 'job_title');

        if ($request->boolean('remove_avatar') && ($existing['avatar_path'] ?? null)) {
            Storage::disk('public')->delete($existing['avatar_path']);
            $data['avatar_path'] = null;
        }
        if ($request->hasFile('avatar')) {
            if ($existing['avatar_path'] ?? null) Storage::disk('public')->delete($existing['avatar_path']);
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->filled('pricing_plan_id')) {
            $plan = \App\Models\PricingPlan::find($request->input('pricing_plan_id'));
            if ($plan) {
                $data['pricing_plan_id'] = $plan->id;
                $data['plan'] = $plan->name;
            }
        }

        $this->supabase->update('users', $user, $data);
        return redirect('/admin/users')->with('success', 'User updated successfully.');
    }

    public function destroy(int $user)
    {
        $rows = $this->supabase->select('users', 'id,avatar_path', ['id' => $user]);
        if (!empty($rows) && $rows[0]['avatar_path'] ?? null) {
            Storage::disk('public')->delete($rows[0]['avatar_path']);
        }
        $this->supabase->delete('users', $user);
        return redirect('/admin/users')->with('success', 'User deleted successfully.');
    }
}
