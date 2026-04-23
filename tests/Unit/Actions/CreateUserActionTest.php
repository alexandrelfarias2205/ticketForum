<?php declare(strict_types=1);

use App\Actions\Users\CreateUserAction;
use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('creates user with hashed password', function (): void {
    $user = app(CreateUserAction::class)->handle([
        'name'     => 'Test User',
        'email'    => 'test@example.com',
        'password' => 'plaintext',
        'role'     => UserRole::TenantUser,
    ]);

    expect($user)->toBeInstanceOf(User::class)
        ->and(Hash::check('plaintext', $user->getAuthPassword()))->toBeTrue();
});

test('assigns tenant_id from tenant object', function (): void {
    $tenant = Tenant::factory()->create();

    $user = app(CreateUserAction::class)->handle([
        'name'     => 'Tenant User',
        'email'    => 'tenant@example.com',
        'password' => 'secret',
        'role'     => UserRole::TenantUser,
    ], $tenant);

    expect($user->tenant_id)->toBe($tenant->id);
});

test('creates root user with null tenant_id', function (): void {
    $user = app(CreateUserAction::class)->handle([
        'name'      => 'Root User',
        'email'     => 'root@example.com',
        'password'  => 'secret',
        'role'      => UserRole::Root,
        'tenant_id' => null,
    ]);

    expect($user->tenant_id)->toBeNull()
        ->and($user->role)->toBe(UserRole::Root);
});
