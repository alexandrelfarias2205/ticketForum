<?php declare(strict_types=1);

use App\Actions\Integrations\DispatchIssueCreationAction;
use App\Jobs\CheckDuplicateIssueJob;
use App\Jobs\EnrichExistingIssueJob;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\TenantIntegration;
use App\Models\User;
use App\Services\AI\IssueSimilarityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Config::set('services.anthropic.api_key', 'test-key');
    Config::set('services.anthropic.api_url', 'https://api.anthropic.com/v1/messages');
    Config::set('services.anthropic.version', '2023-06-01');
    Config::set('services.anthropic.similarity_model', 'claude-haiku-4-5');
});

test('duplicate is marked when platform returns matching issues and api says duplicate', function (): void {
    Queue::fake();

    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->bug()->approved()->create([
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

    Http::fake([
        'https://gitlab.com/*'       => Http::response([
            ['iid' => 5, 'title' => $report->title, 'description' => $report->description],
        ], 200),
        'https://api.anthropic.com/*' => Http::response([
            'content' => [
                ['type' => 'text', 'text' => json_encode([
                    'is_duplicate'     => true,
                    'matched_issue_id' => '5',
                    'confidence'       => 0.95,
                ])],
            ],
        ], 200),
    ]);

    $job = new CheckDuplicateIssueJob((string) $report->id);
    $job->handle(app(IssueSimilarityService::class), app(DispatchIssueCreationAction::class));

    expect($report->fresh()->is_duplicate)->toBeTrue();
    Queue::assertPushed(EnrichExistingIssueJob::class);
});

test('no duplicate proceeds without marking report as duplicate', function (): void {
    Queue::fake();

    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->bug()->approved()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    // No TenantIntegration → existingIssues=[] → findSimilar short-circuits, returns not duplicate
    $job = new CheckDuplicateIssueJob((string) $report->id);

    // DispatchIssueCreationAction will throw since no integration — but command catches it
    // We catch it here to test the duplicate-check path only
    try {
        $job->handle(app(IssueSimilarityService::class), app(DispatchIssueCreationAction::class));
    } catch (Throwable) {
        // Expected: no integration configured — DispatchIssueCreationAction throws
    }

    expect($report->fresh()->is_duplicate)->toBeFalse();
});

test('non-bug report is skipped and not marked duplicate', function (): void {
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->improvement()->approved()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    Http::fake(); // should not be called

    $job = new CheckDuplicateIssueJob((string) $report->id);
    $job->handle(app(IssueSimilarityService::class), app(DispatchIssueCreationAction::class));

    Http::assertNothingSent();
    expect($report->fresh()->is_duplicate)->toBeFalse();
});
