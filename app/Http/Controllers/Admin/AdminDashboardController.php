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

        // Plan distribution
        $planCounts = [];
        foreach ($users as $u) {
            $plan = $u['plan'] ?? 'Free';
            $planCounts[$plan] = ($planCounts[$plan] ?? 0) + 1;
        }
        $planDist = collect($planCounts)->map(fn($count, $plan) => [
            'name'       => $plan,
            'percentage' => $totalUsers > 0 ? round(($count / $totalUsers) * 100) : 0,
        ])->values();

        // Top materials
        $topMaterials = collect(array_slice($materials, 0, 5))->map(fn($m) => [
            'name' => $m['name'], 'count' => 1,
        ])->values();

        // Chart data — last 6 months
        $chartUsers = collect(range(5, 0))->map(function ($i) use ($users) {
            $d = now()->subMonths($i);
            return ['label' => $d->format('M'), 'count' => count(array_filter($users, fn($u) => $this->inMonth($u['created_at'] ?? null, $d->year, $d->month)))];
        });

        $chartProjects = collect(range(5, 0))->map(function ($i) use ($projects) {
            $d = now()->subMonths($i);
            return ['label' => $d->format('M'), 'count' => count(array_filter($projects, fn($p) => $this->inMonth($p['created_at'] ?? null, $d->year, $d->month)))];
        });

        return response()->json(['data' => [
            'total_users'        => $totalUsers,
            'active_rate'        => $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100) : 0,
            'users_this_month'   => $usersThisMonth,
            'users_last_month'   => $usersLastMonth,
            'active_users'       => $activeUsers,
            'inactive_users'     => $inactiveUsers,
            'total_projects'     => $totalProjects,
            'draft_projects'     => $draftProjects,
            'estimated_projects' => $activeProjects,
            'completed_projects' => $completedProjects,
            'total_materials'    => count($materials),
            'total_partners'     => count($partners),
            'plan_distribution'  => $planDist,
            'top_materials'      => $topMaterials,
            'chart_data'         => ['users' => $chartUsers, 'projects' => $chartProjects],
        ]]);
    }

    public function activity()
    {
        $users    = $this->supabase->select('users',    'id,username,email,created_at');
        $projects = $this->supabase->select('projects', 'id,name,user_id,created_at');

        // Build user lookup map
        $userMap = [];
        foreach ($users as $u) {
            $userMap[$u['id']] = $u['username'] ?? $u['email'] ?? "#{$u['id']}";
        }

        usort($users,    fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        usort($projects, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

        $recentUsers = collect(array_slice($users, 0, 5))->map(fn($u) => [
            'type'    => 'user',
            'message' => 'User baru terdaftar: ' . ($u['username'] ?? $u['email']),
            'time'    => $u['created_at'] ? Carbon::parse($u['created_at'])->diffForHumans() : '—',
        ]);

        $recentProjects = collect(array_slice($projects, 0, 5))->map(fn($p) => [
            'type'    => 'project',
            'message' => 'Project dibuat: ' . $p['name'] . ' oleh ' . ($userMap[$p['user_id']] ?? "user #{$p['user_id']}"),
            'time'    => $p['created_at'] ? Carbon::parse($p['created_at'])->diffForHumans() : '—',
        ]);

        $activities = $recentUsers->concat($recentProjects)->take(10)->values();

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
