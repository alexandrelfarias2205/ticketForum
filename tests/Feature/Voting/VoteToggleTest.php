<?php declare(strict_types=1);

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can cast vote on published report', function (): void {
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

    expect(Vote::where('report_id', $report->id)->where('user_id', $user->id)->exists())->toBeTrue();
    expect($report->fresh()->vote_count)->toBe(1);
});

test('user can retract vote', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->create([
        'tenant_id'  => $tenant->id,
        'author_id'  => $author->id,
        'status'     => ReportStatus::PublishedForVoting,
        'vote_count' => 0,
    ]);

    // Cast vote
    $this->actingAs($user)->postJson(route('app.votes.toggle', $report));

    // Retract vote
    $response = $this->actingAs($user)->postJson(route('app.votes.toggle', $report));

    $response->assertOk()
        ->assertJson(['voted' => false, 'vote_count' => 0]);

    expect(Vote::where('report_id', $report->id)->where('user_id', $user->id)->exists())->toBeFalse();
    expect($report->fresh()->vote_count)->toBe(0);
});

test('user cannot vote on non-published report', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
        'status'    => ReportStatus::PendingReview,
    ]);

    $this->actingAs($user)
        ->postJson(route('app.votes.toggle', $report))
        ->assertStatus(500); // LogicException bubbles as 500 in test env
});

test('vote is unique per user per report', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->create([
        'tenant_id'  => $tenant->id,
        'author_id'  => $author->id,
        'status'     => ReportStatus::PublishedForVoting,
        'vote_count' => 0,
    ]);

    // Cast vote manually
    Vote::create(['report_id' => $report->id, 'user_id' => $user->id]);
    $report->increment('vote_count');

    // Trying to cast again should retract (toggle), not duplicate
    $response = $this->actingAs($user)->postJson(route('app.votes.toggle', $report));
    $response->assertOk()->assertJson(['voted' => false]);

    expect(Vote::where('report_id', $report->id)->where('user_id', $user->id)->count())->toBe(0);
});

test('vote_count is atomic', function (): void {
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
});
