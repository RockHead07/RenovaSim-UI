<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Partner;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function metrics()
    {
        $now       = now();
        $thisMonth = $now->month;
        $thisYear  = $now->year;
        $lastMonth = $now->copy()->subMonth();

        $totalUsers     = User::count();
        $activeUsers    = User::where('account_status', 'active')->orWhereNull('account_status')->count();
        $inactiveUsers  = $totalUsers - $activeUsers;
        $usersThisMonth = User::whereYear('created_at', $thisYear)->whereMonth('created_at', $thisMonth)->count();
        $usersLastMonth = User::whereYear('created_at', $lastMonth->year)->whereMonth('created_at', $lastMonth->month)->count();

        $totalProjects     = Project::count();
        $draftProjects     = Project::where('status', 'draft')->count();
        $activeProjects    = Project::where('status', 'active')->count();
        $completedProjects = Project::where('status', 'completed')->count();

        $planCounts = User::selectRaw("COALESCE(plan, 'Free') as plan, COUNT(*) as cnt")
            ->groupBy('plan')
            ->pluck('cnt', 'plan');

        $planDist = $planCounts->map(fn($count, $plan) => [
            'name'       => $plan,
            'percentage' => $totalUsers > 0 ? round(($count / $totalUsers) * 100) : 0,
        ])->values();

        $topMaterials = Material::select('name')->limit(5)->get()
            ->map(fn($m) => ['name' => $m->name, 'count' => 1])->values();

        $chartUsers = collect(range(5, 0))->map(function ($i) {
            $d = now()->subMonths($i);
            return [
                'label' => $d->format('M'),
                'count' => User::whereYear('created_at', $d->year)->whereMonth('created_at', $d->month)->count(),
            ];
        });

        $chartProjects = collect(range(5, 0))->map(function ($i) {
            $d = now()->subMonths($i);
            return [
                'label' => $d->format('M'),
                'count' => Project::whereYear('created_at', $d->year)->whereMonth('created_at', $d->month)->count(),
            ];
        });

        $usersGrowth = $usersLastMonth > 0
            ? round((($usersThisMonth - $usersLastMonth) / $usersLastMonth) * 100)
            : 0;

        return response()->json(['data' => [
            'total_users'          => $totalUsers,
            'active_rate'          => $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100) : 0,
            'users_growth'         => $usersGrowth,
            'new_users_this_month' => $usersThisMonth,
            'new_users_last_month' => $usersLastMonth,
            'active_users'         => $activeUsers,
            'inactive_users'       => $inactiveUsers,
            'total_projects'       => $totalProjects,
            'projects_by_status'   => [
                'draft'     => $draftProjects,
                'estimated' => $activeProjects,
                'completed' => $completedProjects,
            ],
            'total_materials'   => Material::count(),
            'total_partners'    => Partner::count(),
            'plan_distribution' => $planDist,
            'top_materials'     => $topMaterials,
            'chart_data'        => [
                'users'    => $chartUsers,
                'projects' => $chartProjects,
            ],
        ]]);
    }

    public function activity()
    {
        $recentUsers = User::select('id', 'username', 'email', 'avatar_path', 'created_at')
            ->latest()
            ->limit(4)
            ->get()
            ->map(fn($u) => [
                'type'       => 'user',
                'initials'   => strtoupper(substr($u->username ?? $u->email ?? 'U', 0, 2)),
                'avatar_url' => $u->avatar_path ? asset('storage/' . $u->avatar_path) : null,
                'user'       => $u->username ?? $u->email,
                'action'     => 'mendaftar sebagai user baru',
                'detail'     => $u->email ?? '',
                'status'     => 'New',
                'time_human' => $u->created_at ? $u->created_at->diffForHumans() : '—',
                '_ts'        => $u->created_at?->toIso8601String() ?? '',
            ]);

        $recentProjects = Project::with('user:id,username,email,avatar_path')
            ->select('id', 'name', 'user_id', 'created_at')
            ->latest()
            ->limit(4)
            ->get()
            ->map(fn($p) => [
                'type'       => 'project',
                'initials'   => strtoupper(substr($p->name ?? 'P', 0, 2)),
                'avatar_url' => $p->user?->avatar_path ? asset('storage/' . $p->user->avatar_path) : null,
                'user'       => $p->user?->username ?? $p->user?->email ?? 'Unknown',
                'action'     => 'membuat project',
                'detail'     => $p->name,
                'status'     => 'Done',
                'time_human' => $p->created_at ? $p->created_at->diffForHumans() : '—',
                '_ts'        => $p->created_at?->toIso8601String() ?? '',
            ]);

        $activities = $recentUsers->concat($recentProjects)
            ->sortByDesc('_ts')
            ->take(8)
            ->map(fn($a) => collect($a)->except('_ts')->all())
            ->values();

        return response()->json(['data' => $activities]);
    }
}
