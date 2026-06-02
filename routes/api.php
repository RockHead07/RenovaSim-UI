<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\PartnerController;
use App\Http\Controllers\Api\PricingPlanController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PlanFeatureController;
use App\Http\Controllers\Api\PublicApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are intentionally separated from the Blade/Admin layer.
| Protected by auth:sanctum + admin middleware for admin endpoints.
|
*/

// ─── Public API v1 (Bearer token auth via RENOVASIM_API_KEY) ─────────
Route::prefix('v1')->group(function () {
    Route::get('users',          [PublicApiController::class, 'users']);
    Route::get('users/{id}',     [PublicApiController::class, 'user']);
    Route::get('projects',       [PublicApiController::class, 'projects']);
    Route::get('estimations',    [PublicApiController::class, 'estimations']);
    Route::get('materials',      [PublicApiController::class, 'materials']);
    Route::get('partners',       [PublicApiController::class, 'partners']);
    Route::get('pricing-plans',  [PublicApiController::class, 'pricingPlans']);
});

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Admin-only API endpoints
    Route::middleware('admin')->group(function () {
        // Dashboard
        Route::get('dashboard/metrics', [DashboardController::class, 'metrics']);
        Route::get('dashboard/activity', [DashboardController::class, 'activity']);

        // CRUD Resources
        Route::apiResource('users', UserController::class)->names('api.users');
        Route::apiResource('projects', ProjectController::class)->names('api.projects');
        Route::apiResource('materials', MaterialController::class)->names('api.materials');
        Route::apiResource('pricing-plans', PricingPlanController::class)->names('api.pricing-plans');
        Route::apiResource('partners', PartnerController::class)->names('api.partners');
        Route::apiResource('plan-features', PlanFeatureController::class)->names('api.plan-features');
    });
});
