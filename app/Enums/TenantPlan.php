<?php declare(strict_types=1);

namespace App\Enums;

enum TenantPlan: string
{
    case Free       = 'free';
    case Pro        = 'pro';
    case Enterprise = 'enterprise';

    public function label(): string
    {
        return match($this) {
            self::Free       => 'Gratuito',
            self::Pro        => 'Pro',
            self::Enterprise => 'Enterprise',
        };
    }
}
