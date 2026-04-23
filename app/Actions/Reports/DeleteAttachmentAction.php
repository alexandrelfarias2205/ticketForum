<?php declare(strict_types=1);

namespace App\Actions\Reports;

use App\Enums\AttachmentType;
use App\Models\ReportAttachment;
use Illuminate\Support\Facades\Storage;

class DeleteAttachmentAction
{
    public function handle(ReportAttachment $attachment): void
    {
        if ($attachment->type === AttachmentType::Image) {
            Storage::disk('private')->delete($attachment->url);
        }

        $attachment->delete();
    }
}
