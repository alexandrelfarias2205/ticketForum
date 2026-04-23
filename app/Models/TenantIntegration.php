<?php declare(strict_types=1);

namespace App\Models;

use App\Enums\ExternalPlatform;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantIntegration extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'platform',
        'config',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'platform'  => ExternalPlatform::class,
            'is_active' => 'boolean',
            // config is NOT cast to array — stored encrypted, decrypted manually in Jobs
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
}
