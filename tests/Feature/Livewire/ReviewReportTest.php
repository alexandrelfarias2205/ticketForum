<?php declare(strict_types=1);

use App\Livewire\Root\Reports\ReviewReport;
use App\Models\Label;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('root can approve report via livewire component', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();
    $report = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    $this->actingAs($root);

    Livewire::test(ReviewReport::class, ['report' => $report])
        ->set('enrichedTitle', 'Enriched title for testing')
        ->set('enrichedDescription', 'Enriched description that is long enough.')
        ->call('approve')
        ->assertHasNoErrors()
        ->assertRedirect();

    expect($report->fresh()->status->value)->toBe('approved');
});

test('root can reject report via livewire component', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();
    $report = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    $this->actingAs($root);

    Livewire::test(ReviewReport::class, ['report' => $report])
        ->set('showRejectModal', true)
        ->set('rejectReason', 'Not enough details provided.')
        ->call('confirmReject')
        ->assertHasNoErrors()
        ->assertRedirect();

    expect($report->fresh()->status->value)->toBe('rejected');
});

test('reject fails validation when reason is too short', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();
    $report = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    $this->actingAs($root);

    Livewire::test(ReviewReport::class, ['report' => $report])
        ->set('rejectReason', 'No')
        ->call('confirmReject')
        ->assertHasErrors(['rejectReason']);
});

test('tenant_admin cannot mount review report component', function (): void {
    $tenant = Tenant::factory()->create();
    $admin  = User::factory()->tenantAdmin($tenant)->create();
    $author = User::factory()->tenantUser($tenant)->create();
    $report = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    $this->actingAs($admin);

    Livewire::test(ReviewReport::class, ['report' => $report])
        ->assertForbidden();
});

test('root can publish approved report via livewire component', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();
    $report = Report::factory()->approved()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    $this->actingAs($root);

    Livewire::test(ReviewReport::class, ['report' => $report])
        ->call('publish')
        ->assertHasNoErrors();

    expect($report->fresh()->status->value)->toBe('published_for_voting');
});
