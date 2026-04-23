<?php declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Actions\Reports\CreateReportAction;
use App\Actions\Reports\UpdateReportAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\StoreReportRequest;
use App\Http\Requests\Reports\UpdateReportRequest;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Report::class);

        return view('app.reports.index');
    }

    public function create(): View
    {
        $this->authorize('create', Report::class);

        return view('app.reports.create');
    }

    public function store(StoreReportRequest $request, CreateReportAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user   = $request->user();
        $report = $action->handle($user, $request->validated());

        return redirect()->route('app.reports.show', $report);
    }

    public function show(Report $report): View
    {
        $this->authorize('view', $report);

        return view('app.reports.show', compact('report'));
    }

    public function edit(Report $report): View
    {
        $this->authorize('update', $report);

        return view('app.reports.edit', compact('report'));
    }

    public function update(UpdateReportRequest $request, Report $report, UpdateReportAction $action): RedirectResponse
    {
        $this->authorize('update', $report);

        $action->handle($report, $request->validated());

        return redirect()->route('app.reports.show', $report);
    }
}
