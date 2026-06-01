<?php

namespace App\Providers;

use App\Auth\SupabaseUserProvider;
use App\Services\SupabaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Blade::anonymousComponentPath(resource_path('views/user'), 'user');

        Auth::provider('supabase', function ($app, array $config) {
            return new SupabaseUserProvider($app->make(SupabaseService::class));
        });
    }
}
