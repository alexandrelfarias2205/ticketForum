<?php declare(strict_types=1);

namespace App\Http\Controllers\Root;

use App\Actions\Integrations\DispatchIssueCreationAction;
use App\Actions\Reports\ApproveReportAction;
use App\Actions\Reports\PublishReportAction;
use App\Actions\Reports\RejectReportAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ApproveReportRequest;
use App\Http\Requests\Reports\RejectReportRequest;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReportReviewController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Report::class);

        return view('root.reports.index');
    }

    public function show(Report $report): View
    {
        $this->authorize('view', $report);

        return view('root.reports.show', compact('report'));
    }

    public function approve(
        ApproveReportRequest $request,
        Report $report,
        ApproveReportAction $action,
    ): RedirectResponse {
        $this->authorize('approve', $report);

        /** @var \App\Models\User $reviewer */
        $reviewer = $request->user();

        $action->handle($report, $reviewer, $request->validated());

        return redirect()->route('root.reports.show', $report)
            ->with('success', 'Report approved successfully.');
    }

    public function reject(
        RejectReportRequest $request,
        Report $report,
        RejectReportAction $action,
    ): RedirectResponse {
        $this->authorize('approve', $report); // root-only gate — same access level as approve

        /** @var \App\Models\User $reviewer */
        $reviewer = $request->user();

        $action->handle($report, $reviewer, $request->validated('reason'));

        return redirect()->route('root.reports.show', $report)
            ->with('success', 'Report rejected.');
    }

    public function publish(Report $report, PublishReportAction $action): RedirectResponse
    {
        $this->authorize('publish', $report);

        $action->handle($report);

        return redirect()->route('root.reports.show', $report)
            ->with('success', 'Report published for voting.');
    }

    public function createIssue(Report $report, DispatchIssueCreationAction $action): RedirectResponse
    {
        $this->authorize('createIssue', $report);

        if ($report->external_issue_id !== null) {
            return redirect()->back()->with('info', 'Issue já criada: ' . $report->external_issue_url);
        }

        $action->handle($report);

        return redirect()->back()->with('success', 'Issue sendo criada. Aguarde alguns instantes.');
    }
}
