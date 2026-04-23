<?php declare(strict_types=1);

use App\Enums\ReportStatus;
use App\Enums\ReportType;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('tenant_user can create report', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    $this->actingAs($user)
        ->post(route('app.reports.store'), [
            'type'        => 'bug',
            'title'       => 'Something is broken',
            'description' => 'Details about the bug go here.',
        ])
        ->assertRedirect();

    $report = Report::withoutGlobalScopes()->where('author_id', $user->id)->first();

    expect($report)->not->toBeNull()
        ->and($report->status)->toBe(ReportStatus::PendingReview)
        ->and((string) $report->tenant_id)->toBe((string) $tenant->id);
});

test('tenant_user cannot create report with invalid type', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    $this->actingAs($user)
        ->post(route('app.reports.store'), [
            'type'        => 'invalid_type',
            'title'       => 'A title',
            'description' => 'Some description.',
        ])
        ->assertSessionHasErrors(['type']);
});

test('tenant_user can view own reports', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    Report::factory()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('app.reports.index'))
        ->assertStatus(200);
});

test('tenant_user can view own report detail', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $user->id,
    ]);

    // Re-read user from DB so UUID attributes are plain strings (not LazyUuid objects)
    $user->refresh();

    $this->actingAs($user)
        ->get(route('app.reports.show', $report))
        ->assertStatus(200);
});

test('tenant_user cannot view report from another tenant', function (): void {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $userA   = User::factory()->tenantUser($tenantA)->create();
    $authorB = User::factory()->tenantUser($tenantB)->create();

    $reportB = Report::factory()->create([
        'tenant_id' => $tenantB->id,
        'author_id' => $authorB->id,
    ]);

    // TenantScope will filter out the report — route model binding will 404
    $this->actingAs($userA)
        ->get(route('app.reports.show', $reportB))
        ->assertStatus(404);
});

test('tenant_user can edit pending report', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $user->id,
    ]);

    $user->refresh();

    $this->actingAs($user)
        ->get(route('app.reports.edit', $report))
        ->assertStatus(200);
});

test('tenant_user cannot edit approved report', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->approved()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('app.reports.edit', $report))
        ->assertStatus(403);
});

test('root cannot create report', function (): void {
    $root = User::factory()->root()->create();

    $this->actingAs($root)
        ->post(route('app.reports.store'), [
            'type'        => 'bug',
            'title'       => 'A title',
            'description' => 'A description.',
        ])
        ->assertStatus(403);
});

test('unauthenticated user is redirected from reports', function (): void {
    $this->get(route('app.reports.index'))
        ->assertRedirect(route('login'));
});
