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
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

test('high confidence duplicate marks report and dispatches EnrichExistingIssueJob', function (): void {
    Queue::fake();

    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->bug()->approved()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    // No TenantIntegration so existingIssues will be empty — mock will return duplicate regardless
    $mockSimilarity = Mockery::mock(IssueSimilarityService::class);
    $mockSimilarity->shouldReceive('findSimilar')
        ->once()
        ->andReturn([
            'is_duplicate'     => true,
            'matched_issue_id' => 'EXT-999',
            'confidence'       => 0.95,
        ]);

    $mockDispatcher = Mockery::mock(DispatchIssueCreationAction::class);
    $mockDispatcher->shouldNotReceive('handle');

    $job = new CheckDuplicateIssueJob((string) $report->id);
    $job->handle($mockSimilarity, $mockDispatcher);

    $report->refresh();
    expect($report->is_duplicate)->toBeTrue();

    Queue::assertPushed(EnrichExistingIssueJob::class);
});

test('no duplicate dispatches DispatchIssueCreationAction', function (): void {
    Queue::fake();

    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->bug()->approved()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    $mockSimilarity = Mockery::mock(IssueSimilarityService::class);
    $mockSimilarity->shouldReceive('findSimilar')
        ->once()
        ->andReturn([
            'is_duplicate'     => false,
            'matched_issue_id' => null,
            'confidence'       => 0.1,
        ]);

    $dispatched = false;
    $mockDispatcher = Mockery::mock(DispatchIssueCreationAction::class);
    $mockDispatcher->shouldReceive('handle')->once()->andReturnUsing(function () use (&$dispatched): void {
        $dispatched = true;
    });

    $job = new CheckDuplicateIssueJob((string) $report->id);
    $job->handle($mockSimilarity, $mockDispatcher);

    expect($dispatched)->toBeTrue();
    expect($report->fresh()->is_duplicate)->toBeFalse();
});

test('non-bug report is skipped silently', function (): void {
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->improvement()->approved()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    $mockSimilarity = Mockery::mock(IssueSimilarityService::class);
    $mockSimilarity->shouldNotReceive('findSimilar');

    $mockDispatcher = Mockery::mock(DispatchIssueCreationAction::class);
    $mockDispatcher->shouldNotReceive('handle');

    $job = new CheckDuplicateIssueJob((string) $report->id);
    $job->handle($mockSimilarity, $mockDispatcher);

    // Assert no changes
    expect($report->fresh()->is_duplicate)->toBeFalse();
});
