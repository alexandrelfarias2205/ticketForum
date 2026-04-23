<?php declare(strict_types=1);

namespace App\Enums;

enum ExternalPlatform: string
{
    case Jira   = 'jira';
    case GitHub = 'github';

    public function label(): string
    {
        return match($this) {
            self::Jira   => 'Jira',
            self::GitHub => 'GitHub Issues',
        };
    }
}
