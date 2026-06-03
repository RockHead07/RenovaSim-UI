<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Estimation;
use App\Models\Project;
use Illuminate\Http\Request;

class UserProjectController extends Controller
{
    public function index()
    {
        $projects = Project::where('user_id', auth()->id())
            ->with(['estimations' => fn ($q) => $q->latest()->limit(1)])
            ->latest()
            ->get()
            ->map(function ($p) {
                $p->latest_estimation = $p->estimations->first();

                return $p;
            });

        return view('user.pages.projects', ['projects' => $projects]);
    }

    public function show(int $project)
    {
        $proj = Project::where('id', $project)->where('user_id', auth()->id())->firstOrFail();

        $estimation = $proj->estimations()->latest()->first();
        $total      = (int) ($estimation?->cost_min ?? 0);
        $labor      = (int) round($total * 0.4);
        $material   = $total - $labor;

        $query = http_build_query([
            'projectName'    => $proj->name,
            'city'           => $proj->location ?? '—',
            'renovationType' => $estimation?->job_type
                ? (config('renovasim.job_type_id')[$estimation->job_type] ?? $estimation->job_type)
                : 'Renovasi',
            'quality'      => $estimation?->quality ?? 'Standar',
            'totalCost'    => $total,
            'materialCost' => $material,
            'laborCost'    => $labor,
            'id'           => $proj->id,
        ]);

        return redirect(route('user.project-overview') . '?' . $query);
    }

    public function destroy(int $project)
    {
        $proj = Project::where('id', $project)->where('user_id', auth()->id())->firstOrFail();
        $proj->estimations()->delete();
        $proj->delete();

        return redirect()->route('user.projects')
            ->with('success', 'Project "' . $proj->name . '" berhasil dihapus.');
    }

    public function saveEstimation(Request $request)
    {
        $result = session('estimation_result');
        $setup  = session('project_setup');

        if (!$result) {
            return redirect()->route('user.estimation.wizard')
                ->with('error', 'Sesi estimasi tidak ditemukan. Silakan mulai ulang.');
        }

        $user   = auth()->user();
        $userId = $user->getAuthIdentifier();

        $existingProjectId = session('current_project_id');
        $existingProject   = null;
        if ($existingProjectId) {
            $existingProject = Project::where('id', $existingProjectId)->where('user_id', $userId)->first();
            if (!$existingProject) {
                $existingProjectId = null;
                session()->forget('current_project_id');
            }
        }

        if ($existingProject) {
            $currentEstimationCount = $existingProject->estimations()->count();
            if ($user->hasReachedLimit('max_estimations_per_project', $currentEstimationCount)) {
                $limit = $user->planLimit('max_estimations_per_project');
                $plan  = $user->activePlan();
                return redirect()->back()->with('error',
                    "Project ini sudah mencapai batas {$limit} estimasi untuk plan {$plan->name}. "
                    . "Upgrade plan untuk menambah lebih banyak estimasi."
                );
            }
            $projectId = $existingProject->id;
        } else {
            $currentCount = Project::where('user_id', $userId)->count();
            if ($user->hasReachedLimit('max_projects', $currentCount)) {
                $limit = $user->planLimit('max_projects');
                return redirect()->back()->with('error',
                    "Slot project kamu sudah penuh ({$limit} project). Upgrade plan untuk membuat lebih banyak project."
                );
            }

            $proj = Project::create([
                'user_id'       => $userId,
                'name'          => $setup['project_name'] ?? $result['project_name'] ?? 'Renovasi',
                'building_type' => $setup['building_type'] ?? null,
                'location'      => $setup['location'] ?? $result['location'] ?? null,
                'description'   => $setup['description'] ?? null,
                'area_size'     => $setup['area'] ?? ($result['breakdown'][0]['area'] ?? 0),
                'status'        => 'estimated',
                'total_cost'    => (float) ($result['total_range']['min'] ?? 0),
            ]);

            $projectId = $proj->id;
        }

        if ($projectId) {
            Estimation::create([
                'project_id'       => $projectId,
                'user_id'          => $userId,
                'label'            => $result['breakdown'][0]['job_type'] ?? 'Estimasi',
                'mode'             => $result['mode'] ?? 'wizard',
                'job_type'         => $result['breakdown'][0]['job_type'] ?? null,
                'area'             => $result['breakdown'][0]['area'] ?? null,
                'location'         => $result['location'] ?? null,
                'quality'          => $result['quality'] ?? null,
                'cost_min'         => $result['total_range']['min'] ?? 0,
                'cost_max'         => $result['total_range']['max'] ?? 0,
                'cost_display'     => $result['total_range']['display'] ?? '-',
                'confidence_score' => $result['confidence']['score'] ?? null,
                'confidence_label' => $result['confidence']['label'] ?? null,
                'fastapi_response' => $result,
            ]);

            Project::find($projectId)->recalculateTotals();

            session()->forget(['estimation_result', 'project_setup', 'current_project_id']);
        }

        $message = $existingProjectId
            ? 'Estimasi berhasil ditambahkan ke project!'
            : 'Estimasi berhasil disimpan!';

        return redirect()->route('user.projects')->with('success', $message);
    }

    public function showOverview()
    {
        $projectId = session('current_project_id') ?? request()->query('id');

        if (!$projectId) {
            return redirect()->route('user.project.setup')
                ->with('error', 'Tidak ada project aktif. Silakan buat project baru.');
        }

        $proj = Project::find($projectId);
        if (!$proj) {
            return redirect()->route('user.project.setup')->with('error', 'Project tidak ditemukan.');
        }

        $proj->load('estimations');
        return view('user.pages.project-overview', ['project' => $proj]);
    }

    public function viewProject(int $id)
    {
        Project::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        session()->put('current_project_id', $id);
        return redirect()->route('user.project-overview');
    }

    public function addEstimation(int $id)
    {
        $proj = Project::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        session()->put('current_project_id', $id);

        $lastEst = $proj->estimations()->latest()->first();

        session()->put('project_setup', [
            'project_name'  => $proj->name,
            'building_type' => $proj->building_type ?? null,
            'location'      => $proj->location ?? null,
            'description'   => $proj->description ?? null,
            'area'          => $lastEst?->area ?? null,
        ]);

        return redirect()->route('user.estimation.wizard');
    }
}
