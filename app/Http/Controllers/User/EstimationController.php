<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\EstimationService;
use Illuminate\Http\Request;
use RuntimeException;

class EstimationController extends Controller
{
    protected EstimationService $estimationService;

    public function __construct(EstimationService $estimationService)
    {
        $this->estimationService = $estimationService;
    }

    /**
     * GET /user/ai-estimation — show the wizard / AI mode selection page
     */
    public function showWizard()
    {
        return view('user.pages.ai-estimation', [
            'renovationTypes' => config('renovasim.renovation_types'),
            'qualities'       => config('renovasim.qualities'),
            'cities'          => config('renovasim.cities'),
            'jobTypeMap'      => config('renovasim.job_type_id'),
        ]);
    }

    /**
     * GET /user/estimation-result — show estimation result from session
     */
    public function showResult()
    {
        return view('user.pages.estimation-result', [
            'result' => session('estimation_result'),
        ]);
    }

    /**
     * POST /user/ai-estimation/wizard — submit structured wizard form
     */
    public function submitWizard(Request $request)
    {
        set_time_limit(300);
        $validated = $request->validate([
            'job_type'    => 'required|string',
            'area'        => 'required|numeric|min:1',
            'location'    => 'nullable|string',
            'quality'     => 'nullable|string',
            'budget'      => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $budget = preg_replace('/[^0-9]/', '', $request->input('budget', '0'));

        try {
            $result = $this->estimationService->estimateWizard([
                'project_name' => session('project_setup.project_name', $request->input('project_name', 'Renovasi')),
                'job_type'     => $validated['job_type'],
                'area'         => $validated['area'],
                'location'     => ($validated['location'] ?: session('project_setup.location')) ?: (auth()->user()->default_location ?: 'jakarta'),
                'quality'      => $validated['quality'] ?? 'standar',
                'scope'        => $request->input('scope', 'full'),
                'budget'       => (int) $budget,
                'description'  => $validated['description'] ?? '',
            ]);

            session()->put('estimation_result', $result);
            return redirect()->route('user.estimation.result');
        } catch (RuntimeException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * POST /user/ai-estimation/ai — submit free-text AI prompt
     */
    public function submitAI(Request $request)
    {
        set_time_limit(300);
        $validated = $request->validate([
            'description' => 'required|string|min:10',
            'location'    => 'nullable|string',
            'budget'      => 'nullable|string',
        ]);

        $budget = preg_replace('/[^0-9]/', '', $request->input('budget', '0'));

        try {
            $result = $this->estimationService->estimateAI([
                'project_name' => session('project_setup.project_name', 'Proyek Renovasi'),
                'description'  => $validated['description'],
                'location'     => ($validated['location'] ?: session('project_setup.location')) ?: (auth()->user()->default_location ?: 'jakarta'),
                'budget'       => (int) $budget,
                'area_hint'    => session('project_setup.area'),
            ]);

            session()->put('estimation_result', $result);
            return redirect()->route('user.estimation.result');
        } catch (RuntimeException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * GET /user/estimation-result/refine — show refine view (using same result page)
     */
    public function showRefine()
    {
        $result = session('estimation_result');
        if (!$result) {
            return redirect()->route('user.estimation.wizard');
        }
        return view('user.pages.estimation-result', compact('result'));
    }

    /**
     * POST /user/estimation-result/refine — submit refine corrections
     */
    public function submitRefine(Request $request)
    {
        set_time_limit(120);
        $request->validate([
            'area'     => 'nullable|numeric|min:1',
            'quality'  => 'nullable|string',
            'location' => 'nullable|string',
            'scope'    => 'nullable|string',
            'job_type' => 'nullable|string',
            'budget'   => 'nullable|string',
        ]);

        $previousResult = session('estimation_result');
        if (!$previousResult) {
            return redirect()->route('user.estimation.wizard')
                ->with('error', 'Sesi estimasi tidak ditemukan. Silakan mulai ulang.');
        }

        // Clean budget field (strip IDR formatting)
        $budget = preg_replace('/[^0-9]/', '', $request->input('budget', ''));
        $budget = $budget && (int)$budget > 0 ? (int)$budget : null;

        $corrections = array_filter([
            'area'     => $request->input('area') ? (float)$request->input('area') : null,
            'quality'  => $request->input('quality'),
            'location' => $request->input('location') ?: null,
            'scope'    => $request->input('scope'),
            'job_type' => $request->input('job_type'),
            'budget'   => $budget,
        ], fn($v) => $v !== null && $v !== '');

        try {
            $result = $this->estimationService->refine($previousResult, $corrections);
            session()->put('estimation_result', $result);
            return redirect()->route('user.estimation.result');
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * GET /user/project/create — show project setup form
     */
    public function showProjectSetup()
    {
        return view('user.pages.project-setup');
    }

    /**
     * POST /user/project/create — store project info in session, redirect to estimation wizard
     */
    public function storeProjectSetup(Request $request)
    {
        $request->validate([
            'project_name'  => 'required|string|max:255',
            'building_type' => 'nullable|string|max:50',
            'location'      => 'nullable|string|max:100',
            'description'   => 'nullable|string|max:1000',
        ]);

        // Store project context in session
        session()->put('project_setup', [
            'project_name'  => $request->input('project_name'),
            'building_type' => $request->input('building_type'),
            'location'      => $request->input('location'),
            'description'   => $request->input('description'),
        ]);

        return redirect()->route('user.estimation.wizard');
    }

    /**
     * GET /user/estimation/start — show context selection page
     */
    public function showStart()
    {
        $projects = \App\Models\Project::where('user_id', auth()->id())
                        ->withCount('estimations')
                        ->latest()
                        ->get();

        return view('user.pages.estimation-start', compact('projects'));
    }

    /**
     * GET /user/estimation/quick — clear project session and go to wizard
     */
    public function quickEstimation()
    {
        session()->forget(['current_project_id', 'project_setup']);
        return redirect()->route('user.estimation.wizard');
    }
}
