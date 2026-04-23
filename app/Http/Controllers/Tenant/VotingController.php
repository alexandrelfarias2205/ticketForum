<?php declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VotingController extends Controller
{
    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', Report::class);

        return view('app.voting.index');
    }
}
