<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanFeature extends Model
{
    protected $fillable = [
        'pricing_plan_id',
        'feature',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    public function plan()
    {
        return $this->belongsTo(PricingPlan::class, 'pricing_plan_id');
    }
}