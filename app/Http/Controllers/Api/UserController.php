<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\PricingPlan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query()->with(['pricingPlan']);

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtering:
        // - New: ?plan=free (slug)
        // - Legacy: ?plan=Free (name)
        // Slug-first, then name fallback. If name is ambiguous -> 422.
        if ($request->filled('plan') && $request->input('plan') !== 'All') {
            $plan = trim((string) $request->input('plan'));

            $resolved = $this->resolvePricingPlanFromPlanParam($plan);
            if ($resolved === 'ambiguous') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Plan filter is ambiguous. Use plan slug.',
                    'data' => null,
                ], 422);
            }

            if ($resolved instanceof PricingPlan) {
                $query->where('pricing_plan_id', $resolved->id);
            } else {
                // Fallback to legacy users.plan for backward compatibility during transition.
                $query->where('plan', $plan);
                logger()->info('Legacy plan filter used (name-based).', ['plan' => $plan]);
            }
        }

        $perPage = (int) $request->input('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        $users = $query->latest()->paginate($perPage)->withQueryString();

        return response()->json([
            'status' => 'success',
            'message' => 'Users retrieved successfully.',
            'data' => UserResource::collection($users),
        ], 200);
    }

    public function show(int $user): JsonResponse
    {
        $model = User::find($user);

        if (! $model) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User retrieved successfully.',
            'data' => new UserResource($model->load('pricingPlan')),
        ], 200);
    }

    public function store(UserRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated = $this->normalizePlanInputToPricingPlanId($validated);

        $user = User::create([
            'username' => $validated['username'],
            'first_name' => $validated['first_name'] ?? null,
            'last_name' => $validated['last_name'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'role' => $validated['role'] ?? 'user',
            'account_status' => $validated['account_status'] ?? 'active',
            'timezone' => $validated['timezone'] ?? null,
            'language' => $validated['language'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            'plan' => $validated['plan'] ?? 'Free',
            'pricing_plan_id' => $validated['pricing_plan_id'] ?? null,
        ]);

        if (array_key_exists('assigned_projects', $validated)) {
            $user->assignedProjects()->sync($validated['assigned_projects'] ?? []);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully.',
            'data' => new UserResource($user->load('pricingPlan')),
        ], 201);
    }

    public function update(UserRequest $request, int $user): JsonResponse
    {
        $model = User::find($user);

        if (! $model) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
                'data' => null,
            ], 404);
        }

        $validated = $request->validated();
        $validated = $this->normalizePlanInputToPricingPlanId($validated);

        $data = collect($validated)->only([
            'username',
            'email',
            'role',
            'first_name',
            'last_name',
            'phone',
            'account_status',
            'timezone',
            'language',
            'job_title',
            'plan',
            'pricing_plan_id',
        ])->all();

        if (array_key_exists('password', $validated) && $validated['password']) {
            $data['password'] = $validated['password'];
        }

        $model->update($data);

        if (array_key_exists('assigned_projects', $validated)) {
            $model->assignedProjects()->sync($validated['assigned_projects'] ?? []);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully.',
            'data' => new UserResource($model->fresh()->load('pricingPlan')),
        ], 200);
    }

    public function destroy(int $user): JsonResponse
    {
        $model = User::find($user);

        if (! $model) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
                'data' => null,
            ], 404);
        }

        $model->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully.',
            'data' => null,
        ], 200);
    }

    /**
     * Resolve a plan identifier from ?plan=... (slug-first, then name).
     * Returns:
     * - PricingPlan instance if resolved uniquely
     * - 'ambiguous' if name matches multiple plans
     * - null if not found (caller may fallback to legacy users.plan)
     */
    private function resolvePricingPlanFromPlanParam(string $plan): PricingPlan|string|null
    {
        if ($plan === '') {
            return null;
        }

        $bySlug = PricingPlan::query()->where('slug', $plan)->first();
        if ($bySlug) {
            return $bySlug;
        }

        $byNameCount = PricingPlan::query()->where('name', $plan)->count();
        if ($byNameCount === 1) {
            return PricingPlan::query()->where('name', $plan)->first();
        }

        if ($byNameCount > 1) {
            return 'ambiguous';
        }

        return null;
    }

    /**
     * Normalizes plan input into pricing_plan_id.
     * - pricing_plan_id wins (UserRequest prevents both present)
     * - plan string may be slug or legacy name
     */
    private function normalizePlanInputToPricingPlanId(array $validated): array
    {
        if (array_key_exists('pricing_plan_id', $validated) && $validated['pricing_plan_id']) {
            return $validated;
        }

        if (! array_key_exists('plan', $validated) || ! is_string($validated['plan'])) {
            return $validated;
        }

        $plan = trim($validated['plan']);
        if ($plan === '') {
            return $validated;
        }

        $resolved = $this->resolvePricingPlanFromPlanParam($plan);
        if ($resolved instanceof PricingPlan) {
            $validated['pricing_plan_id'] = $resolved->id;
        }

        return $validated;
    }
}

