<?php declare(strict_types=1);

namespace App\Services\Git;

use App\Enums\ExternalPlatform;
use App\Models\ProductIntegration;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class GitPushService
{
    /**
     * Create a new branch from a base branch and return the new branch's HEAD SHA.
     */
    public function createBranch(
        ProductIntegration $integration,
        string $branchName,
        string $baseBranch = 'main',
    ): string {
        $config = $integration->decryptedConfig();

        return match ($integration->platform) {
            ExternalPlatform::GitHub => $this->githubCreateBranch($config, $branchName, $baseBranch),
            ExternalPlatform::GitLab => $this->gitlabCreateBranch($config, $branchName, $baseBranch),
            default                  => throw new RuntimeException(
                "Platform {$integration->platform->value} does not support branch creation via GitPushService."
            ),
        };
    }

    /**
     * Commit one or more files to an existing branch and return the new commit SHA.
     *
     * @param  array<int|string, array{path: string, content: string}>  $files
     */
    public function commitFiles(
        ProductIntegration $integration,
        string $branchName,
        array $files,
        string $commitMessage,
    ): string {
        $config = $integration->decryptedConfig();

        return match ($integration->platform) {
            ExternalPlatform::GitHub => $this->githubCommitFiles($config, $branchName, $files, $commitMessage),
            ExternalPlatform::GitLab => $this->gitlabCommitFiles($config, $branchName, $files, $commitMessage),
            default                  => throw new RuntimeException(
                "Platform {$integration->platform->value} does not support file commits via GitPushService."
            ),
        };
    }

    // ── GitHub ──────────────────────────────────────────────────────────────

    /**
     * @param  array<string, mixed>  $config
     */
    private function githubCreateBranch(array $config, string $branchName, string $baseBranch): string
    {
        $owner = (string) ($config['owner'] ?? '');
        $repo  = (string) ($config['repo'] ?? '');
        $token = (string) ($config['token'] ?? '');
        $base  = "https://api.github.com/repos/{$owner}/{$repo}";

        // Resolve base branch SHA.
        $refResponse = Http::withToken($token)
            ->withHeaders(['Accept' => 'application/vnd.github+json'])
            ->timeout(30)
            ->connectTimeout(10)
            ->get("{$base}/git/refs/heads/{$baseBranch}");

        $refResponse->throw();
        $baseSha = (string) ($refResponse->json('object.sha') ?? '');

        if ($baseSha === '') {
            throw new RuntimeException("Could not resolve SHA for base branch '{$baseBranch}'.");
        }

        // Create the new branch.
        $createResponse = Http::withToken($token)
            ->withHeaders(['Accept' => 'application/vnd.github+json'])
            ->timeout(30)
            ->connectTimeout(10)
            ->post("{$base}/git/refs", [
                'ref' => "refs/heads/{$branchName}",
                'sha' => $baseSha,
            ]);

        $createResponse->throw();

        return (string) ($createResponse->json('object.sha') ?? $baseSha);
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<int|string, array{path: string, content: string}>  $files
     */
    private function githubCommitFiles(
        array $config,
        string $branchName,
        array $files,
        string $commitMessage,
    ): string {
        $owner = (string) ($config['owner'] ?? '');
        $repo  = (string) ($config['repo'] ?? '');
        $token = (string) ($config['token'] ?? '');
        $base  = "https://api.github.com/repos/{$owner}/{$repo}";

        $http = Http::withToken($token)
            ->withHeaders(['Accept' => 'application/vnd.github+json'])
            ->timeout(30)
            ->connectTimeout(10);

        // Get current HEAD SHA for the branch.
        $refResp = $http->get("{$base}/git/refs/heads/{$branchName}");
        $refResp->throw();
        $headSha = (string) ($refResp->json('object.sha') ?? '');

        // Get the tree SHA from the current commit.
        $commitResp = $http->get("{$base}/git/commits/{$headSha}");
        $commitResp->throw();
        $baseTreeSha = (string) ($commitResp->json('tree.sha') ?? '');

        // Create blobs for each file.
        $treeItems = [];
        foreach ($files as $file) {
            $blobResp = $http->post("{$base}/git/blobs", [
                'content'  => $file['content'],
                'encoding' => 'utf-8',
            ]);
            $blobResp->throw();
            $treeItems[] = [
                'path' => $file['path'],
                'mode' => '100644',
                'type' => 'blob',
                'sha'  => $blobResp->json('sha'),
            ];
        }

        // Create a new tree.
        $treeResp = $http->post("{$base}/git/trees", [
            'base_tree' => $baseTreeSha,
            'tree'      => $treeItems,
        ]);
        $treeResp->throw();
        $newTreeSha = (string) ($treeResp->json('sha') ?? '');

        // Create the commit.
        $newCommitResp = $http->post("{$base}/git/commits", [
            'message' => $commitMessage,
            'tree'    => $newTreeSha,
            'parents' => [$headSha],
        ]);
        $newCommitResp->throw();
        $newCommitSha = (string) ($newCommitResp->json('sha') ?? '');

        // Update the branch ref.
        $http->patch("{$base}/git/refs/heads/{$branchName}", [
            'sha' => $newCommitSha,
        ])->throw();

        return $newCommitSha;
    }

    // ── GitLab ──────────────────────────────────────────────────────────────

    /**
     * @param  array<string, mixed>  $config
     */
    private function gitlabCreateBranch(array $config, string $branchName, string $baseBranch): string
    {
        $baseUrl   = rtrim((string) ($config['base_url'] ?? 'https://gitlab.com'), '/');
        $projectId = (string) ($config['project_id'] ?? '');
        $token     = (string) ($config['token'] ?? '');

        $response = Http::withHeaders(['PRIVATE-TOKEN' => $token])
            ->timeout(30)
            ->connectTimeout(10)
            ->post("{$baseUrl}/api/v4/projects/{$projectId}/repository/branches", [
                'branch' => $branchName,
                'ref'    => $baseBranch,
            ]);

        $response->throw();

        return (string) ($response->json('commit.id') ?? '');
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<int|string, array{path: string, content: string}>  $files
     */
    private function gitlabCommitFiles(
        array $config,
        string $branchName,
        array $files,
        string $commitMessage,
    ): string {
        $baseUrl   = rtrim((string) ($config['base_url'] ?? 'https://gitlab.com'), '/');
        $projectId = (string) ($config['project_id'] ?? '');
        $token     = (string) ($config['token'] ?? '');

        $actions = array_map(fn (array $file): array => [
            'action'        => 'update',
            'file_path'     => $file['path'],
            'content'       => $file['content'],
        ], array_values($files));

        $response = Http::withHeaders(['PRIVATE-TOKEN' => $token])
            ->timeout(30)
            ->connectTimeout(10)
            ->post("{$baseUrl}/api/v4/projects/{$projectId}/repository/commits", [
                'branch'         => $branchName,
                'commit_message' => $commitMessage,
                'actions'        => $actions,
            ]);

        $response->throw();

        return (string) ($response->json('id') ?? '');
    }
}
