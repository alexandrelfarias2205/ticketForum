<?php declare(strict_types=1);

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user belonging to inactive tenant is blocked from tenant routes', function (): void {
    $tenant = Tenant::factory()->create(['is_active' => false]);
    $user   = User::factory()->tenantUser($tenant)->create();

    $this->actingAs($user)
        ->get(route('app.reports.index'))
        ->assertStatus(403);
});

test('user belonging to active tenant can access tenant routes', function (): void {
    $tenant = Tenant::factory()->create(['is_active' => true]);
    $user   = User::factory()->tenantUser($tenant)->create();

    $this->actingAs($user)
        ->get(route('app.reports.index'))
        ->assertStatus(200);
});
