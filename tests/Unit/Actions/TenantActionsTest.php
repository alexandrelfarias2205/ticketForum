<?php declare(strict_types=1);

use App\Actions\Tenants\DeactivateTenantAction;
use App\Actions\Tenants\UpdateTenantAction;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('UpdateTenantAction updates name and regenerates slug', function (): void {
    $tenant = Tenant::factory()->create(['name' => 'Old Corp', 'slug' => 'old-corp']);

    $updated = app(UpdateTenantAction::class)->handle($tenant, [
        'name'      => 'New Corp',
        'plan'      => 'pro',
        'is_active' => true,
    ]);

    expect($updated->name)->toBe('New Corp')
        ->and($updated->slug)->toBe('new-corp')
        ->and($updated->plan->value)->toBe('pro');
});

test('UpdateTenantAction keeps slug when name unchanged', function (): void {
    $tenant = Tenant::factory()->create(['name' => 'Stable Corp', 'slug' => 'stable-corp']);

    $updated = app(UpdateTenantAction::class)->handle($tenant, [
        'name'      => 'Stable Corp',
        'plan'      => 'free',
        'is_active' => true,
    ]);

    expect($updated->slug)->toBe('stable-corp');
});

test('DeactivateTenantAction deactivates tenant and all its users', function (): void {
    $tenant = Tenant::factory()->create(['is_active' => true]);
    $user1  = User::factory()->tenantUser($tenant)->create(['is_active' => true]);
    $user2  = User::factory()->tenantAdmin($tenant)->create(['is_active' => true]);

    app(DeactivateTenantAction::class)->handle($tenant);

    expect($tenant->fresh()->is_active)->toBeFalse()
        ->and($user1->fresh()->is_active)->toBeFalse()
        ->and($user2->fresh()->is_active)->toBeFalse();
});
