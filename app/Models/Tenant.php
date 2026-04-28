<?php declare(strict_types=1);

namespace App\Models;

use App\Enums\TenantPlan;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'logo_url',
        'plan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'plan'      => TenantPlan::class,
            'is_active' => 'boolean',
        ];
    }

    // Relationships

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'tenant_product')
            ->withTimestamps();
    }
}
