<?php

namespace App\Providers;

use App\Models\PricingPlan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Blade::anonymousComponentPath(resource_path('views/user'), 'user');

        Cache::remember('pricing_plans_with_features', 3600, function () {
            return PricingPlan::with('features')->get();
        });
    }
}
