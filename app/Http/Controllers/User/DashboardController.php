<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Estimation;
use App\Models\Project;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = Cache::remember('user_stats_'.auth()->id(), 300, function () {
            $userId = auth()->id();

            return [
                'projects_count'    => Project::where('user_id', $userId)->count(),
                'estimations_count' => Estimation::where('user_id', $userId)->count(),
                'total_cost'        => Project::where('user_id', $userId)->sum('total_cost'),
            ];
        });

        return view('user.pages.dashboard', compact('stats'));
    }
}
