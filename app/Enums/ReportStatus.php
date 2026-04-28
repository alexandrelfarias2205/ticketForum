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
            self::PendingReview      => 'badge badge-warning',
            self::Approved           => 'badge badge-success',
            self::Rejected           => 'badge badge-danger',
            self::PublishedForVoting => 'badge badge-brand',
            self::InProgress         => 'badge badge-info',
            self::Done               => 'badge badge-neutral',
        };
    }

    /**
     * Semantic tone for the <x-badge> component.
     */
    public function badgeTone(): string
    {
        return match($this) {
            self::PendingReview      => 'warning',
            self::Approved           => 'success',
            self::Rejected           => 'danger',
            self::PublishedForVoting => 'brand',
            self::InProgress         => 'info',
            self::Done               => 'neutral',
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
