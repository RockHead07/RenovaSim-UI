<?php
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\PricingPlanController;
use App\Http\Controllers\User\EstimationController;
use App\Models\PricingPlan;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $pricingPlans = PricingPlan::with('features')
        ->where('is_active', true)
        ->orderByDesc('is_popular')
        ->orderBy('price')
        ->get();

    return view('welcome', compact('pricingPlans'));
});

Route::middleware(['auth', 'role:user'])->prefix('user')->group(function () {
    Route::view('/dashboard', 'user.pages.dashboard');
    Route::view('/project-stage', 'user.pages.project-stage');
    Route::view('/project-details', 'user.pages.project-details');
    Route::view('/project-overview', 'user.pages.project-overview')->name('user.project-overview');
    Route::view('/project-rab', 'user.pages.project-rab');
    Route::view('/3d', 'user.pages.three-d-design');

    // Estimation flow — controller-based
    Route::get('/ai-estimation', [EstimationController::class, 'showWizard'])->name('user.estimation.wizard');
    Route::post('/ai-estimation/wizard', [EstimationController::class, 'submitWizard'])->name('user.estimation.submitWizard');
    Route::post('/ai-estimation/ai', [EstimationController::class, 'submitAI'])->name('user.estimation.submitAI');
    Route::get('/estimation-result', [EstimationController::class, 'showResult'])->name('user.estimation.result');
    Route::get('/estimation-result/refine', [EstimationController::class, 'showRefine'])->name('user.estimation.showRefine');
    Route::post('/estimation-result/refine', [EstimationController::class, 'submitRefine'])->name('user.estimation.refine');
});

Route::get('/project/{id}/rab', function ($id) {
    return view('user.pages.project-rab');
})->middleware(['auth', 'role:user'])->name('project-rab');

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Users API (used by admin.users.index search/filter)
    Route::get('/users-api', [UserController::class, 'api']);

    // CRUD Routes with full resource control
    Route::resource('/users', UserController::class);
    Route::resource('/projects', ProjectController::class);
    Route::resource('/materials', MaterialController::class);
    Route::resource('/pricing-plans', PricingPlanController::class)->middleware('manage-pricing-plans');
    Route::resource('/partners', PartnerController::class);

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/newsletter', function () {
    return back()->with('success', 'Subscribed!');
})->name('newsletter.subscribe');

Route::get('/_phpinfo', function () {
    phpinfo();
    return '';
});

require __DIR__.'/auth.php';
