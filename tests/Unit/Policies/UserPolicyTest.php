<?php declare(strict_types=1);

use App\Models\Tenant;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('viewAny allows root', function (): void {
    $root = User::factory()->root()->create();

    expect((new UserPolicy())->viewAny($root))->toBeTrue();
});

test('viewAny allows tenant_admin', function (): void {
    $tenant = Tenant::factory()->create();
    $admin  = User::factory()->tenantAdmin($tenant)->create();

    expect((new UserPolicy())->viewAny($admin))->toBeTrue();
});

test('viewAny denies tenant_user', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    expect((new UserPolicy())->viewAny($user))->toBeFalse();
});

test('view allows root to view any user', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();
    $target = User::factory()->tenantUser($tenant)->create();

    expect((new UserPolicy())->view($root, $target))->toBeTrue();
});

test('view allows tenant_admin to view own tenant user', function (): void {
    $tenant = Tenant::factory()->create();
    $admin  = User::factory()->tenantAdmin($tenant)->create();
    $target = User::factory()->tenantUser($tenant)->create();

    expect((new UserPolicy())->view($admin, $target))->toBeTrue();
});

test('view denies tenant_admin from viewing another tenant user', function (): void {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();
    $admin   = User::factory()->tenantAdmin($tenantA)->create();
    $target  = User::factory()->tenantUser($tenantB)->create();

    expect((new UserPolicy())->view($admin, $target))->toBeFalse();
});

test('update denies tenant_admin from updating root user', function (): void {
    $tenant = Tenant::factory()->create();
    $admin  = User::factory()->tenantAdmin($tenant)->create();
    $root   = User::factory()->root()->create();

    expect((new UserPolicy())->update($admin, $root))->toBeFalse();
});

test('delete prevents tenant_admin from deleting themselves', function (): void {
    $tenant = Tenant::factory()->create();
    $admin  = User::factory()->tenantAdmin($tenant)->create();

    expect((new UserPolicy())->delete($admin, $admin))->toBeFalse();
});

test('delete prevents tenant_admin from deleting cross-tenant user', function (): void {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();
    $admin   = User::factory()->tenantAdmin($tenantA)->create();
    $target  = User::factory()->tenantUser($tenantB)->create();

    expect((new UserPolicy())->delete($admin, $target))->toBeFalse();
});

test('delete allows root to delete any user', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();
    $target = User::factory()->tenantUser($tenant)->create();

    expect((new UserPolicy())->delete($root, $target))->toBeTrue();
});

test('delete allows tenant_admin to delete own tenant non-root user', function (): void {
    $tenant = Tenant::factory()->create();
    $admin  = User::factory()->tenantAdmin($tenant)->create();
    $target = User::factory()->tenantUser($tenant)->create();

    expect((new UserPolicy())->delete($admin, $target))->toBeTrue();
});
