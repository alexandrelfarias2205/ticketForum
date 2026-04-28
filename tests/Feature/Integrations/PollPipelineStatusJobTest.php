<?php declare(strict_types=1);

use App\Enums\ExternalPlatform;
use App\Events\PipelineFailed;
use App\Events\PipelineSucceeded;
use App\Jobs\PollPipelineStatusJob;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\TenantIntegration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function makeReportWithGitHubIntegration(): Report
{
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->approved()->create([
        'tenant_id'         => $tenant->id,
        'author_id'         => $author->id,
        'external_platform' => ExternalPlatform::GitHub,
        'external_issue_id' => '10',
    ]);

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

    return $report;
}

function makeReportWithGitLabIntegration(): Report
{
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->approved()->create([
        'tenant_id'         => $tenant->id,
        'author_id'         => $author->id,
        'external_platform' => ExternalPlatform::GitLab,
        'external_issue_id' => '7',
    ]);

    TenantIntegration::withoutGlobalScopes()->create([
        'tenant_id' => $tenant->id,
        'platform'  => 'gitlab',
        'config'    => encrypt([
            'token'      => 'glpat-secret',
            'project_id' => '123',
            'base_url'   => 'https://gitlab.com',
        ]),
        'is_active' => true,
    ]);

    return $report;
}

test('detects github pipeline success and fires PipelineSucceeded event', function (): void {
    Event::fake();
    Queue::fake();

    Http::fake([
        'api.github.com/*' => Http::response([
            'workflow_runs' => [[
                'status'     => 'completed',
                'conclusion' => 'success',
            ]],
        ], 200),
    ]);

    $report = makeReportWithGitHubIntegration();

    (new PollPipelineStatusJob((string) $report->id, 'fix/branch-1'))->handle();

    Event::assertDispatched(PipelineSucceeded::class, fn ($e): bool => $e->report->id === $report->id);
});

test('detects gitlab pipeline success and fires PipelineSucceeded event', function (): void {
    Event::fake();
    Queue::fake();

    Http::fake([
        'gitlab.com/*' => Http::response([
            ['status' => 'success'],
        ], 200),
    ]);

    $report = makeReportWithGitLabIntegration();

    (new PollPipelineStatusJob((string) $report->id, 'fix/branch-1'))->handle();

    Event::assertDispatched(PipelineSucceeded::class, fn ($e): bool => $e->report->id === $report->id);
});

test('max poll attempts exhausted fires PipelineFailed event', function (): void {
    Event::fake();
    Queue::fake();

    Http::fake([
        'api.github.com/*' => Http::response([
            'workflow_runs' => [[
                'status'     => 'in_progress',
                'conclusion' => null,
            ]],
        ], 200),
    ]);

    $report = makeReportWithGitHubIntegration();

    // Pass pollAttempt = MAX_POLLS (30) so next schedule triggers failure
    (new PollPipelineStatusJob((string) $report->id, 'fix/branch-1', 30))->handle();

    Event::assertDispatched(PipelineFailed::class, fn ($e): bool => $e->report->id === $report->id);
});

test('github pipeline failure fires PipelineFailed event', function (): void {
    Event::fake();
    Queue::fake();

    Http::fake([
        'api.github.com/*' => Http::response([
            'workflow_runs' => [[
                'status'     => 'completed',
                'conclusion' => 'failure',
            ]],
        ], 200),
    ]);

    $report = makeReportWithGitHubIntegration();

    (new PollPipelineStatusJob((string) $report->id, 'fix/branch-1'))->handle();

    Event::assertDispatched(PipelineFailed::class);
});
