<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar_path' => $this->avatar_path,
            'role' => $this->role,
            'account_status' => $this->account_status,
            'timezone' => $this->timezone,
            'language' => $this->language,
            'job_title' => $this->job_title,
            'plan' => $this->whenLoaded('pricingPlan', function () {
                if (! $this->pricingPlan) {
                    return null;
                }

                return [
                    'id' => $this->pricingPlan->id,
                    'slug' => $this->pricingPlan->slug,
                    'name' => $this->pricingPlan->name,
                ];
            }),
            // Temporary legacy field for backwards compatibility during migration window.
            // Remove once the frontend no longer depends on string-based plan.
            'plan_name' => $this->pricingPlan?->name ?? $this->plan,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}

