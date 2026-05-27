<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Estimation;
use App\Models\Project;
use Illuminate\Http\Request;

class UserProjectController extends Controller
{
    /**
     * GET /user/projects
     */
    public function index()
    {
        $projects = Project::where('user_id', auth()->id())
            ->with(['estimations' => fn($q) => $q->latest()->limit(1)])
            ->latest()
            ->get();

        return view('user.pages.projects', compact('projects'));
    }

    /**
     * GET /user/projects/{project}
     */
    public function show(Project $project)
    {
        abort_if($project->user_id !== auth()->id(), 403);

        $estimation = $project->estimations()->latest()->first();

        $total    = (int) ($estimation?->cost_min ?? 0);
        $labor    = (int) round($total * 0.4);
        $material = $total - $labor;

        $query = http_build_query([
            'projectName'    => $project->name,
            'city'           => $project->location ?? '—',
            'renovationType' => $estimation?->job_type
                ? (config('renovasim.job_type_id')[$estimation->job_type] ?? $estimation->job_type)
                : 'Renovasi',
            'quality'        => $estimation?->quality ?? 'Standar',
            'totalCost'      => $total,
            'materialCost'   => $material,
            'laborCost'      => $labor,
            'id'             => $project->id,
        ]);

        return redirect(route('user.project-overview') . '?' . $query);
    }

    /**
     * DELETE /user/projects/{project}
     */
    public function destroy(Project $project)
    {
        abort_if($project->user_id !== auth()->id(), 403);

        $project->estimations()->delete();
        $project->delete();

        return redirect()->route('user.projects')
            ->with('success', 'Project "' . $project->name . '" berhasil dihapus.');
    }

    /**
     * POST /user/project/save-estimation
     */
    public function saveEstimation(Request $request)
    {
        $result = session('estimation_result');
        $setup  = session('project_setup');

        if (!$result) {
            return redirect()->route('user.estimation.wizard')
                ->with('error', 'Sesi estimasi tidak ditemukan. Silakan mulai ulang.');
        }

        $user = auth()->user();

        $project = Project::create([
            'user_id'           => $user->id,
            'name'              => $setup['project_name'] ?? $result['project_name'] ?? 'Renovasi',
            'building_type'     => $setup['building_type'] ?? null,
            'location'          => $setup['location'] ?? $result['location'] ?? null,
            'description'       => $setup['description'] ?? null,
            'status'            => 'active',
            'total_cost'        => 0,
            'estimations_count' => 0,
        ]);

        Estimation::create([
            'project_id'       => $project->id,
            'user_id'          => $user->id,
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

        $project->recalculateTotals();

        session()->forget(['estimation_result', 'project_setup']);
        session()->put('current_project_id', $project->id);

        return redirect()->route('user.projects')
            ->with('success', 'Estimasi berhasil disimpan! Klik project untuk melihat detail.');
    }

    /**
     * GET /user/project-overview
     * Load current project from session and pass to view.
     */
    public function showOverview()
    {
        $projectId = session('current_project_id');

        if (!$projectId) {
            return redirect()->route('user.project.setup')
                ->with('error', 'Tidak ada project aktif. Silakan buat project baru.');
        }

        $project = Project::with('estimations')->findOrFail($projectId);

        return view('user.pages.project-overview', compact('project'));
    }

    /**
     * GET /user/project/{id}/view
     * Set session current_project_id and redirect to project overview.
     */
    public function viewProject(int $id)
    {
        $project = Project::where('user_id', auth()->id())->findOrFail($id);
        session()->put('current_project_id', $project->id);
        return redirect()->route('user.project-overview');
    }

    /**
     * GET /user/project/{id}/add-estimation
     * Set existing project as active context and redirect to estimation wizard.
     */
    public function addEstimation(int $id)
    {
        $project = Project::where('user_id', auth()->id())->findOrFail($id);

        session()->put('current_project_id', $project->id);
        session()->put('project_setup', [
            'project_name'  => $project->name,
            'building_type' => $project->building_type,
            'location'      => $project->location,
            'description'   => $project->description,
        ]);

        return redirect()->route('user.estimation.wizard');
    }
}
