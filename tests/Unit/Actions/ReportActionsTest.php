<?php declare(strict_types=1);

use App\Actions\Reports\ApproveReportAction;
use App\Actions\Reports\CreateReportAction;
use App\Actions\Reports\PublishReportAction;
use App\Actions\Reports\RejectReportAction;
use App\Actions\Reports\StoreAttachmentAction;
use App\Enums\ReportStatus;
use App\Models\Label;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('CreateReportAction sets correct tenant and author', function (): void {
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = app(CreateReportAction::class)->handle($author, [
        'type'        => 'bug',
        'title'       => 'Login page crashes on mobile',
        'description' => 'Reproducible on iOS Safari 17.',
    ]);

    expect($report)->toBeInstanceOf(Report::class)
        ->and((string) $report->tenant_id)->toBe((string) $tenant->id)
        ->and((string) $report->author_id)->toBe((string) $author->id)
        ->and($report->status)->toBe(ReportStatus::PendingReview);
});

test('ApproveReportAction sets status and reviewer and syncs labels', function (): void {
    $tenant   = Tenant::factory()->create();
    $author   = User::factory()->tenantUser($tenant)->create();
    $reviewer = User::factory()->root()->create();

    $report = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    $label = Label::create([
        'id'         => Str::uuid(),
        'name'       => 'Backend',
        'color'      => '#0000ff',
        'created_by' => $reviewer->id,
    ]);

    $approved = app(ApproveReportAction::class)->handle($report, $reviewer, [
        'enriched_title'       => 'Enriched: Login page crashes on mobile',
        'enriched_description' => 'Enriched: Reproducible on iOS Safari 17 when viewport is under 375px.',
        'label_ids'            => [(string) $label->id],
    ]);

    expect($approved->status)->toBe(ReportStatus::Approved)
        ->and((string) $approved->reviewer_id)->toBe((string) $reviewer->id)
        ->and($approved->enriched_title)->toBe('Enriched: Login page crashes on mobile')
        ->and($approved->labels->pluck('id')->map(fn ($id) => (string) $id)->toArray())
        ->toContain((string) $label->id);
});

test('RejectReportAction sets status to rejected', function (): void {
    $tenant   = Tenant::factory()->create();
    $author   = User::factory()->tenantUser($tenant)->create();
    $reviewer = User::factory()->root()->create();

    $report = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    $rejected = app(RejectReportAction::class)->handle($report, $reviewer, 'Not reproducible.');

    expect($rejected->status)->toBe(ReportStatus::Rejected)
        ->and((string) $rejected->reviewer_id)->toBe((string) $reviewer->id);
});

test('PublishReportAction throws LogicException if not approved', function (): void {
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    $this->expectException(\LogicException::class);

    app(PublishReportAction::class)->handle($report);
});

test('PublishReportAction sets status to published_for_voting when approved', function (): void {
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->approved()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    $published = app(PublishReportAction::class)->handle($report);

    expect($published->status)->toBe(ReportStatus::PublishedForVoting)
        ->and($published->published_at)->not->toBeNull();
});

test('StoreAttachmentAction stores file with UUID name and creates attachment record', function (): void {
    Storage::fake('private');

    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    $file = UploadedFile::fake()->image('my-screenshot.png', 1024, 768);

    $attachment = app(StoreAttachmentAction::class)->handle($report, $file);

    expect($attachment->id)->not->toBeNull()
        ->and((string) $attachment->report_id)->toBe((string) $report->id)
        ->and($attachment->filename)->toBe('my-screenshot.png')
        ->and($attachment->url)->toStartWith("reports/{$report->id}/attachments/");

    // Verify the stored filename uses a UUID (not the original name)
    $storedFilename = basename($attachment->url);
    $uuidPart       = pathinfo($storedFilename, PATHINFO_FILENAME);
    expect(Str::isUuid($uuidPart))->toBeTrue();

    Storage::disk('private')->assertExists($attachment->url);
});
