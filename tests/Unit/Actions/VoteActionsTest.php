<?php declare(strict_types=1);

use App\Actions\Votes\CastVoteAction;
use App\Actions\Votes\RetractVoteAction;
use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('CastVoteAction creates vote and increments count', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->create([
        'tenant_id'  => $tenant->id,
        'author_id'  => $author->id,
        'status'     => ReportStatus::PublishedForVoting,
        'vote_count' => 0,
    ]);

    $vote = app(CastVoteAction::class)->handle($user, $report);

    expect($vote)->toBeInstanceOf(Vote::class)
        ->and((string) $vote->report_id)->toBe((string) $report->id)
        ->and((string) $vote->user_id)->toBe((string) $user->id)
        ->and($report->fresh()->vote_count)->toBe(1);
});

test('CastVoteAction throws LogicException on non-published report', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
        'status'    => ReportStatus::PendingReview,
    ]);

    expect(fn () => app(CastVoteAction::class)->handle($user, $report))
        ->toThrow(\LogicException::class);
});

test('RetractVoteAction deletes vote and decrements count', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->create([
        'tenant_id'  => $tenant->id,
        'author_id'  => $author->id,
        'status'     => ReportStatus::PublishedForVoting,
        'vote_count' => 1,
    ]);

    Vote::create(['report_id' => $report->id, 'user_id' => $user->id]);

    app(RetractVoteAction::class)->handle($user, $report);

    expect(Vote::where('report_id', $report->id)->where('user_id', $user->id)->exists())->toBeFalse()
        ->and($report->fresh()->vote_count)->toBe(0);
});

test('RetractVoteAction throws ModelNotFoundException if vote not found', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
        'status'    => ReportStatus::PublishedForVoting,
    ]);

    expect(fn () => app(RetractVoteAction::class)->handle($user, $report))
        ->toThrow(ModelNotFoundException::class);
});
