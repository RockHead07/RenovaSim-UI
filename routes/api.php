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
use App\Http\Controllers\Api\Room3DController;
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

// 3D Room API — public catalog endpoints (no auth, just Flask proxy)
Route::prefix('3d')->group(function () {
    Route::get('/status',      [Room3DController::class, 'status']);
    Route::get('/furniture',   [Room3DController::class, 'furniture']);
    Route::get('/templates',   [Room3DController::class, 'templates']);
    Route::get('/paint-colors',[Room3DController::class, 'paintColors']);
});

// 3D Room API — user data endpoints (session auth)
Route::middleware(['web', 'auth'])->group(function () {
    Route::prefix('3d')->group(function () {
        Route::get('/projects',                   [Room3DController::class, 'projects']);
        Route::post('/upload-images',             [Room3DController::class, 'uploadImages']);
        Route::get('/rooms/{id}',                 [Room3DController::class, 'getRoom']);
        Route::post('/rooms/{id}/save',           [Room3DController::class, 'saveRoom']);
        Route::post('/rooms/{id}/thumbnail',      [Room3DController::class, 'saveThumbnail']);
        Route::post('/rooms/{id}/apply-template', [Room3DController::class, 'applyTemplate']);
        Route::post('/rooms/{id}/update-wall',    [Room3DController::class, 'updateWall']);
        Route::post('/rooms/{id}/rename',         [Room3DController::class, 'renameRoom']);
        Route::delete('/rooms/{id}',              [Room3DController::class, 'deleteRoom']);
        Route::post('/migrate',                   [Room3DController::class, 'migrateFromFlask']);
    });

    // Admin CRUD resources using session authentication (protected by admin middleware)
    Route::middleware('admin')->group(function () {
        Route::apiResource('partners', PartnerController::class)->names('api.partners');
        Route::apiResource('users', UserController::class)->names('api.users');
        Route::apiResource('projects', ProjectController::class)->names('api.projects');
        Route::apiResource('materials', MaterialController::class)->names('api.materials');
        Route::apiResource('pricing-plans', PricingPlanController::class)->names('api.pricing-plans');
        Route::apiResource('plan-features', PlanFeatureController::class)->names('api.plan-features');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Admin-only API endpoints (using token authentication)
    Route::middleware('admin')->group(function () {
        // Dashboard
        Route::get('dashboard/metrics', [DashboardController::class, 'metrics']);
        Route::get('dashboard/activity', [DashboardController::class, 'activity']);
    });
});
