<?php declare(strict_types=1);

use App\Livewire\Root\Tenants\EditTenant;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('root can update tenant via livewire component', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create(['name' => 'Old Name', 'plan' => 'free']);

    $this->actingAs($root);

    Livewire::test(EditTenant::class, ['tenant' => $tenant])
        ->set('name', 'New Name')
        ->set('plan', 'pro')
        ->set('is_active', true)
        ->call('save')
        ->assertHasNoErrors();

    expect($tenant->fresh()->name)->toBe('New Name')
        ->and($tenant->fresh()->plan->value)->toBe('pro');
});

test('save fails validation when name is empty', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();

    $this->actingAs($root);

    Livewire::test(EditTenant::class, ['tenant' => $tenant])
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name']);
});

test('save fails validation with invalid plan', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();

    $this->actingAs($root);

    Livewire::test(EditTenant::class, ['tenant' => $tenant])
        ->set('name', 'Valid Name')
        ->set('plan', 'ultra')
        ->call('save')
        ->assertHasErrors(['plan']);
});

test('tenant_admin is forbidden from editing tenant', function (): void {
    $tenant = Tenant::factory()->create();
    $admin  = User::factory()->tenantAdmin($tenant)->create();

    $this->actingAs($admin);

    Livewire::test(EditTenant::class, ['tenant' => $tenant])
        ->assertForbidden();
});
