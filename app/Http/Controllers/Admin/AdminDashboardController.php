<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Partner;
use App\Models\Project;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        $activeUsers    = User::where(fn ($q) => $q->where('account_status', 'active')->orWhereNull('account_status'))->count();
        $inactiveUsers  = User::where('account_status', 'inactive')->count();
        $suspendedUsers = User::where('account_status', 'suspended')->count();
        $onlineUsers    = User::where('last_active_at', '>=', now()->subMinutes(10))->count();
        $offlineUsers   = $totalUsers - $onlineUsers;
        $usersThisMonth = User::whereYear('created_at', $thisYear)->whereMonth('created_at', $thisMonth)->count();
        $usersLastMonth = User::whereYear('created_at', $lastMonth->year)->whereMonth('created_at', $lastMonth->month)->count();

        $totalProjects     = Project::count();
        $draftProjects     = Project::where('status', 'draft')->count();
        $activeProjects    = Project::where('status', 'estimated')->count();
        $completedProjects = Project::where('status', 'completed')->count();

        $planCounts = User::selectRaw("COALESCE(plan, 'Free') as plan, COUNT(*) as cnt")
            ->groupBy('plan')
            ->pluck('cnt', 'plan');

        $planDist = $planCounts->map(fn($count, $plan) => [
            'name'       => $plan,
            'count'      => $count,
            'percentage' => $totalUsers > 0 ? round(($count / $totalUsers) * 100) : 0,
            'color'      => self::planDistributionColor($plan),
        ])->values();

        $topMaterials = Material::select('name')->limit(5)->get()
            ->map(fn($m) => ['name' => $m->name, 'count' => 1])->values();

        $sixMonthsAgo = now()->subMonths(5)->startOfMonth();

        $chartUsersRaw = DB::table('users')
            ->selectRaw("TO_CHAR(created_at, 'YYYY-MM') as month, COUNT(*) as count")
            ->where('created_at', '>=', $sixMonthsAgo)
            ->groupByRaw("TO_CHAR(created_at, 'YYYY-MM')")
            ->pluck('count', 'month');

        $chartProjectsRaw = DB::table('projects')
            ->selectRaw("TO_CHAR(created_at, 'YYYY-MM') as month, COUNT(*) as count")
            ->where('created_at', '>=', $sixMonthsAgo)
            ->groupByRaw("TO_CHAR(created_at, 'YYYY-MM')")
            ->pluck('count', 'month');

        $chartUsers = collect(range(5, 0))->map(function ($i) use ($chartUsersRaw) {
            $d = now()->subMonths($i);
            return ['label' => $d->format('M'), 'count' => (int) ($chartUsersRaw[$d->format('Y-m')] ?? 0)];
        });

        $chartProjects = collect(range(5, 0))->map(function ($i) use ($chartProjectsRaw) {
            $d = now()->subMonths($i);
            return ['label' => $d->format('M'), 'count' => (int) ($chartProjectsRaw[$d->format('Y-m')] ?? 0)];
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
            'suspended_users'      => $suspendedUsers,
            'online_users'         => $onlineUsers,
            'offline_users'        => $offlineUsers,
            'online_rate'          => $totalUsers > 0 ? round(($onlineUsers / $totalUsers) * 100) : 0,
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

    private static function planDistributionColor(string $plan): string
    {
        $key = strtolower(trim($plan));

        if (str_contains($key, 'enterprise')) {
            return '#facc15'; // yellow-400
        }

        if ($key === 'pro' || str_contains($key, 'pro')) {
            return '#8BA023'; // primary accent
        }

        return '#838383'; // free / default gray
    }

    public function activity()
    {
        $range = request('range', 'all');
        $sort = request('sort', 'desc');
        $order = ($sort === 'asc') ? 'asc' : 'desc';

        $dateThreshold = null;
        if ($range === '12h') {
            $dateThreshold = now()->subHours(12);
        } elseif ($range === '1d') {
            $dateThreshold = now()->subDay();
        } elseif ($range === '3d') {
            $dateThreshold = now()->subDays(3);
        } elseif ($range === '1w') {
            $dateThreshold = now()->subWeek();
        }

        $userQuery = User::select('id', 'username', 'email', 'avatar_path', 'created_at');
        if ($dateThreshold) {
            $userQuery->where('created_at', '>=', $dateThreshold);
        }
        $recentUsers = $userQuery->orderBy('created_at', $order)
            ->limit(8)
            ->get()
            ->map(fn($u) => [
                'type'       => 'user',
                'initials'   => strtoupper(substr($u->username ?? $u->email ?? 'U', 0, 2)),
                'avatar_url' => $u->avatar_url,
                'user'       => $u->username ?? $u->email,
                'action'     => 'mendaftar sebagai user baru',
                'detail'     => $u->email ?? '',
                'status'     => 'New',
                'time_human' => $u->created_at ? $u->created_at->diffForHumans() : '—',
                '_ts'        => $u->created_at?->toIso8601String() ?? '',
            ]);

        $projectQuery = Project::with('user:id,username,email,avatar_path')
            ->select('id', 'name', 'user_id', 'created_at');
        if ($dateThreshold) {
            $projectQuery->where('created_at', '>=', $dateThreshold);
        }
        $recentProjects = $projectQuery->orderBy('created_at', $order)
            ->limit(8)
            ->get()
            ->map(fn($p) => [
                'type'       => 'project',
                'initials'   => strtoupper(substr($p->name ?? 'P', 0, 2)),
                'avatar_url' => $p->user?->avatar_url,
                'user'       => $p->user?->username ?? $p->user?->email ?? 'Unknown',
                'action'     => 'membuat project',
                'detail'     => $p->name,
                'status'     => 'Done',
                'time_human' => $p->created_at ? $p->created_at->diffForHumans() : '—',
                '_ts'        => $p->created_at?->toIso8601String() ?? '',
            ]);

        $roomQuery = Room::with('user:id,username,email,avatar_path')
            ->select('id', 'name', 'user_id', 'created_at', 'recommended_type', 'status');
        if ($dateThreshold) {
            $roomQuery->where('created_at', '>=', $dateThreshold);
        }
        $recentRooms = $roomQuery->orderBy('created_at', $order)
            ->limit(8)
            ->get()
            ->map(fn($r) => [
                'type'       => 'room',
                'initials'   => strtoupper(substr($r->name ?? 'R', 0, 2)),
                'avatar_url' => $r->user?->avatar_url,
                'user'       => $r->user?->username ?? $r->user?->email ?? 'Unknown',
                'action'     => 'membuat 3D design',
                'detail'     => $r->name ?? 'Design 3D',
                'status'     => 'Done',
                'time_human' => $r->created_at ? $r->created_at->diffForHumans() : '—',
                '_ts'        => $r->created_at?->toIso8601String() ?? '',
            ]);

        $activities = $recentUsers->concat($recentProjects)->concat($recentRooms);

        if ($order === 'asc') {
            $activities = $activities->sortBy('_ts');
        } else {
            $activities = $activities->sortByDesc('_ts');
        }

        $activities = $activities->take(8)
            ->map(fn($a) => collect($a)->except('_ts')->all())
            ->values();

        return response()->json(['data' => $activities]);
    }
}
