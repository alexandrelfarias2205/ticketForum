<?php declare(strict_types=1);

use App\Policies\ReportPolicy;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// NOTE: There is no HTTP DELETE route for reports in this application.
// The delete policy is defined but deletion is only available through
// admin actions. These tests verify the policy itself is correctly enforced.

test('ReportPolicy delete allows root', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();
    $report = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    expect((new ReportPolicy())->delete($root, $report))->toBeTrue();
});

test('ReportPolicy delete denies tenant_user even on own report', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();
    $report = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $user->id,
    ]);

    expect((new ReportPolicy())->delete($user, $report))->toBeFalse();
});

test('ReportPolicy delete denies tenant_admin', function (): void {
    $tenant = Tenant::factory()->create();
    $admin  = User::factory()->tenantAdmin($tenant)->create();
    $author = User::factory()->tenantUser($tenant)->create();
    $report = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    expect((new ReportPolicy())->delete($admin, $report))->toBeFalse();
});
