<?php declare(strict_types=1);

use App\Jobs\CreateGitHubIssueJob;
use App\Jobs\CreateJiraIssueJob;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\TenantIntegration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

test('root can dispatch jira issue creation', function (): void {
    Queue::fake();

    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    TenantIntegration::withoutGlobalScopes()->create([
        'tenant_id' => $tenant->id,
        'platform'  => 'jira',
        'config'    => encrypt([
            'email'       => 'user@example.com',
            'api_token'   => 'token',
            'base_url'    => 'https://example.atlassian.net',
            'project_key' => 'PROJ',
        ]),
        'is_active' => true,
    ]);

    $report = Report::factory()->approved()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    $this->actingAs($root)
        ->post(route('root.reports.create-issue', $report))
        ->assertRedirect();

    Queue::assertPushedOn('integrations', CreateJiraIssueJob::class);
});

test('root can dispatch github issue creation', function (): void {
    Queue::fake();

    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    TenantIntegration::withoutGlobalScopes()->create([
        'tenant_id' => $tenant->id,
        'platform'  => 'github',
        'config'    => encrypt([
            'token' => 'ghp_secret',
            'owner' => 'acme',
            'repo'  => 'my-repo',
        ]),
        'is_active' => true,
    ]);

    $report = Report::factory()->approved()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    $this->actingAs($root)
        ->post(route('root.reports.create-issue', $report))
        ->assertRedirect();

    Queue::assertPushedOn('integrations', CreateGitHubIssueJob::class);
});

test('dispatch is idempotent when report already has external_issue_id', function (): void {
    Queue::fake();

    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    TenantIntegration::withoutGlobalScopes()->create([
        'tenant_id' => $tenant->id,
        'platform'  => 'jira',
        'config'    => encrypt([
            'email'       => 'user@example.com',
            'api_token'   => 'token',
            'base_url'    => 'https://example.atlassian.net',
            'project_key' => 'PROJ',
        ]),
        'is_active' => true,
    ]);

    $report = Report::factory()->approved()->create([
        'tenant_id'         => $tenant->id,
        'author_id'         => $author->id,
        'external_issue_id' => 'PROJ-42',
        'external_issue_url' => 'https://example.atlassian.net/browse/PROJ-42',
    ]);

    $response = $this->actingAs($root)
        ->post(route('root.reports.create-issue', $report));

    Queue::assertNothingPushed();
    $response->assertRedirect();
    $response->assertSessionHas('info');
});

test('tenant_admin cannot create issue', function (): void {
    Queue::fake();

    $tenant      = Tenant::factory()->create();
    $tenantAdmin = User::factory()->tenantAdmin($tenant)->create();
    $author      = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->approved()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    $this->actingAs($tenantAdmin)
        ->post(route('root.reports.create-issue', $report))
        ->assertStatus(403);

    Queue::assertNothingPushed();
});
