<?php declare(strict_types=1);

use App\Enums\ExternalPlatform;
use App\Enums\IntegrationJobStatus;
use App\Enums\ReportStatus;
use App\Jobs\CreateJiraIssueJob;
use App\Models\IntegrationJob;
use App\Models\Product;
use App\Models\ProductIntegration;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(Tests\TestCase::class, RefreshDatabase::class);

function makeJiraSetup(): array
{
    $tenant  = Tenant::factory()->create();
    $author  = User::factory()->tenantUser($tenant)->create();
    $product = Product::factory()->create();
    $tenant->products()->attach($product);

    $report = Report::factory()->approved()->create([
        'tenant_id'  => $tenant->id,
        'author_id'  => $author->id,
        'product_id' => $product->id,
    ]);

    ProductIntegration::create([
        'product_id' => $product->id,
        'platform'   => ExternalPlatform::Jira,
        'config'     => encrypt([
            'email'       => 'user@example.com',
            'api_token'   => 'api-token',
            'base_url'    => 'https://example.atlassian.net',
            'project_key' => 'PROJ',
        ]),
        'is_active'  => true,
    ]);

    $integrationJob = IntegrationJob::create([
        'report_id' => $report->id,
        'platform'  => 'jira',
        'status'    => IntegrationJobStatus::Pending,
    ]);

    return [$report, $integrationJob];
}

test('job creates jira issue and updates report', function (): void {
    Http::fake([
        '*/rest/api/3/issue' => Http::response(['key' => 'PROJ-1'], 201),
    ]);

    [$report, $integrationJob] = makeJiraSetup();

    (new CreateJiraIssueJob((string) $report->id, (string) $integrationJob->id))->handle();

    $report->refresh();
    $integrationJob->refresh();

    expect($report->external_issue_id)->toBe('PROJ-1')
        ->and($report->external_platform->value)->toBe('jira')
        ->and($integrationJob->status)->toBe(IntegrationJobStatus::Done)
        ->and($integrationJob->external_id)->toBe('PROJ-1');
});

test('job is idempotent when report already has external_issue_id', function (): void {
    Http::fake();

    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->approved()->create([
        'tenant_id'          => $tenant->id,
        'author_id'          => $author->id,
        'external_issue_id'  => 'PROJ-99',
        'external_issue_url' => 'https://example.atlassian.net/browse/PROJ-99',
        'external_platform'  => 'jira',
    ]);

    $integrationJob = IntegrationJob::create([
        'report_id' => $report->id,
        'platform'  => 'jira',
        'status'    => IntegrationJobStatus::Pending,
    ]);

    (new CreateJiraIssueJob((string) $report->id, (string) $integrationJob->id))->handle();

    Http::assertNothingSent();

    expect($integrationJob->fresh()->status)->toBe(IntegrationJobStatus::Done);
});

test('job marks integration_job as failed on http error', function (): void {
    Http::fake([
        '*/rest/api/3/issue' => Http::response(['error' => 'Unauthorized'], 401),
    ]);

    [$report, $integrationJob] = makeJiraSetup();

    $job = new CreateJiraIssueJob((string) $report->id, (string) $integrationJob->id);

    try {
        $job->handle();
    } catch (\Throwable $e) {
        $job->failed($e);
    }

    expect($integrationJob->fresh()->status)->toBe(IntegrationJobStatus::Failed);
});
