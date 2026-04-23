<?php declare(strict_types=1);

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\User;
use App\Policies\ReportPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('viewAny is true for any authenticated user', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    expect((new ReportPolicy())->viewAny($user))->toBeTrue();
});

test('view allows root to view any report', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();
    $report = Report::factory()->create(['tenant_id' => $tenant->id, 'author_id' => $author->id]);

    expect((new ReportPolicy())->view($root, $report))->toBeTrue();
});

test('view allows tenant_user to view own tenant report', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();
    $report = Report::factory()->create(['tenant_id' => $tenant->id, 'author_id' => $user->id]);

    expect((new ReportPolicy())->view($user, $report))->toBeTrue();
});

test('view denies tenant_user from viewing another tenant report', function (): void {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();
    $userA   = User::factory()->tenantUser($tenantA)->create();
    $authorB = User::factory()->tenantUser($tenantB)->create();
    $report  = Report::factory()->create(['tenant_id' => $tenantB->id, 'author_id' => $authorB->id]);

    expect((new ReportPolicy())->view($userA, $report))->toBeFalse();
});

test('create allows tenant_user', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    expect((new ReportPolicy())->create($user))->toBeTrue();
});

test('create allows tenant_admin', function (): void {
    $tenant = Tenant::factory()->create();
    $admin  = User::factory()->tenantAdmin($tenant)->create();

    expect((new ReportPolicy())->create($admin))->toBeTrue();
});

test('create denies root', function (): void {
    $root = User::factory()->root()->create();

    expect((new ReportPolicy())->create($root))->toBeFalse();
});

test('update allows author on pending report', function (): void {
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();
    $report = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    expect((new ReportPolicy())->update($author, $report))->toBeTrue();
});

test('update denies non-author tenant user', function (): void {
    $tenant  = Tenant::factory()->create();
    $author  = User::factory()->tenantUser($tenant)->create();
    $intruder = User::factory()->tenantUser($tenant)->create();
    $report  = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    expect((new ReportPolicy())->update($intruder, $report))->toBeFalse();
});

test('update denies author on approved report', function (): void {
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();
    $report = Report::factory()->approved()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    expect((new ReportPolicy())->update($author, $report))->toBeFalse();
});

test('update allows root on any report', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();
    $report = Report::factory()->approved()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    expect((new ReportPolicy())->update($root, $report))->toBeTrue();
});

test('delete allows root only', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();
    $report = Report::factory()->create(['tenant_id' => $tenant->id, 'author_id' => $author->id]);

    expect((new ReportPolicy())->delete($root, $report))->toBeTrue()
        ->and((new ReportPolicy())->delete($author, $report))->toBeFalse();
});

test('approve and publish allowed only for root', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();
    $admin  = User::factory()->tenantAdmin($tenant)->create();
    $report = Report::factory()->create(['tenant_id' => $tenant->id, 'author_id' => $admin->id]);

    $policy = new ReportPolicy();

    expect($policy->approve($root, $report))->toBeTrue()
        ->and($policy->approve($admin, $report))->toBeFalse()
        ->and($policy->publish($root, $report))->toBeTrue()
        ->and($policy->publish($admin, $report))->toBeFalse()
        ->and($policy->createIssue($root, $report))->toBeTrue()
        ->and($policy->createIssue($admin, $report))->toBeFalse();
});
