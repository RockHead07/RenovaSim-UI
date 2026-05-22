<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlanFeatureRequest;
use App\Http\Requests\UpdatePlanFeatureRequest;
use App\Http\Resources\PlanFeatureResource;
use App\Models\PlanFeature;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;

class PlanFeatureController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $features = PlanFeature::with('plan')->latest()->paginate(15);
        return PlanFeatureResource::collection($features);
    }

    public function store(StorePlanFeatureRequest $request): PlanFeatureResource
    {
        $feature = PlanFeature::create($request->validated());
        return new PlanFeatureResource($feature->load('plan'));
    }

    public function show(PlanFeature $planFeature): PlanFeatureResource
    {
        return new PlanFeatureResource($planFeature->loadMissing('plan'));
    }

    public function update(UpdatePlanFeatureRequest $request, PlanFeature $planFeature): PlanFeatureResource
    {
        $planFeature->update($request->validated());
        return new PlanFeatureResource($planFeature->loadMissing('plan'));
    }

    public function destroy(PlanFeature $planFeature): JsonResponse
    {
        $planFeature->delete();
        return response()->json(null, 204);
    }
}
