<?php declare(strict_types=1);

use App\Actions\Tenants\CreateTenantAction;
use App\Actions\Tenants\DeactivateTenantAction;
use App\Actions\Tenants\UpdateTenantAction;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('root can view tenant list', function (): void {
    $root = User::factory()->root()->create();

    $this->actingAs($root)
        ->get('/root/tenants')
        ->assertStatus(200);
});

test('root can create tenant', function (): void {
    $root = User::factory()->root()->create();
    $this->actingAs($root);

    $tenant = app(CreateTenantAction::class)->handle([
        'name' => 'Acme Corporation',
    ]);

    expect($tenant->name)->toBe('Acme Corporation')
        ->and($tenant->slug)->toBe('acme-corporation')
        ->and($tenant->is_active)->toBeTrue();
});

test('root can update tenant', function (): void {
    $root = User::factory()->root()->create();
    $this->actingAs($root);

    $tenant = Tenant::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

    $updated = app(UpdateTenantAction::class)->handle($tenant, ['name' => 'New Name']);

    expect($updated->name)->toBe('New Name')
        ->and($updated->slug)->toBe('new-name');
});

test('root can deactivate tenant', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    $this->actingAs($root);

    app(DeactivateTenantAction::class)->handle($tenant);

    expect($tenant->fresh()->is_active)->toBeFalse()
        ->and($user->fresh()->is_active)->toBeFalse();
});

test('tenant_admin cannot access root tenant routes', function (): void {
    $tenant = Tenant::factory()->create();
    $admin  = User::factory()->tenantAdmin($tenant)->create();

    $this->actingAs($admin)
        ->get('/root/tenants')
        ->assertStatus(403);
});

test('tenant_user cannot access root tenant routes', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    $this->actingAs($user)
        ->get('/root/tenants')
        ->assertStatus(403);
});

test('unauthenticated user is redirected', function (): void {
    $this->get('/root/tenants')
        ->assertRedirect('/login');
});
