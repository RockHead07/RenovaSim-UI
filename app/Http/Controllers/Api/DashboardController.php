<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Partner;
use App\Models\PricingPlan;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function metrics(): JsonResponse
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // User counts
        $totalUsers = User::count();
        $activeUsers = User::where('account_status', 'active')->count();
        $inactiveUsers = User::where('account_status', 'inactive')->count();
        $suspendedUsers = User::where('account_status', 'suspended')->count();
        $newUsersThisMonth = User::where('created_at', '>=', $startOfMonth)->count();
        $newUsersLastMonth = User::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();

        // Active rate percentage
        $activeRate = $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100) : 0;

        // Users growth percentage vs last month
        $usersGrowth = $newUsersLastMonth > 0
            ? round((($newUsersThisMonth - $newUsersLastMonth) / $newUsersLastMonth) * 100)
            : ($newUsersThisMonth > 0 ? 100 : 0);

        // Project counts
        $totalProjects = Project::count();
        $projectsByStatus = [
            'draft'     => Project::where('status', 'draft')->count(),
            'estimated' => Project::where('status', 'estimated')->count(),
            'completed' => Project::where('status', 'completed')->count(),
        ];

        // Other counts
        $totalMaterials = Material::count();
        $totalPartners = Partner::where('is_active', true)->count();

        // Plan distribution
        $planDistribution = PricingPlan::where('is_active', true)
            ->withCount('features')
            ->get()
            ->map(function ($plan) use ($totalUsers) {
                $userCount = User::where('pricing_plan_id', $plan->id)->count();
                return [
                    'name'       => $plan->name,
                    'slug'       => $plan->slug,
                    'user_count' => $userCount,
                    'percentage' => $totalUsers > 0 ? round(($userCount / $totalUsers) * 100) : 0,
                ];
            });

        // Monthly user registrations (last 12 months)
        $userChartData = collect(range(11, 0))->map(function ($i) use ($now) {
            $month = $now->copy()->subMonths($i);
            return [
                'label' => $month->format('M'),
                'count' => User::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
            ];
        })->values();

        // Monthly project creation (last 8 months)
        $projectChartData = collect(range(7, 0))->map(function ($i) use ($now) {
            $month = $now->copy()->subMonths($i);
            return [
                'label' => $month->format('M'),
                'count' => Project::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
            ];
        })->values();

        // Top 5 materials by project usage
        $topMaterials = Material::withCount('projects')
            ->orderByDesc('projects_count')
            ->limit(5)
            ->get()
            ->map(fn ($m) => [
                'name'  => $m->name,
                'count' => $m->projects_count,
            ]);

        // Cost distribution (project cost ranges)
        $costDistribution = [
            ['label' => '< 5jt',    'count' => Project::where('total_cost', '<', 5000000)->whereNotNull('total_cost')->count()],
            ['label' => '5-20jt',   'count' => Project::whereBetween('total_cost', [5000000, 20000000])->count()],
            ['label' => '20-50jt',  'count' => Project::whereBetween('total_cost', [20000000, 50000000])->count()],
            ['label' => '> 50jt',   'count' => Project::where('total_cost', '>', 50000000)->count()],
        ];

        return response()->json([
            'status'  => 'success',
            'message' => 'Dashboard metrics retrieved successfully.',
            'data'    => [
                'total_users'          => $totalUsers,
                'active_users'         => $activeUsers,
                'inactive_users'       => $inactiveUsers,
                'suspended_users'      => $suspendedUsers,
                'active_rate'          => $activeRate,
                'new_users_this_month' => $newUsersThisMonth,
                'new_users_last_month' => $newUsersLastMonth,
                'users_growth'         => $usersGrowth,
                'total_projects'       => $totalProjects,
                'projects_by_status'   => $projectsByStatus,
                'total_materials'      => $totalMaterials,
                'total_partners'       => $totalPartners,
                'plan_distribution'    => $planDistribution,
                'chart_data'           => [
                    'users'    => $userChartData,
                    'projects' => $projectChartData,
                ],
                'top_materials'        => $topMaterials,
                'cost_distribution'    => $costDistribution,
            ],
        ]);
    }

    public function activity(): JsonResponse
    {
        // Multi-table merge: latest records from each entity
        $activities = collect()
            ->merge(
                User::latest()->limit(3)->get()->map(fn ($u) => [
                    'type'     => 'user',
                    'initials' => $this->initials($u->username),
                    'user'     => $u->username,
                    'action'   => 'Registered as new user',
                    'detail'   => ucfirst($u->role ?? 'user'),
                    'status'   => $u->account_status === 'active' ? 'Done' : 'In progress',
                    'time'     => $u->created_at,
                ])
            )
            ->merge(
                Project::with('user')->latest()->limit(3)->get()->map(fn ($p) => [
                    'type'     => 'project',
                    'initials' => $this->initials($p->user->username ?? 'N/A'),
                    'user'     => $p->user->username ?? 'N/A',
                    'action'   => 'Created a new project',
                    'detail'   => $p->name,
                    'status'   => $p->status === 'completed' ? 'Done' : 'In progress',
                    'time'     => $p->created_at,
                ])
            )
            ->merge(
                Material::latest()->limit(2)->get()->map(fn ($m) => [
                    'type'     => 'material',
                    'initials' => $this->initials($m->name),
                    'user'     => 'System',
                    'action'   => 'Added new material',
                    'detail'   => $m->name,
                    'status'   => 'Done',
                    'time'     => $m->created_at,
                ])
            )
            ->merge(
                PricingPlan::latest('updated_at')->limit(2)->get()->map(fn ($p) => [
                    'type'     => 'plan',
                    'initials' => $this->initials($p->name),
                    'user'     => 'System',
                    'action'   => 'Updated plan pricing',
                    'detail'   => $p->name,
                    'status'   => $p->is_active ? 'Done' : 'In progress',
                    'time'     => $p->updated_at,
                ])
            )
            ->sortByDesc('time')
            ->take(10)
            ->map(function ($item) {
                $item['time_human'] = Carbon::parse($item['time'])->diffForHumans();
                $item['time'] = Carbon::parse($item['time'])->toISOString();
                return $item;
            })
            ->values();

        return response()->json([
            'status'  => 'success',
            'message' => 'Recent activity retrieved successfully.',
            'data'    => $activities,
        ]);
    }

    private function initials(string $name): string
    {
        $parts = preg_split('/[\s_]+/', trim($name));
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }
        return $initials ?: '??';
    }
}
