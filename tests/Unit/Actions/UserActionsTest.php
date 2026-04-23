<?php declare(strict_types=1);

use App\Actions\Users\DeactivateUserAction;
use App\Actions\Users\UpdateUserAction;
use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('UpdateUserAction updates basic user fields', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create(['name' => 'Old Name']);

    $updated = app(UpdateUserAction::class)->handle($user, [
        'name'      => 'New Name',
        'email'     => 'new@example.com',
        'role'      => 'tenant_admin',
        'is_active' => true,
    ]);

    expect($updated->name)->toBe('New Name')
        ->and($updated->email)->toBe('new@example.com')
        ->and($updated->role)->toBe(UserRole::TenantAdmin);
});

test('UpdateUserAction hashes password when provided', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    $updated = app(UpdateUserAction::class)->handle($user, [
        'name'      => $user->name,
        'email'     => $user->email,
        'role'      => $user->role->value,
        'password'  => 'newSecurePassword123',
        'is_active' => true,
    ]);

    expect(Hash::check('newSecurePassword123', $updated->password))->toBeTrue();
});

test('UpdateUserAction does not change password when empty string given', function (): void {
    $tenant          = Tenant::factory()->create();
    $originalPassword = 'password';
    $user            = User::factory()->tenantUser($tenant)->create([
        'password' => Hash::make($originalPassword),
    ]);

    app(UpdateUserAction::class)->handle($user, [
        'name'      => $user->name,
        'email'     => $user->email,
        'role'      => $user->role->value,
        'password'  => '',
        'is_active' => true,
    ]);

    expect(Hash::check($originalPassword, $user->fresh()->password))->toBeTrue();
});

test('DeactivateUserAction sets is_active to false', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create(['is_active' => true]);

    app(DeactivateUserAction::class)->handle($user);

    expect($user->fresh()->is_active)->toBeFalse();
});
