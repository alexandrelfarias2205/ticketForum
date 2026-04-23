<?php declare(strict_types=1);

namespace App\Enums;

enum ReportType: string
{
    case Bug            = 'bug';
    case Improvement    = 'improvement';
    case FeatureRequest = 'feature_request';

    public function label(): string
    {
        return match($this) {
            self::Bug            => 'Bug',
            self::Improvement    => 'Melhoria',
            self::FeatureRequest => 'Nova Funcionalidade',
        };
    }
}
