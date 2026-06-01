<?php
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\PricingPlanController;
use App\Http\Controllers\User\EstimationController;
use App\Http\Controllers\User\UserProjectController;
use App\Http\Controllers\User\RabController;
use App\Http\Controllers\User\UserSettingsController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ApiManagerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $order    = ['free' => 1, 'pro' => 2, 'enterprise' => 3];
    $supabase = app(\App\Services\SupabaseService::class);

    $rawPlans = $supabase->select('pricing_plans', '*', ['is_active' => true]);

    // Attach features to each plan
    $pricingPlans = collect($rawPlans)->map(function ($plan) use ($supabase) {
        $features = $supabase->select('plan_features', '*', ['pricing_plan_id' => $plan['id']]);
        $plan['features'] = collect($features)->map(fn($f) => (object) $f);
        return (object) $plan;
    })->sortBy(fn($p) => $order[$p->slug] ?? 99)->values();

    return view('welcome', compact('pricingPlans'));
});

// Google OAuth Routes
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('auth.google.callback');

Route::middleware(['auth', 'role:user'])->prefix('user')->group(function () {
    Route::view('/dashboard', 'user.pages.dashboard')->name('dashboard');
    Route::view('/project-stage', 'user.pages.project-stage');
    Route::view('/project-details', 'user.pages.project-details');
    Route::get('/project-overview', [UserProjectController::class, 'showOverview'])->name('user.project-overview');
    Route::view('/project-rab', 'user.pages.project-rab');
    Route::view('/3d', 'user.pages.three-d-design')->name('user.3d');
    Route::view('/editor', 'user.pages.editor')->name('user.editor');
    Route::get('/editor/{projectId}', function ($projectId) {
        return view('user.pages.editor', ['projectId' => $projectId]);
    })->name('user.editor.project');

    // Projects list & detail
    Route::get('/projects', [UserProjectController::class, 'index'])->name('user.projects');
    Route::get('/projects/{project}', [UserProjectController::class, 'show'])->name('user.projects.show');
    Route::delete('/projects/{project}', [UserProjectController::class, 'destroy'])->name('user.projects.destroy');
    Route::get('/project/{id}/view', [UserProjectController::class, 'viewProject'])->name('user.project.view');

    // Project setup flow
    Route::get('/project/create', [EstimationController::class, 'showProjectSetup'])->name('user.project.setup');
    Route::post('/project/create', [EstimationController::class, 'storeProjectSetup'])->name('user.project.setup.store');
    Route::post('/project/save-estimation', [UserProjectController::class, 'saveEstimation'])->name('user.project.save');

    // Estimation context selection
    Route::get('/estimation/start', [EstimationController::class, 'showStart'])->name('user.estimation.start');
    Route::get('/estimation/quick', [EstimationController::class, 'quickEstimation'])->name('user.estimation.quick');
    Route::get('/project/{id}/add-estimation', [UserProjectController::class, 'addEstimation'])->name('user.project.add-estimation');

    // Estimation flow
    Route::get('/ai-estimation', [EstimationController::class, 'showWizard'])->name('user.estimation.wizard');
    Route::post('/ai-estimation/wizard', [EstimationController::class, 'submitWizard'])->name('user.estimation.submitWizard');
    Route::post('/ai-estimation/ai', [EstimationController::class, 'submitAI'])->name('user.estimation.submitAI');
    Route::get('/estimation-result', [EstimationController::class, 'showResult'])->name('user.estimation.result');
    Route::get('/estimation-result/refine', [EstimationController::class, 'showRefine'])->name('user.estimation.showRefine');
    Route::post('/estimation-result/refine', [EstimationController::class, 'submitRefine'])->name('user.estimation.refine');
    // Settings
    Route::get('/settings',           [UserSettingsController::class, 'show'])->name('user.settings');
    Route::post('/settings/profile',  [UserSettingsController::class, 'updateProfile'])->name('user.settings.profile');
    Route::post('/settings/password', [UserSettingsController::class, 'updatePassword'])->name('user.settings.password');

    // RAB routes
    Route::get('/project/{id}/rab',        [RabController::class, 'show'])->name('user.project.rab');
    Route::get('/project/{id}/rab/export', [RabController::class, 'export'])->name('user.project.rab.export');
    Route::post('/project/{id}/rab/share', [RabController::class, 'generateShare'])->name('user.project.rab.share');
});

Route::get('/rab/{token}', [RabController::class, 'publicView'])->name('rab.public');

// User Panel & Room Editor Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/panel', [RoomController::class, 'panel'])->name('panel');
    Route::get('/rooms', [RoomController::class, 'index'])->name('room.index');
    Route::get('/room/create', [RoomController::class, 'create'])->name('room.create');
    Route::post('/room/store', [RoomController::class, 'store'])->name('room.store');
    Route::get('/room/{room}/editor', [RoomController::class, 'editor'])->name('room.editor');
    Route::get('/api/room/{room}', [RoomController::class, 'show'])->name('room.show');
    Route::post('/api/room/{room}/save', [RoomController::class, 'save'])->name('room.save');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard/metrics', [AdminDashboardController::class, 'metrics'])->name('admin.dashboard.metrics');
    Route::get('/dashboard/activity', [AdminDashboardController::class, 'activity'])->name('admin.dashboard.activity');

    // Users API (used by admin.users.index search/filter)
    Route::get('/users-api', [UserController::class, 'api']);

    // CRUD Routes with full resource control
    Route::resource('/users', UserController::class);
    Route::resource('/projects', ProjectController::class);
    Route::resource('/materials', MaterialController::class);
    Route::resource('/pricing-plans', PricingPlanController::class)->middleware('manage-pricing-plans');
    Route::resource('/partners', PartnerController::class);
    Route::get('/rooms', [RoomController::class, 'adminIndex'])->name('admin.rooms.index');

    // Manage API
    Route::get('/api-manager',  [ApiManagerController::class, 'index'])->name('admin.api');
    Route::post('/api-manager/regenerate', [ApiManagerController::class, 'regenerate'])->name('admin.api.regenerate');

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
