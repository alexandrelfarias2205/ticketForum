<?php declare(strict_types=1);

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vote;
use App\Policies\VotePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('create allows vote on published report when not already voted', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();
    $author = User::factory()->tenantUser($tenant)->create();
    $report = Report::factory()->publishedForVoting()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    expect((new VotePolicy())->create($user, $report))->toBeTrue();
});

test('create denies vote on non-published report', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();
    $report = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $user->id,
    ]);

    expect((new VotePolicy())->create($user, $report))->toBeFalse();
});

test('create denies duplicate vote on same report', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();
    $author = User::factory()->tenantUser($tenant)->create();
    $report = Report::factory()->publishedForVoting()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    Vote::create([
        'id'        => \Illuminate\Support\Str::uuid(),
        'report_id' => $report->id,
        'user_id'   => $user->id,
    ]);

    expect((new VotePolicy())->create($user, $report))->toBeFalse();
});

test('delete allows vote owner to retract', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();
    $author = User::factory()->tenantUser($tenant)->create();
    $report = Report::factory()->publishedForVoting()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    $vote = Vote::create([
        'id'        => \Illuminate\Support\Str::uuid(),
        'report_id' => $report->id,
        'user_id'   => $user->id,
    ]);

    expect((new VotePolicy())->delete($user, $vote))->toBeTrue();
});

test('delete denies non-owner from retracting vote', function (): void {
    $tenant  = Tenant::factory()->create();
    $owner   = User::factory()->tenantUser($tenant)->create();
    $intruder = User::factory()->tenantUser($tenant)->create();
    $author  = User::factory()->tenantUser($tenant)->create();
    $report  = Report::factory()->publishedForVoting()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    $vote = Vote::create([
        'id'        => \Illuminate\Support\Str::uuid(),
        'report_id' => $report->id,
        'user_id'   => $owner->id,
    ]);

    expect((new VotePolicy())->delete($intruder, $vote))->toBeFalse();
});
