<?php declare(strict_types=1);

namespace App\Enums;

enum ReportStatus: string
{
    case PendingReview      = 'pending_review';
    case Approved           = 'approved';
    case Rejected           = 'rejected';
    case PublishedForVoting = 'published_for_voting';
    case InProgress         = 'in_progress';
    case Done               = 'done';

    public function label(): string
    {
        return match($this) {
            self::PendingReview      => 'Aguardando Revisão',
            self::Approved           => 'Aprovado',
            self::Rejected           => 'Rejeitado',
            self::PublishedForVoting => 'Em Votação',
            self::InProgress         => 'Em Desenvolvimento',
            self::Done               => 'Concluído',
        };
    }

    public function badgeClasses(): string
    {
        return match($this) {
            self::PendingReview      => 'bg-yellow-100 text-yellow-800',
            self::Approved           => 'bg-green-100 text-green-800',
            self::Rejected           => 'bg-red-100 text-red-800',
            self::PublishedForVoting => 'bg-indigo-100 text-indigo-800',
            self::InProgress         => 'bg-blue-100 text-blue-800',
            self::Done               => 'bg-gray-100 text-gray-800',
        };
    }

    public function canBeReviewed(): bool
    {
        return $this === self::PendingReview;
    }

    public function canBePublished(): bool
    {
        return $this === self::Approved;
    }

    public function canReceiveVotes(): bool
    {
        return $this === self::PublishedForVoting;
    }
}
