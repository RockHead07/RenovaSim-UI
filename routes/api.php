<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are intentionally separated from the Blade/Admin layer.
| If you want to protect them, add middleware like:
| ->middleware(['auth:sanctum', 'admin'])
|
*/

Route::apiResource('users', UserController::class);

