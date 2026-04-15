<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

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
            'id' => $user->id,
            'name' => $user->username,
            'email' => $user->email,
            'role' => $user->role ?? 'user',
            'roleLabel' => match ($user->role ?? 'user') {
                'admin' => 'Admin',
                'super_admin' => 'Super Admin',
                'owner' => 'Owner',
                default => 'User',
            },
            'plan' => $user->plan ?? 'Free',
            'joined' => $user->created_at->format('Y-m-d'),
            'status' => 'Active',
        ])->values();

        return view('admin.users.index', compact('users', 'usersData'));
    }

    // SHOW CREATE FORM
    public function create()
    {
        return view('admin.users.create');
    }

    // CREATE
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:user,admin,super_admin,owner',
        ]);

        User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'plan' => 'Free',
        ]);

        return redirect('/admin/users')->with('success', 'User created successfully');
    }

    // SHOW EDIT FORM
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    // UPDATE
    public function update(Request $request, User $user)
    {
        $request->validate([
            'username' => 'required|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:user,admin,super_admin,owner',
        ]);

        $user->update($request->only(['username', 'email', 'role']));

        return redirect('/admin/users')->with('success', 'User updated successfully');
    }

    // DELETE
    public function destroy(User $user)
    {
        $user->delete();

        return redirect('/admin/users')->with('success', 'User deleted successfully');
    }
}