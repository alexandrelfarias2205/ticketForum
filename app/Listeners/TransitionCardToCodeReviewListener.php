<?php declare(strict_types=1);

namespace App\Listeners;

use App\Events\PipelineSucceeded;
use App\Services\Integrations\CardTransitionService;
use Illuminate\Contracts\Queue\ShouldQueue;

final class TransitionCardToCodeReviewListener implements ShouldQueue
{
    public string $queue = 'integrations';

    public function __construct(private readonly CardTransitionService $service) {}

    public function handle(PipelineSucceeded $event): void
    {
        $this->service->transitionToCodeReview($event->report);
    }
}
