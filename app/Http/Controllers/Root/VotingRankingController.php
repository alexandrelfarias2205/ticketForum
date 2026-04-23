<?php declare(strict_types=1);

namespace App\Http\Controllers\Root;

use Illuminate\Http\Request;
use Illuminate\View\View;

final class VotingRankingController
{
    public function __invoke(Request $request): View
    {
        return view('root.voting.index');
    }
}
