<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Partner;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function metrics()
    {
        $totalUsers      = User::count();
        $usersThisMonth  = User::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $usersLastMonth  = User::whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year)->count();
        $activeUsers     = User::where('account_status', 'active')->count();
        $inactiveUsers   = User::where('account_status', '!=', 'active')->count();

        $totalProjects     = Project::count();
        $draftProjects     = Project::where('status', 'draft')->count();
        $activeProjects    = Project::where('status', 'active')->count();
        $completedProjects = Project::where('status', 'completed')->count();

        $totalMaterials = Material::count();
        $totalPartners  = Partner::count();

        // Plan distribution
        $planDist = User::selectRaw('COALESCE(plan, \'Free\') as plan, count(*) as count')
            ->groupBy('plan')
            ->get()
            ->map(fn($p) => [
                'name'       => $p->plan,
                'percentage' => $totalUsers > 0 ? round(($p->count / $totalUsers) * 100) : 0,
            ]);

        // Top materials
        $topMaterials = Material::take(5)->get()->map(fn($m) => [
            'name'  => $m->name,
            'count' => 1,
        ]);

        // Chart data — last 6 months
        $chartUsers = collect(range(5, 0))->map(function ($i) {
            $date = now()->subMonths($i);
            return [
                'label' => $date->format('M'),
                'count' => User::whereYear('created_at', $date->year)
                               ->whereMonth('created_at', $date->month)
                               ->count(),
            ];
        });

        $chartProjects = collect(range(5, 0))->map(function ($i) {
            $date = now()->subMonths($i);
            return [
                'label' => $date->format('M'),
                'count' => Project::whereYear('created_at', $date->year)
                                  ->whereMonth('created_at', $date->month)
                                  ->count(),
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
            'total_materials'      => $totalMaterials,
            'total_partners'       => $totalPartners,
            'plan_distribution'    => $planDist,
            'top_materials'        => $topMaterials,
            'chart_data'           => [
                'users'    => $chartUsers,
                'projects' => $chartProjects,
            ],
        ]]);
    }

    public function activity()
    {
        $recentUsers = User::latest()->take(4)->get()->map(fn($u) => [
            'type'       => 'user',
            'initials'   => strtoupper(substr($u->username ?? $u->email ?? 'U', 0, 2)),
            'avatar_url' => $u->avatar_path ? asset('storage/' . $u->avatar_path) : null,
            'user'       => $u->username ?? $u->email,
            'action'     => 'mendaftar sebagai user baru',
            'detail'     => $u->email,
            'status'     => 'New',
            'time_human' => $u->created_at->diffForHumans(),
        ]);

        $recentProjects = Project::with('user')->latest()->take(4)->get()->map(fn($p) => [
            'type'       => 'project',
            'initials'   => strtoupper(substr($p->name ?? 'P', 0, 2)),
            'avatar_url' => $p->user?->avatar_path ? asset('storage/' . $p->user->avatar_path) : null,
            'user'       => $p->user?->username ?? $p->user?->email ?? 'Unknown',
            'action'     => 'membuat project',
            'detail'     => $p->name . ' — ' . ($p->location ?? 'lokasi tidak diset'),
            'status'     => 'Done',
            'time_human' => $p->created_at->diffForHumans(),
        ]);

        $activities = $recentUsers->concat($recentProjects)
            ->sortByDesc(fn($a) => $a['time_human'])
            ->take(8)
            ->values();

        return response()->json(['data' => $activities]);
    }
}
