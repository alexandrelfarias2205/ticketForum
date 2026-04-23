<?php declare(strict_types=1);

use App\Actions\Reports\DeleteAttachmentAction;
use App\Actions\Reports\StoreLinkAttachmentAction;
use App\Actions\Reports\UpdateReportAction;
use App\Enums\AttachmentType;
use App\Models\Report;
use App\Models\ReportAttachment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('UpdateReportAction updates type, title and description', function (): void {
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();
    $report = Report::factory()->pendingReview()->create([
        'tenant_id'   => $tenant->id,
        'author_id'   => $author->id,
        'title'       => 'Old Title',
        'description' => 'Old desc',
        'type'        => 'bug',
    ]);

    $updated = app(UpdateReportAction::class)->handle($report, [
        'type'        => 'improvement',
        'title'       => 'New Title',
        'description' => 'Updated description text',
    ]);

    expect($updated->title)->toBe('New Title')
        ->and($updated->description)->toBe('Updated description text')
        ->and($updated->type->value)->toBe('improvement');
});

test('StoreLinkAttachmentAction creates link attachment record', function (): void {
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();
    $report = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    $attachment = app(StoreLinkAttachmentAction::class)->handle($report, 'https://example.com');

    expect($attachment->type)->toBe(AttachmentType::Link)
        ->and($attachment->url)->toBe('https://example.com')
        ->and((string) $attachment->report_id)->toBe((string) $report->id);
});

test('DeleteAttachmentAction deletes link attachment record without touching storage', function (): void {
    Storage::fake('private');

    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();
    $report = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    $attachment = ReportAttachment::create([
        'report_id' => $report->id,
        'type'      => AttachmentType::Link,
        'url'       => 'https://example.com',
    ]);

    app(DeleteAttachmentAction::class)->handle($attachment);

    expect(ReportAttachment::find($attachment->id))->toBeNull();
    Storage::disk('private')->assertMissing('https://example.com'); // not stored on disk
});

test('DeleteAttachmentAction removes image file from storage', function (): void {
    Storage::fake('private');

    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();
    $report = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    $path = "reports/{$report->id}/attachments/" . Str::uuid() . '.png';
    Storage::disk('private')->put($path, 'fake-image-content');

    $attachment = ReportAttachment::create([
        'report_id' => $report->id,
        'type'      => AttachmentType::Image,
        'url'       => $path,
        'filename'  => 'screenshot.png',
    ]);

    app(DeleteAttachmentAction::class)->handle($attachment);

    expect(ReportAttachment::find($attachment->id))->toBeNull();
    Storage::disk('private')->assertMissing($path);
});
