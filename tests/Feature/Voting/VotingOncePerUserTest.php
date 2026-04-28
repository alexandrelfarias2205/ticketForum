<?php declare(strict_types=1);

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can vote once and vote_count increments', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->create([
        'tenant_id'  => $tenant->id,
        'author_id'  => $author->id,
        'status'     => ReportStatus::PublishedForVoting,
        'vote_count' => 0,
    ]);

    $response = $this->actingAs($user)
        ->postJson(route('app.votes.toggle', $report));

    $response->assertOk()
        ->assertJson(['voted' => true, 'vote_count' => 1]);

    expect($report->fresh()->vote_count)->toBe(1);
    expect(Vote::where('report_id', $report->id)->where('user_id', $user->id)->exists())->toBeTrue();
});

test('second vote attempt on same report retracts the vote', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->create([
        'tenant_id'  => $tenant->id,
        'author_id'  => $author->id,
        'status'     => ReportStatus::PublishedForVoting,
        'vote_count' => 0,
    ]);

    // First vote
    $this->actingAs($user)->postJson(route('app.votes.toggle', $report));

    // Second attempt — should toggle off, not 500
    $response = $this->actingAs($user)->postJson(route('app.votes.toggle', $report));

    $response->assertOk()
        ->assertJson(['voted' => false, 'vote_count' => 0]);

    expect(Vote::where('report_id', $report->id)->where('user_id', $user->id)->count())->toBe(0);
});

test('two different users each get their own vote counted', function (): void {
    $tenant  = Tenant::factory()->create();
    $userA   = User::factory()->tenantUser($tenant)->create();
    $userB   = User::factory()->tenantUser($tenant)->create();
    $author  = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->create([
        'tenant_id'  => $tenant->id,
        'author_id'  => $author->id,
        'status'     => ReportStatus::PublishedForVoting,
        'vote_count' => 0,
    ]);

    $this->actingAs($userA)->postJson(route('app.votes.toggle', $report));
    $this->actingAs($userB)->postJson(route('app.votes.toggle', $report));

    expect($report->fresh()->vote_count)->toBe(2);
    expect(Vote::where('report_id', $report->id)->count())->toBe(2);
});

test('unauthenticated user cannot vote', function (): void {
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
        'status'    => ReportStatus::PublishedForVoting,
    ]);

    $this->postJson(route('app.votes.toggle', $report))
        ->assertUnauthorized();
});
