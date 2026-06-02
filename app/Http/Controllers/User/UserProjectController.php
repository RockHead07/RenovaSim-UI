<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\SupabaseService;
use Illuminate\Http\Request;

class UserProjectController extends Controller
{
    protected SupabaseService $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    /**
     * GET /user/projects
     */
    public function index()
    {
        $userId   = auth()->id();
        $projects = $this->supabase->select('projects', '*', ['user_id' => $userId]);

        $validProjects = [];
        foreach ($projects as $project) {
            $project = is_array($project) ? $project : (array) $project;
            $projectId = $project['id'] ?? null;
            if (!$projectId) continue;

            $estimations = $this->supabase->select('estimations', '*', ['project_id' => $projectId]);
            $project['latest_estimation'] = !empty($estimations) ? $estimations[0] : null;
            $validProjects[] = $project;
        }

        usort($validProjects, fn($a, $b) =>
            strtotime($b['created_at'] ?? '0') - strtotime($a['created_at'] ?? '0')
        );

        return view('user.pages.projects', ['projects' => $validProjects]);
    }

    /**
     * GET /user/projects/{project}
     */
    public function show(int $project)
    {
        $userId    = auth()->id();
        $rows      = $this->supabase->select('projects', '*', ['id' => $project, 'user_id' => $userId]);
        $proj      = $rows[0] ?? null;

        if (!$proj) abort(403);

        $estimations = $this->supabase->select('estimations', '*', ['project_id' => $project]);
        $estimation  = $estimations[0] ?? null;

        $total    = (int) ($estimation['cost_min'] ?? 0);
        $labor    = (int) round($total * 0.4);
        $material = $total - $labor;

        $query = http_build_query([
            'projectName'    => $proj['name'],
            'city'           => $proj['location'] ?? '—',
            'renovationType' => $estimation && isset($estimation['job_type'])
                ? (config('renovasim.job_type_id')[$estimation['job_type']] ?? $estimation['job_type'])
                : 'Renovasi',
            'quality'        => $estimation['quality'] ?? 'Standar',
            'totalCost'      => $total,
            'materialCost'   => $material,
            'laborCost'      => $labor,
            'id'             => $proj['id'],
        ]);

        return redirect(route('user.project-overview') . '?' . $query);
    }

    /**
     * DELETE /user/projects/{project}
     */
    public function destroy(int $project)
    {
        $userId = auth()->id();
        $rows   = $this->supabase->select('projects', 'id,name', ['id' => $project, 'user_id' => $userId]);
        $proj   = $rows[0] ?? null;

        if (!$proj) abort(403);

        $this->supabase->deleteWhere('estimations', ['project_id' => $project]);
        $this->supabase->delete('projects', $project);

        return redirect()->route('user.projects')
            ->with('success', 'Project "' . $proj['name'] . '" berhasil dihapus.');
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

        $user   = auth()->user();
        $userId = $user->getAuthIdentifier();

        // Check if adding to EXISTING project
        $existingProjectId = session('current_project_id');
        $existingProject   = null;
        if ($existingProjectId) {
            $rows = $this->supabase->select('projects', '*', ['id' => $existingProjectId, 'user_id' => $userId]);
            $existingProject = $rows[0] ?? null;
        }

        if ($existingProject) {
            // Plan limit: max_estimations_per_project
            $estRows = $this->supabase->select('estimations', 'id', ['project_id' => $existingProjectId]);
            $currentEstimationCount = count($estRows);
            if ($user->hasReachedLimit('max_estimations_per_project', $currentEstimationCount)) {
                $limit = $user->planLimit('max_estimations_per_project');
                $plan  = $user->activePlan();
                return redirect()->back()->with('error',
                    "Project ini sudah mencapai batas {$limit} estimasi untuk plan {$plan->name}. "
                    . "Upgrade plan untuk menambah lebih banyak estimasi."
                );
            }
            $projectId = $existingProjectId;
        } else {
            // Plan limit: max_projects
            $currentProjects = $this->supabase->select('projects', 'id', ['user_id' => $userId]);
            $currentCount    = count($currentProjects);
            if ($user->hasReachedLimit('max_projects', $currentCount)) {
                $limit = $user->planLimit('max_projects');
                return redirect()->back()->with('error',
                    "Slot project kamu sudah penuh ({$limit} project). Upgrade plan untuk membuat lebih banyak project."
                );
            }

            // Create new project via Supabase
            $projectData = $this->supabase->insert('projects', [
                'user_id'    => $userId,
                'name'       => $setup['project_name'] ?? $result['project_name'] ?? 'Renovasi',
                'room_type'  => $setup['building_type'] ?? 'Lainnya',
                'area_size'  => $setup['area'] ?? ($result['breakdown'][0]['area'] ?? 0),
                'status'     => 'active',
                'total_cost' => (float) ($result['total_range']['min'] ?? 0),
            ]);

            if (!$projectData) {
                return redirect()->back()->with('error', 'Gagal membuat project. Silakan coba lagi.');
            }

            $project   = is_array($projectData) ? ($projectData[0] ?? $projectData) : (array) $projectData;
            $projectId = $project['id'] ?? null;
        }

        if ($projectId) {
            $this->supabase->insert('estimations', [
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
                'fastapi_response' => json_encode($result),
            ]);

            session()->forget(['estimation_result', 'project_setup']);
            session()->put('current_project_id', $projectId);
        }

        $message = $existingProjectId
            ? 'Estimasi berhasil ditambahkan ke project!'
            : 'Estimasi berhasil disimpan!';

        return redirect()->route('user.projects')
            ->with('success', $message);
    }

    /**
     * GET /user/project-overview
     */
    public function showOverview()
    {
        $projectId = session('current_project_id') ?? request()->query('id');

        if (!$projectId) {
            return redirect()->route('user.project.setup')
                ->with('error', 'Tidak ada project aktif. Silakan buat project baru.');
        }

        $rows = $this->supabase->select('projects', '*', ['id' => $projectId]);
        if (empty($rows)) {
            return redirect()->route('user.project.setup')->with('error', 'Project tidak ditemukan.');
        }

        $proj        = (object) $rows[0];
        $estRows     = $this->supabase->select('estimations', '*', ['project_id' => $projectId]);
        $proj->estimations = collect(array_map(fn($e) => (object) $e, $estRows));

        return view('user.pages.project-overview', ['project' => $proj]);
    }

    /**
     * GET /user/project/{id}/view
     */
    public function viewProject(int $id)
    {
        $userId = auth()->id();
        $rows   = $this->supabase->select('projects', 'id', ['id' => $id, 'user_id' => $userId]);
        if (empty($rows)) abort(403);

        session()->put('current_project_id', $id);
        return redirect()->route('user.project-overview');
    }

    /**
     * GET /user/project/{id}/add-estimation
     */
    public function addEstimation(int $id)
    {
        $userId = auth()->id();
        $rows   = $this->supabase->select('projects', '*', ['id' => $id, 'user_id' => $userId]);
        if (empty($rows)) abort(403);

        $proj = $rows[0];
        session()->put('current_project_id', $id);

        $estRows = $this->supabase->select('estimations', '*', ['project_id' => $id]);
        $lastEst = !empty($estRows) ? $estRows[0] : null;

        session()->put('project_setup', [
            'project_name'  => $proj['name'],
            'building_type' => $proj['building_type'] ?? null,
            'location'      => $proj['location'] ?? null,
            'description'   => $proj['description'] ?? null,
            'area'          => $lastEst['area'] ?? null,
        ]);

        return redirect()->route('user.estimation.wizard');
    }
}
