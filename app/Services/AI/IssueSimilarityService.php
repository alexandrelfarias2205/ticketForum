<?php declare(strict_types=1);

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Detect duplicate issues using Claude. Uses prompt caching for the existing-issues
 * payload so repeated checks for the same product hit the cache.
 */
final class IssueSimilarityService
{
    /**
     * @param  array<int, array{id: string, title: string, description?: string}>  $existingIssues
     * @return array{is_duplicate: bool, matched_issue_id: ?string, confidence: float}
     */
    public function findSimilar(string $title, string $description, array $existingIssues): array
    {
        if ($existingIssues === []) {
            return ['is_duplicate' => false, 'matched_issue_id' => null, 'confidence' => 0.0];
        }

        $apiKey = (string) config('services.anthropic.api_key');
        if ($apiKey === '') {
            // Without API key (e.g. in CI) we err on the side of "not duplicate" so the pipeline keeps moving.
            return ['is_duplicate' => false, 'matched_issue_id' => null, 'confidence' => 0.0];
        }

        $payload = $this->buildPayload($title, $description, $existingIssues);

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => (string) config('services.anthropic.version', '2023-06-01'),
                'content-type'      => 'application/json',
            ])
                ->timeout(15)
                ->connectTimeout(10)
                ->post((string) config('services.anthropic.api_url'), $payload);

            $response->throw();
        } catch (Throwable $e) {
            Log::channel('integrations')->error('IssueSimilarityService API call failed', [
                'error' => $e->getMessage(),
            ]);

            return ['is_duplicate' => false, 'matched_issue_id' => null, 'confidence' => 0.0];
        }

        $text = $this->extractText($response->json() ?? []);

        return $this->parseDecision($text);
    }

    /**
     * @param  array<int, array{id: string, title: string, description?: string}>  $existingIssues
     * @return array<string, mixed>
     */
    private function buildPayload(string $title, string $description, array $existingIssues): array
    {
        $existingPayload = collect($existingIssues)->map(function (array $i): string {
            $desc = $i['description'] ?? '';
            return "[{$i['id']}] {$i['title']}\n{$desc}";
        })->implode("\n\n---\n\n");

        return [
            'model'      => (string) config('services.anthropic.similarity_model'),
            'max_tokens' => 256,
            'system'     => [
                [
                    'type'          => 'text',
                    'text'          => "You are a deduplication engine for a bug-tracking system. Given a new bug and a list of existing open issues, determine whether the new bug is a duplicate of any existing issue.\n\nReply ONLY with a single JSON object in this format (no extra text, no code fences):\n{\"is_duplicate\": true|false, \"matched_issue_id\": \"<id-or-null>\", \"confidence\": 0.0-1.0}\n\nMark is_duplicate=true ONLY if confidence >= 0.85.",
                    'cache_control' => ['type' => 'ephemeral'],
                ],
                [
                    'type'          => 'text',
                    'text'          => "EXISTING OPEN ISSUES (cached):\n\n" . $existingPayload,
                    'cache_control' => ['type' => 'ephemeral'],
                ],
            ],
            'messages' => [[
                'role'    => 'user',
                'content' => "NEW BUG REPORT:\nTitle: {$title}\nDescription: {$description}\n\nReturn the JSON decision now.",
            ]],
        ];
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function extractText(array $body): string
    {
        $blocks = $body['content'] ?? [];
        if (! is_array($blocks)) {
            return '';
        }

        foreach ($blocks as $block) {
            if (is_array($block) && ($block['type'] ?? '') === 'text') {
                return (string) ($block['text'] ?? '');
            }
        }

        return '';
    }

    /**
     * @return array{is_duplicate: bool, matched_issue_id: ?string, confidence: float}
     */
    private function parseDecision(string $text): array
    {
        $trimmed = trim($text);
        if ($trimmed === '') {
            return ['is_duplicate' => false, 'matched_issue_id' => null, 'confidence' => 0.0];
        }

        // Strip code fences if model added them despite instructions.
        $trimmed = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $trimmed) ?? $trimmed;

        try {
            $decoded = json_decode($trimmed, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return ['is_duplicate' => false, 'matched_issue_id' => null, 'confidence' => 0.0];
        }

        if (! is_array($decoded)) {
            return ['is_duplicate' => false, 'matched_issue_id' => null, 'confidence' => 0.0];
        }

        $confidence = (float) ($decoded['confidence'] ?? 0.0);
        $matchedId  = $decoded['matched_issue_id'] ?? null;

        return [
            'is_duplicate'     => (bool) ($decoded['is_duplicate'] ?? false) && $confidence >= 0.85,
            'matched_issue_id' => is_string($matchedId) && $matchedId !== '' ? $matchedId : null,
            'confidence'       => max(0.0, min(1.0, $confidence)),
        ];
    }
}
