<?php declare(strict_types=1);

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

    Route::resource('labels', \App\Http\Controllers\Root\LabelController::class);

    // Root — review queue
    Route::get('reports', [\App\Http\Controllers\Root\ReportReviewController::class, 'index'])->name('reports.index');
    Route::get('reports/{report}', [\App\Http\Controllers\Root\ReportReviewController::class, 'show'])->name('reports.show');
    Route::post('reports/{report}/approve', [\App\Http\Controllers\Root\ReportReviewController::class, 'approve'])->name('reports.approve');
    Route::post('reports/{report}/reject', [\App\Http\Controllers\Root\ReportReviewController::class, 'reject'])->name('reports.reject');
    Route::post('reports/{report}/publish', [\App\Http\Controllers\Root\ReportReviewController::class, 'publish'])->name('reports.publish');

    // Integration configuration per tenant
    Route::get('tenants/{tenant}/integration', [\App\Http\Controllers\Root\IntegrationController::class, 'edit'])->name('tenants.integration.edit');
    Route::post('tenants/{tenant}/integration/jira', [\App\Http\Controllers\Root\IntegrationController::class, 'storeJira'])->name('tenants.integration.jira');
    Route::post('tenants/{tenant}/integration/github', [\App\Http\Controllers\Root\IntegrationController::class, 'storeGitHub'])->name('tenants.integration.github');

    // Create issue from approved/published report
    Route::post('reports/{report}/create-issue', [\App\Http\Controllers\Root\ReportReviewController::class, 'createIssue'])->name('reports.create-issue');

    // Voting / Ranking
    Route::get('voting', \App\Http\Controllers\Root\VotingRankingController::class)->name('voting.index');

    // Entregas (relatórios concluídos)
    Route::get('delivered', \App\Http\Controllers\Root\DeliveredController::class)->name('delivered.index');
});

// Tenant routes (tenant_admin + tenant_user)
Route::middleware(['auth', 'tenant'])->prefix('app')->name('app.')->group(function () {
    Route::get('/dashboard', fn() => view('app.dashboard'))->name('dashboard');

    // Tenant admin only
    Route::middleware('role:tenant_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', \App\Http\Controllers\Tenant\UserController::class);
    });

    // Reports — tenant users
    Route::resource('reports', \App\Http\Controllers\Tenant\ReportController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update']);

    Route::post('reports/{report}/attachments', [\App\Http\Controllers\Tenant\ReportAttachmentController::class, 'store'])->name('reports.attachments.store')->middleware('throttle:attachments');
    Route::post('reports/{report}/links', [\App\Http\Controllers\Tenant\ReportAttachmentController::class, 'storeLink'])->name('reports.links.store')->middleware('throttle:attachments');
    Route::delete('reports/attachments/{attachment}', [\App\Http\Controllers\Tenant\ReportAttachmentController::class, 'destroy'])->name('reports.attachments.destroy');
    Route::get('reports/attachments/{attachment}/download', [\App\Http\Controllers\Tenant\ReportAttachmentController::class, 'download'])->name('reports.attachments.download');

    // Voting board
    Route::get('voting', \App\Http\Controllers\Tenant\VotingController::class)->name('voting.index');

    // Vote toggle (single endpoint handles both cast and retract)
    Route::post('reports/{report}/vote', [\App\Http\Controllers\Tenant\VoteController::class, 'toggle'])->name('votes.toggle')->middleware('throttle:votes');
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
