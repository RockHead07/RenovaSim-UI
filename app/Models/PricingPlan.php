<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PricingPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'original_price',
        'is_popular',
        'is_active',
    ];

    protected $casts = [
        'is_popular' => 'boolean',
        'is_active'  => 'boolean',
        'price'      => 'decimal:2',
        'original_price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $plan): void {
            // Slug is only auto-generated on CREATE, never on update
            if (is_string($plan->slug) && trim($plan->slug) !== '') {
                $plan->slug = Str::slug($plan->slug);
                return;
            }
            $base = Str::slug((string) $plan->name);
            $base = $base !== '' ? $base : 'plan';
            $candidate = $base;
            $i = 2;
            while (
                static::query()
                    ->where('slug', $candidate)
                    ->exists()
            ) {
                $candidate = "{$base}-{$i}";
                $i++;
            }
            $plan->slug = $candidate;
        });
        // Note: NO saving hook — slug never changes after creation
    }

    public function features()
    {
        return $this->hasMany(PlanFeature::class);
    }
}