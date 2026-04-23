<?php

use App\Http\Controllers\ProfileController;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureTenantAccess;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Root-only routes
Route::middleware(['auth', 'role:root'])->prefix('root')->name('root.')->group(function () {
    Route::get('/dashboard', fn() => view('root.dashboard'))->name('dashboard');
    Route::resource('tenants', \App\Http\Controllers\Root\TenantController::class);
    Route::resource('users', \App\Http\Controllers\Root\UserController::class);
});

// Tenant routes (tenant_admin + tenant_user)
Route::middleware(['auth', 'tenant'])->prefix('app')->name('app.')->group(function () {
    Route::get('/dashboard', fn() => view('app.dashboard'))->name('dashboard');

    // Tenant admin only
    Route::middleware('role:tenant_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', \App\Http\Controllers\Tenant\UserController::class);
    });

    // Placeholder routes for views not yet implemented
    Route::get('/reports', fn() => view('app.dashboard'))->name('reports.index');
    Route::get('/voting', fn() => view('app.dashboard'))->name('voting.index');
});

// Redirect after login based on role
Route::get('/dashboard', function () {
    $role = auth()->user()->role;
    if ($role->isRoot()) {
        return redirect()->route('root.dashboard');
    }
    return redirect()->route('app.dashboard');
})->middleware('auth')->name('dashboard');

require __DIR__.'/auth.php';
