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

    public function badgeClasses(): string
    {
        return match($this) {
            self::Bug            => 'bg-red-100 text-red-700',
            self::Improvement    => 'bg-blue-100 text-blue-700',
            self::FeatureRequest => 'bg-indigo-100 text-indigo-700',
        };
    }
}
