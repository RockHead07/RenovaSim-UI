<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\RabShare;
use App\Exports\RabExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class RabController extends Controller
{
    /**
     * Build merged RAB data from all estimations in a project.
     */
    public function buildRabData(Project $project): array
    {
        $jobTypeLabels = Cache::remember('renovasim_job_type_id', 3600, fn () => config('renovasim.job_type_id', []));

        $estimations = $project->estimations()->get();
        $jobGroups   = [];

        foreach ($estimations as $estimation) {
            $response = $estimation->fastapi_response;
            // Handle string (not yet decoded)
            if (is_string($response)) {
                $response = json_decode($response, true);
            }
            if (!is_array($response) || empty($response['breakdown'])) {
                continue;
            }

            foreach ($response['breakdown'] as $item) {
                $jobType = $item['job_type'];
                $area    = (float) ($item['area'] ?? 0);
                $min     = (float) ($item['min'] ?? 0);
                $max     = (float) ($item['max'] ?? 0);

                if (!isset($jobGroups[$jobType])) {
                    $jobGroups[$jobType] = [
                        'job_type'   => $jobType,
                        'label'      => $jobTypeLabels[$jobType] ?? ucfirst($jobType),
                        'total_area' => 0,
                        'total_min'  => 0,
                        'total_max'  => 0,
                    ];
                }

                $jobGroups[$jobType]['total_area'] += $area;
                $jobGroups[$jobType]['total_min']  += $min;
                $jobGroups[$jobType]['total_max']  += $max;
            }
        }

        $rab      = [];
        $grandMin = 0;
        $grandMax = 0;

        foreach ($jobGroups as $group) {
            $area         = $group['total_area'];
            $unitPriceMin = $area > 0 ? round($group['total_min'] / $area) : 0;
            $unitPriceMax = $area > 0 ? round($group['total_max'] / $area) : 0;

            $rab[] = [
                'job_type'       => $group['job_type'],
                'label'          => $group['label'],
                'area'           => $area,
                'unit_price_min' => $unitPriceMin,
                'unit_price_max' => $unitPriceMax,
                'total_min'      => $group['total_min'],
                'total_max'      => $group['total_max'],
            ];

            $grandMin += $group['total_min'];
            $grandMax += $group['total_max'];
        }

        return [
            'items'     => $rab,
            'grand_min' => $grandMin,
            'grand_max' => $grandMax,
        ];
    }

    /**
     * GET /user/project/{id}/rab — RAB page (authenticated)
     */
    public function show(int $id)
    {
        $project = Project::where('user_id', auth()->id())
                          ->with('estimations')
                          ->findOrFail($id);

        $rab   = $this->buildRabData($project);
        $share = RabShare::where('project_id', $project->id)
                         ->where('expires_at', '>', now())
                         ->latest()
                         ->first();

        session()->put('current_project_id', $project->id);

        return view('user.pages.rab', compact('project', 'rab', 'share'));
    }

    /**
     * GET /user/project/{id}/rab/export — Download XLSX
     */
    public function export(int $id)
    {
        $project = Project::where('user_id', auth()->id())
                          ->with('estimations')
                          ->findOrFail($id);

        $rab      = $this->buildRabData($project);
        $filename = 'RAB-' . Str::slug($project->name) . '-' . now()->format('Ymd') . '.xlsx';

        return Excel::download(new RabExport($project, $rab), $filename);
    }

    /**
     * POST /user/project/{id}/rab/share — Generate share link
     */
    public function generateShare(Request $request, int $id)
    {
        $project = Project::where('user_id', auth()->id())->findOrFail($id);

        $request->validate([
            'visibility' => 'required|in:public,private',
        ]);

        // Invalidate old shares for this project
        RabShare::where('project_id', $project->id)->delete();

        $share = RabShare::create([
            'project_id' => $project->id,
            'user_id'    => auth()->id(),
            'token'      => Str::random(32),
            'visibility' => $request->visibility,
            'expires_at' => now()->addDays(7),
        ]);

        return response()->json([
            'url'        => route('rab.public', $share->token),
            'expires_at' => $share->expires_at->format('d M Y'),
            'visibility' => $share->visibility,
        ]);
    }

    /**
     * GET /rab/{token} — Public RAB view (no auth required)
     */
    public function publicView(string $token)
    {
        $share = RabShare::where('token', $token)->firstOrFail();

        if ($share->isExpired()) {
            abort(410, 'Link RAB ini sudah kadaluarsa.');
        }

        $project = Project::with('estimations')->findOrFail($share->project_id);
        $rab     = $this->buildRabData($project);
        $owner   = $share->visibility === 'public' ? $project->user : null;

        return view('user.pages.rab-public', compact('project', 'rab', 'share', 'owner'));
    }
}
