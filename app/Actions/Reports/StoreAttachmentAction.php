<?php declare(strict_types=1);

namespace App\Actions\Reports;

use App\Enums\AttachmentType;
use App\Models\Report;
use App\Models\ReportAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StoreAttachmentAction
{
    public function handle(Report $report, UploadedFile $file): ReportAttachment
    {
        $validator = Validator::make(
            ['file' => $file],
            ['file' => 'required|file|mimes:jpeg,png,gif,webp|max:10240'],
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path     = "reports/{$report->id}/attachments/{$filename}";

        Storage::disk('private')->putFileAs(
            "reports/{$report->id}/attachments",
            $file,
            $filename,
        );

        return ReportAttachment::create([
            'report_id'  => $report->id,
            'type'       => AttachmentType::Image,
            'url'        => $path,
            'filename'   => $file->getClientOriginalName(),
            'size_bytes' => $file->getSize(),
        ]);
    }
}
