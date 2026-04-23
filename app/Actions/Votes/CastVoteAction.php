<?php declare(strict_types=1);

namespace App\Actions\Votes;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;

class CastVoteAction
{
    public function handle(User $user, Report $report): Vote
    {
        if ($report->status !== ReportStatus::PublishedForVoting) {
            throw new \LogicException('Apenas relatórios publicados para votação podem receber votos.');
        }

        if (Vote::where('report_id', $report->id)->where('user_id', $user->id)->exists()) {
            throw new \LogicException('Usuário já votou neste relatório.');
        }

        return DB::transaction(function () use ($user, $report): Vote {
            $vote = Vote::create([
                'report_id' => $report->id,
                'user_id'   => $user->id,
            ]);

            $report->increment('vote_count');

            return $vote;
        });
    }
}
