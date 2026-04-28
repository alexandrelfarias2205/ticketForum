<?php declare(strict_types=1);

use App\Enums\ExternalPlatform;
use App\Enums\IntegrationJobStatus;
use App\Jobs\CreateGitLabIssueJob;
use App\Models\IntegrationJob;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\TenantIntegration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function makeGitLabSetup(): array
{
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->approved()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
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

    $integrationJob = IntegrationJob::create([
        'report_id' => $report->id,
        'platform'  => 'gitlab',
        'status'    => IntegrationJobStatus::Pending,
    ]);

    return [$report, $integrationJob];
}

test('job creates gitlab issue and updates report', function (): void {
    Http::fake([
        'gitlab.com/*' => Http::response([
            'iid'     => 7,
            'web_url' => 'https://gitlab.com/acme/repo/-/issues/7',
        ], 201),
    ]);

    [$report, $integrationJob] = makeGitLabSetup();

    (new CreateGitLabIssueJob((string) $report->id, (string) $integrationJob->id))->handle();

    $report->refresh();
    $integrationJob->refresh();

    expect($report->external_issue_id)->toBe('7')
        ->and($report->external_platform)->toBe(ExternalPlatform::GitLab)
        ->and($report->external_issue_url)->toBe('https://gitlab.com/acme/repo/-/issues/7')
        ->and($integrationJob->status)->toBe(IntegrationJobStatus::Done)
        ->and($integrationJob->external_id)->toBe('7');
});

test('job is idempotent when report already has external_issue_id', function (): void {
    Http::fake();

    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->approved()->create([
        'tenant_id'         => $tenant->id,
        'author_id'         => $author->id,
        'external_issue_id' => '5',
        'external_platform' => 'gitlab',
    ]);

    $integrationJob = IntegrationJob::create([
        'report_id' => $report->id,
        'platform'  => 'gitlab',
        'status'    => IntegrationJobStatus::Pending,
    ]);

    (new CreateGitLabIssueJob((string) $report->id, (string) $integrationJob->id))->handle();

    Http::assertNothingSent();
    expect($integrationJob->fresh()->status)->toBe(IntegrationJobStatus::Done);
});

test('job marks integration job failed when api returns error', function (): void {
    Http::fake([
        'gitlab.com/*' => Http::response(['message' => 'Unauthorized'], 401),
    ]);

    [$report, $integrationJob] = makeGitLabSetup();

    $job = new CreateGitLabIssueJob((string) $report->id, (string) $integrationJob->id);

    try {
        $job->handle();
    } catch (Throwable $e) {
        $job->failed($e);
    }

    expect($integrationJob->fresh()->status)->toBe(IntegrationJobStatus::Failed)
        ->and($integrationJob->fresh()->error_message)->not->toBeNull();
});
