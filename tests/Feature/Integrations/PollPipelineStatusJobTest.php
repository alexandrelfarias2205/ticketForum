<?php declare(strict_types=1);

use App\Enums\ExternalPlatform;
use App\Enums\IntegrationJobStatus;
use App\Events\PipelineFailed;
use App\Events\PipelineSucceeded;
use App\Models\AgentLog;
use App\Models\IntegrationJob;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\TenantIntegration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function makeGitHubReport(): Report
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

function makeGitLabReport(): Report
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

test('github pipeline success: logs success status and dispatches OpenMergeRequestJob', function (): void {
    Queue::fake();

    Http::fake([
        'https://api.github.com/*' => Http::response([
            'workflow_runs' => [[
                'status'     => 'completed',
                'conclusion' => 'success',
            ]],
        ], 200),
    ]);

    $report = makeGitHubReport();

    (new App\Jobs\PollPipelineStatusJob((string) $report->id, 'fix/branch-1'))->handle();

    $log = AgentLog::where('report_id', $report->id)->latest()->first();
    expect($log)->not->toBeNull()
        ->and($log->payload['status'])->toBe('success');

    Queue::assertPushed(App\Jobs\OpenMergeRequestJob::class);
});

test('gitlab pipeline success: logs success status', function (): void {
    Queue::fake();

    Http::fake([
        'https://gitlab.com/*' => Http::response([
            ['status' => 'success'],
        ], 200),
    ]);

    $report = makeGitLabReport();

    (new App\Jobs\PollPipelineStatusJob((string) $report->id, 'fix/branch-1'))->handle();

    $log = AgentLog::where('report_id', $report->id)->latest()->first();
    expect($log)->not->toBeNull()
        ->and($log->payload['status'])->toBe('success');
});

test('max poll attempts exhausted creates failed IntegrationJob', function (): void {
    Queue::fake();

    Http::fake([
        'https://api.github.com/*' => Http::response([
            'workflow_runs' => [[
                'status'     => 'in_progress',
                'conclusion' => null,
            ]],
        ], 200),
    ]);

    $report = makeGitHubReport();

    (new App\Jobs\PollPipelineStatusJob((string) $report->id, 'fix/branch-1', 30))->handle();

    $failedJob = IntegrationJob::where('report_id', $report->id)
        ->where('status', IntegrationJobStatus::Failed)
        ->exists();

    expect($failedJob)->toBeTrue();
});

test('github pipeline failure creates failed IntegrationJob', function (): void {
    Queue::fake();

    Http::fake([
        'https://api.github.com/*' => Http::response([
            'workflow_runs' => [[
                'status'     => 'completed',
                'conclusion' => 'failure',
            ]],
        ], 200),
    ]);

    $report = makeGitHubReport();

    (new App\Jobs\PollPipelineStatusJob((string) $report->id, 'fix/branch-1'))->handle();

    $failedJob = IntegrationJob::where('report_id', $report->id)
        ->where('status', IntegrationJobStatus::Failed)
        ->exists();

    expect($failedJob)->toBeTrue();
});
