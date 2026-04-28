<?php declare(strict_types=1);

namespace App\Enums;

enum ExternalPlatform: string
{
    case Jira   = 'jira';
    case GitHub = 'github';
    case GitLab = 'gitlab';

    public function label(): string
    {
        return match($this) {
            self::Jira   => 'Jira',
            self::GitHub => 'GitHub Issues',
            self::GitLab => 'GitLab Issues',
        };
    }

    public function badgeClasses(): string
    {
        return match($this) {
            self::Jira   => 'bg-blue-600 text-white',
            self::GitHub => 'bg-gray-900 text-white',
            self::GitLab => 'bg-orange-600 text-white',
        };
    }
}
