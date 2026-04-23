<?php declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'email',
        'password',
        'name',
        'role',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'role'          => UserRole::class,
            'is_active'     => 'boolean',
            'last_login_at' => 'datetime',
            'password'      => 'hashed',
        ];
    }

    // Relationships

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'author_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Report::class, 'reviewer_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    // Helpers

    public function isRoot(): bool
    {
        return $this->role->isRoot();
    }

    public function isTenantAdmin(): bool
    {
        return $this->role->isTenantAdmin();
    }

    public function isTenantUser(): bool
    {
        return $this->role === UserRole::TenantUser;
    }
}
