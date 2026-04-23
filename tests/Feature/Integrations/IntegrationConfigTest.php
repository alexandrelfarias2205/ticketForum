<?php declare(strict_types=1);

use App\Models\Tenant;
use App\Models\TenantIntegration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('root can view integration config page', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();

    $this->actingAs($root)
        ->get(route('root.tenants.integration.edit', $tenant))
        ->assertStatus(200);
});

test('root can save jira config', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();

    $this->actingAs($root)
        ->post(route('root.tenants.integration.jira', $tenant), [
            'email'       => 'user@example.com',
            'api_token'   => 'secret-token',
            'base_url'    => 'https://example.atlassian.net',
            'project_key' => 'PROJ',
        ])
        ->assertRedirect(route('root.tenants.integration.edit', $tenant));

    $this->assertDatabaseHas('tenant_integrations', [
        'tenant_id' => $tenant->id,
        'platform'  => 'jira',
        'is_active' => true,
    ]);
});

test('root can save github config', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();

    $this->actingAs($root)
        ->post(route('root.tenants.integration.github', $tenant), [
            'token' => 'ghp_secret',
            'owner' => 'acme-org',
            'repo'  => 'my-repo',
        ])
        ->assertRedirect(route('root.tenants.integration.edit', $tenant));

    $this->assertDatabaseHas('tenant_integrations', [
        'tenant_id' => $tenant->id,
        'platform'  => 'github',
        'is_active' => true,
    ]);
});

test('config is stored encrypted', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();

    $this->actingAs($root)
        ->post(route('root.tenants.integration.jira', $tenant), [
            'email'       => 'user@example.com',
            'api_token'   => 'my-plain-token',
            'base_url'    => 'https://example.atlassian.net',
            'project_key' => 'PROJ',
        ]);

    $raw = DB::table('tenant_integrations')
        ->where('tenant_id', $tenant->id)
        ->value('config');

    expect($raw)->not->toContain('my-plain-token');
});

test('tenant_admin cannot access integration config', function (): void {
    $tenant      = Tenant::factory()->create();
    $tenantAdmin = User::factory()->tenantAdmin($tenant)->create();

    $this->actingAs($tenantAdmin)
        ->get(route('root.tenants.integration.edit', $tenant))
        ->assertStatus(403);
});
