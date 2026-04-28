<?php declare(strict_types=1);

namespace App\Models;

use App\Enums\ExternalPlatform;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductIntegration extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'product_id',
        'platform',
        'config',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'platform'  => ExternalPlatform::class,
            'is_active' => 'boolean',
            // config is NOT cast — stored encrypted via encrypt(), decrypted manually inside Jobs/Actions
        ];
    }

    // Relationships

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Decrypt the encrypted config column. Use ONLY inside Jobs/Actions, never log.
     *
     * @return array<string, mixed>
     */
    public function decryptedConfig(): array
    {
        $decrypted = decrypt($this->config);

        return is_array($decrypted) ? $decrypted : [];
    }
}
