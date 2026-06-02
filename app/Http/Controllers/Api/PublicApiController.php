<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Estimation;
use App\Models\Material;
use App\Models\Partner;
use App\Models\PricingPlan;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicApiController extends Controller
{
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

    public function users(Request $request): JsonResponse
    {
        if (!$this->authenticate($request)) return $this->unauthorized();

        $rows = User::select('id', 'username', 'email', 'role', 'plan', 'account_status', 'created_at')->get();
        return response()->json(['data' => $rows, 'total' => $rows->count()]);
    }

    public function user(Request $request, int $id): JsonResponse
    {
        if (!$this->authenticate($request)) return $this->unauthorized();

        $user = User::select('id', 'username', 'email', 'role', 'plan', 'account_status', 'created_at')->find($id);
        if (!$user) return response()->json(['error' => 'User not found'], 404);
        return response()->json(['data' => $user]);
    }

    public function projects(Request $request): JsonResponse
    {
        if (!$this->authenticate($request)) return $this->unauthorized();

        $query = Project::query();
        if ($request->filled('user_id')) $query->where('user_id', $request->query('user_id'));
        $rows = $query->get();
        return response()->json(['data' => $rows, 'total' => $rows->count()]);
    }

    public function estimations(Request $request): JsonResponse
    {
        if (!$this->authenticate($request)) return $this->unauthorized();

        $query = Estimation::query();
        if ($request->filled('project_id')) $query->where('project_id', $request->query('project_id'));
        if ($request->filled('user_id'))    $query->where('user_id', $request->query('user_id'));
        $rows = $query->get();
        return response()->json(['data' => $rows, 'total' => $rows->count()]);
    }

    public function materials(Request $request): JsonResponse
    {
        if (!$this->authenticate($request)) return $this->unauthorized();

        $rows = Material::orderBy('category')->get();
        return response()->json(['data' => $rows, 'total' => $rows->count()]);
    }

    public function partners(Request $request): JsonResponse
    {
        if (!$this->authenticate($request)) return $this->unauthorized();

        $rows = Partner::select('id', 'name', 'logo', 'logo_image', 'order', 'is_active')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
        return response()->json(['data' => $rows, 'total' => $rows->count()]);
    }

    public function pricingPlans(Request $request): JsonResponse
    {
        if (!$this->authenticate($request)) return $this->unauthorized();

        $plans = PricingPlan::with('features')->get();
        return response()->json(['data' => $plans, 'total' => $plans->count()]);
    }
}
