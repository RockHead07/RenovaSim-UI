<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PricingPlanRequest;
use App\Http\Resources\PricingPlanResource;
use App\Models\PlanFeature;
use App\Models\PricingPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PricingPlanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PricingPlan::with('features')->withCount('features');

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $plans = $query->latest()->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Pricing plans retrieved successfully.',
            'data'    => PricingPlanResource::collection($plans),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $plan = PricingPlan::with('features')->withCount('features')->find($id);

        if (! $plan) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pricing plan not found.',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Pricing plan retrieved successfully.',
            'data'    => new PricingPlanResource($plan),
        ]);
    }

    public function store(PricingPlanRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $data = collect($validated)->only(['name', 'description', 'price', 'original_price'])->all();
        $data['is_popular'] = $validated['is_popular'] ?? false;
        $data['is_active'] = $validated['is_active'] ?? true;

        // Clear original_price if not a valid discount
        if (! is_null($data['original_price']) && (float) $data['original_price'] <= (float) $data['price']) {
            $data['original_price'] = null;
        }

        // Only one plan can be popular
        if ($data['is_popular']) {
            PricingPlan::query()->update(['is_popular' => false]);
        }

        $plan = PricingPlan::create($data);
        $this->syncFeatures($plan, $validated['features'] ?? []);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pricing plan created successfully.',
            'data'    => new PricingPlanResource($plan->load('features')->loadCount('features')),
        ], 201);
    }

    public function update(PricingPlanRequest $request, int $id): JsonResponse
    {
        $plan = PricingPlan::find($id);

        if (! $plan) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pricing plan not found.',
                'data'    => null,
            ], 404);
        }

        $validated = $request->validated();

        $data = collect($validated)->only(['name', 'description', 'price', 'original_price'])->all();
        $data['is_popular'] = $validated['is_popular'] ?? false;
        $data['is_active'] = $validated['is_active'] ?? true;

        if (! is_null($data['original_price']) && (float) $data['original_price'] <= (float) $data['price']) {
            $data['original_price'] = null;
        }

        if ($data['is_popular']) {
            PricingPlan::where('id', '!=', $plan->id)->update(['is_popular' => false]);
        }

        $plan->update($data);
        $this->syncFeatures($plan, $validated['features'] ?? []);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pricing plan updated successfully.',
            'data'    => new PricingPlanResource($plan->fresh()->load('features')->loadCount('features')),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $plan = PricingPlan::find($id);

        if (! $plan) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pricing plan not found.',
                'data'    => null,
            ], 404);
        }

        $plan->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Pricing plan deleted successfully.',
            'data'    => null,
        ]);
    }

    private function syncFeatures(PricingPlan $plan, array $features): void
    {
        $plan->features()->delete();

        foreach ($features as $featureInput) {
            $featureName = trim((string) ($featureInput['feature'] ?? ''));
            if ($featureName === '') {
                continue;
            }

            PlanFeature::create([
                'pricing_plan_id' => $plan->id,
                'feature'         => $featureName,
                'is_available'    => (bool) ($featureInput['is_available'] ?? true),
            ]);
        }
    }
}
