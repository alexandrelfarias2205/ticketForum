<?php declare(strict_types=1);

use App\Actions\Reports\PublishReportAction;
use App\Enums\ReportStatus;
use App\Models\Label;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('root can view review queue', function (): void {
    $root = User::factory()->root()->create();

    $this->actingAs($root)
        ->get(route('root.reports.index'))
        ->assertStatus(200);
});

test('root can approve report with labels', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    $label = Label::create([
        'id'         => Str::uuid(),
        'name'       => 'UI Bug',
        'color'      => '#ff0000',
        'created_by' => $root->id,
    ]);

    $this->actingAs($root)
        ->post(route('root.reports.approve', $report), [
            'enriched_title'       => 'Enriched Title',
            'enriched_description' => 'Enriched description with more detail.',
            'label_ids'            => [$label->id],
        ])
        ->assertRedirect(route('root.reports.show', $report));

    $report->refresh();

    expect($report->status)->toBe(ReportStatus::Approved)
        ->and((string) $report->reviewer_id)->toBe((string) $root->id)
        ->and($report->enriched_title)->toBe('Enriched Title')
        ->and($report->labels()->pluck('labels.id')->toArray())->toContain((string) $label->id);
});

test('root can reject report', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    $this->actingAs($root)
        ->post(route('root.reports.reject', $report), [
            'reason' => 'Does not meet our criteria.',
        ])
        ->assertRedirect(route('root.reports.show', $report));

    expect($report->fresh()->status)->toBe(ReportStatus::Rejected);
});

test('root can publish approved report', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->approved()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    $this->actingAs($root)
        ->post(route('root.reports.publish', $report))
        ->assertRedirect(route('root.reports.show', $report));

    expect($report->fresh()->status)->toBe(ReportStatus::PublishedForVoting);
});

test('root cannot publish pending report', function (): void {
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    $this->expectException(\LogicException::class);

    app(PublishReportAction::class)->handle($report);
});

test('tenant_admin cannot access review queue', function (): void {
    $tenant      = Tenant::factory()->create();
    $tenantAdmin = User::factory()->tenantAdmin($tenant)->create();

    $this->actingAs($tenantAdmin)
        ->get(route('root.reports.index'))
        ->assertStatus(403);
});
