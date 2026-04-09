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

        return view('admin.users.index', compact('users'));
    }

    // CREATE
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'plan' => 'Free',
        ]);

        return back()->with('success', 'User created');
    }

    // UPDATE (PLAN ONLY)
    public function update(Request $request, User $user)
    {
        $request->validate([
            'plan' => 'required|in:Free,Smart,Pro',
        ]);

        $user->update([
            'plan' => $request->plan,
        ]);

        return back()->with('success', 'User updated');
    }

    // DELETE
    public function destroy(User $user)
    {
        $user->delete();

        return back()->with('success', 'User deleted');
    }
}