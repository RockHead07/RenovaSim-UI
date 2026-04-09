<?php
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
//dashboard admin
Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// dashboard admin/users
Route::get('/admin/users', function () {
    return view('admin.users.index');
});

// dashboard admin/projects
Route::get('/admin/projects', function () {
    return view('admin.projects.index');
});

// dashboard admin/materials
Route::get('/admin/materials', function () {
    return view('admin.materials.index');
});

// dashboard admin/pricing
Route::get('/admin/pricing', function () {
    return view('admin.pricing.index');
});

//route crud user
Route::resource('admin/users', UserController::class)
    ->names('admin.users');

//route crud partner
Route::resource('admin/partners', PartnerController::class)
    ->names('admin.partners');

//route subscribe footer
Route::post('/newsletter', function () {
    return back()->with('success', 'Subscribed!');
})->name('newsletter.subscribe');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
