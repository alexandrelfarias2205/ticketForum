<?php declare(strict_types=1);

namespace App\Models;

use App\Enums\ExternalPlatform;
use App\Enums\ReportStatus;
use App\Enums\ReportType;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'author_id',
        'reviewer_id',
        'type',
        'title',
        'description',
        'status',
        'enriched_title',
        'enriched_description',
        'external_issue_url',
        'external_issue_id',
        'external_platform',
        'vote_count',
        'reviewed_at',
        'published_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'type'              => ReportType::class,
            'status'            => ReportStatus::class,
            'external_platform' => ExternalPlatform::class,
            'vote_count'        => 'integer',
            'reviewed_at'       => 'datetime',
            'published_at'      => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
    }

    // Relationships

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ReportAttachment::class);
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class, 'report_labels');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function integrationJobs(): HasMany
    {
        return $this->hasMany(IntegrationJob::class);
    }

    // Scopes

    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('status', ReportStatus::PendingReview);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', ReportStatus::Approved);
    }

    public function scopePublishedForVoting(Builder $query): Builder
    {
        return $query->where('status', ReportStatus::PublishedForVoting);
    }

    public function scopeForVoting(Builder $query): Builder
    {
        return $query->where('status', ReportStatus::PublishedForVoting)
            ->orderByDesc('vote_count');
    }
}
