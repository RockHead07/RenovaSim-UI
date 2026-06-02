<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SupabaseService;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function __construct(protected SupabaseService $supabase) {}

    public function index()
    {
        return view('admin.dashboard');
    }

    public function metrics()
    {
        $users    = $this->supabase->select('users',    'id,account_status,plan,created_at');
        $projects = $this->supabase->select('projects', 'id,status,created_at');
        $materials = $this->supabase->select('materials', 'id,name');
        $partners  = $this->supabase->select('partners',  'id');

        $now       = now();
        $thisMonth = $now->month;
        $thisYear  = $now->year;
        $lastMonth = $now->copy()->subMonth();

        $totalUsers     = count($users);
        $activeUsers    = count(array_filter($users, fn($u) => ($u['account_status'] ?? 'active') === 'active'));
        $inactiveUsers  = $totalUsers - $activeUsers;
        $usersThisMonth = count(array_filter($users, fn($u) => $this->inMonth($u['created_at'] ?? null, $thisYear, $thisMonth)));
        $usersLastMonth = count(array_filter($users, fn($u) => $this->inMonth($u['created_at'] ?? null, $lastMonth->year, $lastMonth->month)));

        $totalProjects     = count($projects);
        $draftProjects     = count(array_filter($projects, fn($p) => ($p['status'] ?? '') === 'draft'));
        $activeProjects    = count(array_filter($projects, fn($p) => ($p['status'] ?? '') === 'active'));
        $completedProjects = count(array_filter($projects, fn($p) => ($p['status'] ?? '') === 'completed'));

        $planCounts = [];
        foreach ($users as $u) {
            $plan = $u['plan'] ?? 'Free';
            $planCounts[$plan] = ($planCounts[$plan] ?? 0) + 1;
        }
        $planDist = collect($planCounts)->map(fn($count, $plan) => [
            'name'       => $plan,
            'percentage' => $totalUsers > 0 ? round(($count / $totalUsers) * 100) : 0,
        ])->values();

        $topMaterials = collect(array_slice($materials, 0, 5))->map(fn($m) => [
            'name' => $m['name'], 'count' => 1,
        ])->values();

        $chartUsers = collect(range(5, 0))->map(function ($i) use ($users) {
            $d = now()->subMonths($i);
            return ['label' => $d->format('M'), 'count' => count(array_filter($users, fn($u) => $this->inMonth($u['created_at'] ?? null, $d->year, $d->month)))];
        });

        $chartProjects = collect(range(5, 0))->map(function ($i) use ($projects) {
            $d = now()->subMonths($i);
            return ['label' => $d->format('M'), 'count' => count(array_filter($projects, fn($p) => $this->inMonth($p['created_at'] ?? null, $d->year, $d->month)))];
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
            'total_materials'      => count($materials),
            'total_partners'       => count($partners),
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
        $users    = $this->supabase->select('users',    'id,username,email,avatar_path,created_at');
        $projects = $this->supabase->select('projects', 'id,name,user_id,created_at');

        $userMap = [];
        foreach ($users as $u) {
            $userMap[$u['id']] = $u;
        }

        usort($users,    fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        usort($projects, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

        $recentUsers = collect(array_slice($users, 0, 4))->map(fn($u) => [
            'type'       => 'user',
            'initials'   => strtoupper(substr($u['username'] ?? $u['email'] ?? 'U', 0, 2)),
            'avatar_url' => !empty($u['avatar_path']) ? asset('storage/' . $u['avatar_path']) : null,
            'user'       => $u['username'] ?? $u['email'],
            'action'     => 'mendaftar sebagai user baru',
            'detail'     => $u['email'] ?? '',
            'status'     => 'New',
            'time_human' => $u['created_at'] ? Carbon::parse($u['created_at'])->diffForHumans() : '—',
            '_ts'        => $u['created_at'] ?? '',
        ]);

        $recentProjects = collect(array_slice($projects, 0, 4))->map(fn($p) => [
            'type'       => 'project',
            'initials'   => strtoupper(substr($p['name'] ?? 'P', 0, 2)),
            'avatar_url' => !empty($userMap[$p['user_id'] ?? '']['avatar_path'])
                            ? asset('storage/' . $userMap[$p['user_id']]['avatar_path'])
                            : null,
            'user'       => $userMap[$p['user_id'] ?? '']['username'] ?? $userMap[$p['user_id'] ?? '']['email'] ?? 'Unknown',
            'action'     => 'membuat project',
            'detail'     => $p['name'],
            'status'     => 'Done',
            'time_human' => $p['created_at'] ? Carbon::parse($p['created_at'])->diffForHumans() : '—',
            '_ts'        => $p['created_at'] ?? '',
        ]);

        $activities = $recentUsers->concat($recentProjects)
            ->sortByDesc('_ts')
            ->take(8)
            ->map(fn($a) => collect($a)->except('_ts')->all())
            ->values();

        return response()->json(['data' => $activities]);
    }

    private function inMonth(?string $date, int $year, int $month): bool
    {
        if (!$date) return false;
        try {
            $d = Carbon::parse($date);
            return $d->year === $year && $d->month === $month;
        } catch (\Throwable) {
            return false;
        }
    }
}
