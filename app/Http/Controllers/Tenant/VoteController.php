<?php declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Actions\Votes\CastVoteAction;
use App\Actions\Votes\RetractVoteAction;
use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Vote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function toggle(Request $request, Report $report): JsonResponse
    {
        // Verify the report is published for voting — Actions enforce vote uniqueness
        abort_unless(
            $report->status === \App\Enums\ReportStatus::PublishedForVoting,
            422,
            'Este relatório não está disponível para votação.'
        );

        $existingVote = Vote::where('report_id', $report->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existingVote) {
            app(RetractVoteAction::class)->handle(auth()->user(), $report);

            return response()->json([
                'voted'      => false,
                'vote_count' => $report->fresh()->vote_count,
            ]);
        }

        app(CastVoteAction::class)->handle(auth()->user(), $report);

        return response()->json([
            'voted'      => true,
            'vote_count' => $report->fresh()->vote_count,
        ]);
    }
}
