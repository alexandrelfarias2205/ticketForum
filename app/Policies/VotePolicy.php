<?php declare(strict_types=1);

namespace App\Policies;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\User;
use App\Models\Vote;

class VotePolicy
{
    public function create(User $user, Report $report): bool
    {
        if ($report->status !== ReportStatus::PublishedForVoting) {
            return false;
        }

        return ! Vote::where('report_id', $report->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function delete(User $user, Vote $vote): bool
    {
        return $user->id === $vote->user_id;
    }
}
