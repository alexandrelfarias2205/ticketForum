<?php declare(strict_types=1);

use App\Enums\ExternalPlatform;
use App\Enums\IntegrationJobStatus;
use App\Jobs\CreateGitHubIssueJob;
use App\Models\IntegrationJob;
use App\Models\Product;
use App\Models\ProductIntegration;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(Tests\TestCase::class, RefreshDatabase::class);

function makeGitHubSetup(): array
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
        'platform'   => ExternalPlatform::GitHub,
        'config'     => encrypt([
            'token' => 'ghp_secret',
            'owner' => 'acme',
            'repo'  => 'my-repo',
        ]),
        'is_active'  => true,
    ]);

    $integrationJob = IntegrationJob::create([
        'report_id' => $report->id,
        'platform'  => 'github',
        'status'    => IntegrationJobStatus::Pending,
    ]);

    return [$report, $integrationJob];
}

test('job creates github issue and updates report', function (): void {
    Http::fake([
        'api.github.com/*' => Http::response([
            'number'   => 42,
            'html_url' => 'https://github.com/acme/my-repo/issues/42',
        ], 201),
    ]);

    [$report, $integrationJob] = makeGitHubSetup();

    (new CreateGitHubIssueJob((string) $report->id, (string) $integrationJob->id))->handle();

    $report->refresh();
    $integrationJob->refresh();

    expect($report->external_issue_id)->toBe('42')
        ->and($report->external_platform->value)->toBe('github')
        ->and($report->external_issue_url)->toBe('https://github.com/acme/my-repo/issues/42')
        ->and($integrationJob->status)->toBe(IntegrationJobStatus::Done)
        ->and($integrationJob->external_id)->toBe('42');
});

test('job is idempotent when report already has external_issue_id', function (): void {
    Http::fake();

    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->approved()->create([
        'tenant_id'          => $tenant->id,
        'author_id'          => $author->id,
        'external_issue_id'  => '99',
        'external_issue_url' => 'https://github.com/acme/my-repo/issues/99',
        'external_platform'  => 'github',
    ]);

    $integrationJob = IntegrationJob::create([
        'report_id' => $report->id,
        'platform'  => 'github',
        'status'    => IntegrationJobStatus::Pending,
    ]);

    (new CreateGitHubIssueJob((string) $report->id, (string) $integrationJob->id))->handle();

    Http::assertNothingSent();

    expect($integrationJob->fresh()->status)->toBe(IntegrationJobStatus::Done);
});

test('job marks failed on error', function (): void {
    Http::fake([
        'api.github.com/*' => Http::response(['message' => 'Not Found'], 404),
    ]);

    [$report, $integrationJob] = makeGitHubSetup();

    $job = new CreateGitHubIssueJob((string) $report->id, (string) $integrationJob->id);

    try {
        $job->handle();
    } catch (\Throwable $e) {
        $job->failed($e);
    }

    expect($integrationJob->fresh()->status)->toBe(IntegrationJobStatus::Failed);
});
