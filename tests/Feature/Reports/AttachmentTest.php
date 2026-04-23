<?php declare(strict_types=1);

use App\Enums\AttachmentType;
use App\Models\Report;
use App\Models\ReportAttachment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('tenant_user can upload image to own report', function (): void {
    Storage::fake('private');

    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $user->id,
    ]);

    // Refresh so UUID attributes are plain strings (for policy === comparison)
    $user->refresh();

    $file = UploadedFile::fake()->image('screenshot.png', 800, 600);

    $this->actingAs($user)
        ->postJson(route('app.reports.attachments.store', $report), [
            'file' => $file,
        ])
        ->assertStatus(201);

    $attachment = ReportAttachment::where('report_id', $report->id)->first();

    expect($attachment)->not->toBeNull()
        ->and($attachment->type)->toBe(AttachmentType::Image)
        ->and($attachment->filename)->toBe('screenshot.png');

    Storage::disk('private')->assertExists($attachment->url);
});

test('tenant_user cannot upload non-image file', function (): void {
    Storage::fake('private');

    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $user->id,
    ]);

    $user->refresh();

    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $this->actingAs($user)
        ->postJson(route('app.reports.attachments.store', $report), [
            'file' => $file,
        ])
        ->assertStatus(422);
});

test('tenant_user can add link to report', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $user->id,
    ]);

    $user->refresh();

    $this->actingAs($user)
        ->postJson(route('app.reports.links.store', $report), [
            'url' => 'https://example.com/related-issue',
        ])
        ->assertStatus(201);

    $attachment = ReportAttachment::where('report_id', $report->id)->first();

    expect($attachment)->not->toBeNull()
        ->and($attachment->type)->toBe(AttachmentType::Link)
        ->and($attachment->url)->toBe('https://example.com/related-issue');
});

test('tenant_user can delete own attachment', function (): void {
    Storage::fake('private');

    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $user->id,
    ]);

    $user->refresh();

    // Store a fake file on the private disk
    $fakePath = "reports/{$report->id}/attachments/test-file.png";
    Storage::disk('private')->put($fakePath, 'fake-image-content');

    $attachment = ReportAttachment::create([
        'report_id'  => $report->id,
        'type'       => AttachmentType::Image,
        'url'        => $fakePath,
        'filename'   => 'test-file.png',
        'size_bytes' => 100,
    ]);

    // Verify the action (policy + file deletion + DB deletion) works correctly
    // by calling it via the service container as the authenticated user.
    $report->refresh();
    $this->actingAs($user);
    $this->assertTrue($user->can('update', $report));

    app(\App\Actions\Reports\DeleteAttachmentAction::class)->handle($attachment);

    expect(ReportAttachment::find($attachment->id))->toBeNull();
    Storage::disk('private')->assertMissing($fakePath);
});

test('tenant_user cannot delete attachment from another tenant\'s report', function (): void {
    Storage::fake('private');

    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $userA   = User::factory()->tenantUser($tenantA)->create();
    $authorB = User::factory()->tenantUser($tenantB)->create();

    $reportB = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenantB->id,
        'author_id' => $authorB->id,
    ]);

    $attachment = ReportAttachment::create([
        'report_id' => $reportB->id,
        'type'      => AttachmentType::Link,
        'url'       => 'https://example.com/link',
    ]);

    // The destroy route resolves {attachment} via route model binding.
    // The controller then authorizes update on $attachment->report — which belongs to tenantB,
    // so userA (tenantA) will be denied by the policy (403).
    $userA->refresh();

    $this->actingAs($userA)
        ->delete(route('app.reports.attachments.destroy', $attachment))
        ->assertStatus(403);
});
