<?php declare(strict_types=1);

namespace App\Models;

use App\Enums\AttachmentType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportAttachment extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'report_id',
        'type',
        'url',
        'filename',
        'size_bytes',
    ];

    protected function casts(): array
    {
        return [
            'type'       => AttachmentType::class,
            'size_bytes' => 'integer',
        ];
    }

    // Relationships

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    // Helpers

    public function isImage(): bool
    {
        return $this->type === AttachmentType::Image;
    }

    public function isLink(): bool
    {
        return $this->type === AttachmentType::Link;
    }
}
