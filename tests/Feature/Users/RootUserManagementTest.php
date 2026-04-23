<?php declare(strict_types=1);

use App\Actions\Users\CreateUserAction;
use App\Actions\Users\DeactivateUserAction;
use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('root can view all users', function (): void {
    $root = User::factory()->root()->create();

    $this->actingAs($root)
        ->get('/root/users')
        ->assertStatus(200);
});

test('root can create tenant user', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();

    $this->actingAs($root);

    $user = app(CreateUserAction::class)->handle([
        'name'     => 'John Doe',
        'email'    => 'john@example.com',
        'password' => 'secret123',
        'role'     => UserRole::TenantUser,
    ], $tenant);

    expect($user->tenant_id)->toBe($tenant->id)
        ->and($user->role)->toBe(UserRole::TenantUser)
        ->and($user->is_active)->toBeTrue();
});

test('root can deactivate user', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();
    $target = User::factory()->tenantUser($tenant)->create();

    $this->actingAs($root);

    app(DeactivateUserAction::class)->handle($target);

    expect($target->fresh()->is_active)->toBeFalse();
});

test('tenant_admin cannot access root user routes', function (): void {
    $tenant = Tenant::factory()->create();
    $admin  = User::factory()->tenantAdmin($tenant)->create();

    $this->actingAs($admin)
        ->get('/root/users')
        ->assertStatus(403);
});
