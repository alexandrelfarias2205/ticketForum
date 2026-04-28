<?php declare(strict_types=1);

namespace App\Contracts;

interface IssueProviderInterface
{
    /**
     * Create an issue on the external platform.
     *
     * @param  array<string, mixed>  $data
     * @return array{id: string, url: string, raw: array<string, mixed>}
     */
    public function createIssue(array $data): array;

    /**
     * Fetch an issue by its external id.
     *
     * @return array<string, mixed>
     */
    public function getIssue(string $id): array;

    /**
     * Update fields on an existing issue.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateIssue(string $id, array $data): void;

    /**
     * Add a comment to an existing issue.
     */
    public function addComment(string $id, string $body): void;
}
