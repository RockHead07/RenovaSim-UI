<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanFeature extends Model
{
    protected $fillable = [
        'pricing_plan_id',
        'feature_key',    // internal key — NEVER changes (e.g. max_projects)
        'feature_label',  // display label — admin can edit
        'feature_value',  // value — admin can edit (e.g. "2", "unlimited", "true")
        'feature',        // legacy display string (kept for backward compat)
        'is_available',   // boolean toggle
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    public function plan()
    {
        return $this->belongsTo(PricingPlan::class, 'pricing_plan_id');
    }

    /**
     * Get numeric value or null if unlimited/boolean
     */
    public function numericValue(): ?int
    {
        if ($this->feature_value === 'unlimited' || $this->feature_value === '') {
            return null;
        }
        return is_numeric($this->feature_value) ? (int) $this->feature_value : null;
    }

    public function isUnlimited(): bool
    {
        return $this->feature_value === 'unlimited';
    }
}
