<?php declare(strict_types=1);

namespace App\Models;

use App\Enums\ExternalPlatform;
use App\Enums\IntegrationJobStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationJob extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'report_id',
        'platform',
        'status',
        'attempts',
        'error_message',
        'external_id',
        'response_payload',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'platform'         => ExternalPlatform::class,
            'status'           => IntegrationJobStatus::class,
            'attempts'         => 'integer',
            'response_payload' => 'array',
            'completed_at'     => 'datetime',
        ];
    }

    // Relationships

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    // Scopes

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', IntegrationJobStatus::Pending);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', IntegrationJobStatus::Failed);
    }
}
