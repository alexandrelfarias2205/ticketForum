<?php declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Actions\Reports\DeleteAttachmentAction;
use App\Actions\Reports\StoreAttachmentAction;
use App\Actions\Reports\StoreLinkAttachmentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\StoreAttachmentRequest;
use App\Http\Requests\Reports\StoreLinkRequest;
use App\Models\Report;
use App\Models\ReportAttachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportAttachmentController extends Controller
{
    public function store(
        StoreAttachmentRequest $request,
        Report $report,
        StoreAttachmentAction $action,
    ): JsonResponse {
        $this->authorize('update', $report);

        $attachment = $action->handle($report, $request->file('file'));

        return response()->json([
            'id'       => $attachment->id,
            'type'     => $attachment->type->value,
            'url'      => $attachment->url,
            'filename' => $attachment->filename,
            'size'     => $attachment->size_bytes,
        ], 201);
    }

    public function storeLink(
        StoreLinkRequest $request,
        Report $report,
        StoreLinkAttachmentAction $action,
    ): JsonResponse {
        $this->authorize('update', $report);

        $attachment = $action->handle($report, $request->validated('url'));

        return response()->json([
            'id'   => $attachment->id,
            'type' => $attachment->type->value,
            'url'  => $attachment->url,
        ], 201);
    }

    public function destroy(
        Report $report,
        ReportAttachment $attachment,
        DeleteAttachmentAction $action,
    ): RedirectResponse {
        $this->authorize('update', $report);

        $action->handle($attachment);

        return redirect()->back();
    }

    public function download(ReportAttachment $attachment): RedirectResponse|StreamedResponse
    {
        $this->authorize('view', $attachment->report);

        $disk = Storage::disk('private');

        if (method_exists($disk, 'temporaryUrl')) {
            try {
                $temporaryUrl = $disk->temporaryUrl($attachment->url, now()->addMinutes(30));

                return redirect($temporaryUrl);
            } catch (\RuntimeException) {
                // Fall through to streamed download for local disk
            }
        }

        return $disk->download($attachment->url, $attachment->filename ?? basename($attachment->url));
    }
}
