<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\PartnerController;
use App\Http\Controllers\Api\PricingPlanController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\UserController;
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
        Route::apiResource('users', UserController::class);
        Route::apiResource('projects', ProjectController::class);
        Route::apiResource('materials', MaterialController::class);
        Route::apiResource('pricing-plans', PricingPlanController::class);
        Route::apiResource('partners', PartnerController::class);
    });
});
