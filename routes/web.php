<?php
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\PricingPlanController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\Auth\GoogleController;
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

// Google OAuth Routes
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// User Panel & Room Editor Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

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
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

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

require __DIR__.'/auth.php';
