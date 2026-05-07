<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\PlanFeature
 */
class PlanFeatureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pricing_plan_id' => $this->pricing_plan_id,
            'feature' => $this->feature,
            'is_available' => $this->is_available,
            'plan' => new PricingPlanResource($this->whenLoaded('plan')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
