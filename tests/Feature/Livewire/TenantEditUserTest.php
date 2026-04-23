<?php declare(strict_types=1);

use App\Livewire\Tenant\Users\EditUser;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('tenant_admin cannot mount component for user from another tenant', function (): void {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();
    $admin   = User::factory()->tenantAdmin($tenantA)->create();
    $target  = User::factory()->tenantUser($tenantB)->create();

    $this->actingAs($admin);

    Livewire::test(EditUser::class, ['user' => $target])
        ->assertForbidden();
});

test('component mounts correctly for own tenant user', function (): void {
    $tenant = Tenant::factory()->create();
    $admin  = User::factory()->tenantAdmin($tenant)->create();
    $target = User::factory()->tenantUser($tenant)->create(['name' => 'Target Name']);

    $this->actingAs($admin);

    Livewire::test(EditUser::class, ['user' => $target])
        ->assertSet('name', 'Target Name')
        ->assertSet('role', 'tenant_user');
});

test('component initializes with correct user data', function (): void {
    $tenant = Tenant::factory()->create();
    $admin  = User::factory()->tenantAdmin($tenant)->create();
    $target = User::factory()->tenantUser($tenant)->create([
        'email'     => 'target@example.com',
        'is_active' => true,
    ]);

    $this->actingAs($admin);

    Livewire::test(EditUser::class, ['user' => $target])
        ->assertSet('email', 'target@example.com')
        ->assertSet('is_active', true);
});
