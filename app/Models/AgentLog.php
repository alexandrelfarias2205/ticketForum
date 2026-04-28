<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentLog extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    /** Only created_at is tracked — append-only log table. */
    public const UPDATED_AT = null;

    protected $fillable = [
        'report_id',
        'action',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload'    => 'array',
            'created_at' => 'datetime',
        ];
    }

    // Relationships

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
