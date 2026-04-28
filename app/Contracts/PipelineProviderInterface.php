<?php declare(strict_types=1);

namespace App\Contracts;

interface PipelineProviderInterface
{
    /**
     * Get the latest pipeline status for a branch. Returns one of:
     * "queued" | "running" | "success" | "failed" | "unknown".
     */
    public function getPipelineStatus(string $branch): string;

    /**
     * Open a merge/pull request and return the merge request url and id.
     *
     * @param  array{title: string, body: string, source_branch: string, target_branch: string}  $data
     * @return array{id: string, url: string, raw: array<string, mixed>}
     */
    public function openMergeRequest(array $data): array;
}
