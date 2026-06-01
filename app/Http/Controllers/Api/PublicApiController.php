<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SupabaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicApiController extends Controller
{
    public function __construct(protected SupabaseService $supabase) {}

    // ─── Auth ────────────────────────────────────────────────────────
    private function authenticate(Request $request): bool
    {
        $token  = $request->bearerToken();
        $apiKey = env('RENOVASIM_API_KEY', '');
        return $apiKey !== '' && $token === $apiKey;
    }

    private function unauthorized(): JsonResponse
    {
        return response()->json(['error' => 'Unauthorized. Provide a valid API key as Bearer token.'], 401);
    }

    // ─── Endpoints ───────────────────────────────────────────────────
    public function users(Request $request): JsonResponse
    {
        if (!$this->authenticate($request)) return $this->unauthorized();

        $rows = $this->supabase->select('users', 'id,username,email,role,plan,account_status,created_at');
        return response()->json(['data' => $rows, 'total' => count($rows)]);
    }

    public function user(Request $request, int $id): JsonResponse
    {
        if (!$this->authenticate($request)) return $this->unauthorized();

        $rows = $this->supabase->select('users', 'id,username,email,role,plan,account_status,created_at', ['id' => $id]);
        if (empty($rows)) return response()->json(['error' => 'User not found'], 404);
        return response()->json(['data' => $rows[0]]);
    }

    public function projects(Request $request): JsonResponse
    {
        if (!$this->authenticate($request)) return $this->unauthorized();

        $userId = $request->query('user_id');
        $rows   = $userId
            ? $this->supabase->select('projects', '*', ['user_id' => $userId])
            : $this->supabase->select('projects', '*');
        return response()->json(['data' => $rows, 'total' => count($rows)]);
    }

    public function estimations(Request $request): JsonResponse
    {
        if (!$this->authenticate($request)) return $this->unauthorized();

        $projectId = $request->query('project_id');
        $userId    = $request->query('user_id');

        $filters = [];
        if ($projectId) $filters['project_id'] = $projectId;
        if ($userId)    $filters['user_id']    = $userId;

        $rows = $this->supabase->select('estimations', '*', $filters);
        return response()->json(['data' => $rows, 'total' => count($rows)]);
    }

    public function materials(Request $request): JsonResponse
    {
        if (!$this->authenticate($request)) return $this->unauthorized();

        $rows = $this->supabase->select('materials', '*');
        usort($rows, fn($a, $b) => strcmp($a['category'] ?? '', $b['category'] ?? ''));
        return response()->json(['data' => $rows, 'total' => count($rows)]);
    }

    public function partners(Request $request): JsonResponse
    {
        if (!$this->authenticate($request)) return $this->unauthorized();

        $rows = $this->supabase->select('partners', 'id,name,logo,logo_image,order,is_active', ['is_active' => true]);
        usort($rows, fn($a, $b) => ($a['order'] ?? 0) - ($b['order'] ?? 0));
        return response()->json(['data' => $rows, 'total' => count($rows)]);
    }

    public function pricingPlans(Request $request): JsonResponse
    {
        if (!$this->authenticate($request)) return $this->unauthorized();

        $plans = $this->supabase->select('pricing_plans', '*');
        $result = array_map(function ($p) {
            $p['features'] = $this->supabase->select('plan_features', '*', ['pricing_plan_id' => $p['id']]);
            return $p;
        }, $plans);

        return response()->json(['data' => $result, 'total' => count($result)]);
    }
}
