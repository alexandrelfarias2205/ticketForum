<?php declare(strict_types=1);

namespace App\Actions\Products;

use App\Enums\ExternalPlatform;
use App\Models\Product;
use App\Models\ProductIntegration;
use InvalidArgumentException;

final class SaveProductIntegrationAction
{
    /**
     * Persist (insert or update) the integration for a Product. Config is encrypted at rest.
     *
     * @param  array<string, mixed>  $config
     */
    public function handle(Product $product, string $platform, array $config): ProductIntegration
    {
        $platformEnum = ExternalPlatform::tryFrom($platform);
        if ($platformEnum === null) {
            throw new InvalidArgumentException("Invalid platform: {$platform}");
        }

        /** @var ProductIntegration $integration */
        $integration = ProductIntegration::updateOrCreate(
            [
                'product_id' => $product->id,
                'platform'   => $platformEnum->value,
            ],
            [
                'config'    => encrypt($config),
                'is_active' => true,
            ]
        );

        return $integration;
    }
}
