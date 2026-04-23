<?php declare(strict_types=1);

namespace App\Actions\Votes;

use App\Models\Report;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;

class RetractVoteAction
{
    public function handle(User $user, Report $report): void
    {
        $vote = Vote::where('report_id', $report->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        DB::transaction(function () use ($vote, $report): void {
            $vote->delete();
            $report->decrement('vote_count');
        });
    }
}
