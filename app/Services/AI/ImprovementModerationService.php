<?php declare(strict_types=1);

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Moderates user-submitted improvements: detects profanity/abuse and corrects typos.
 * Returns a sanitized title/description plus an approval verdict.
 */
final class ImprovementModerationService
{
    /**
     * @return array{approved: bool, reason: ?string, cleaned_title: string, cleaned_description: string}
     */
    public function moderate(string $title, string $description): array
    {
        $apiKey = (string) config('services.anthropic.api_key');
        if ($apiKey === '') {
            // Fail-open in environments without API key.
            return [
                'approved'            => true,
                'reason'              => null,
                'cleaned_title'       => $title,
                'cleaned_description' => $description,
            ];
        }

        $payload = [
            'model'      => (string) config('services.anthropic.moderation_model'),
            'max_tokens' => 1024,
            'system'     => [[
                'type' => 'text',
                'text' => "You are a content moderator for a SaaS feedback board. For each submission you must:\n1) Reject if it contains profanity, hate speech, harassment, sexual content, or spam.\n2) If accepted, lightly correct typos and grammar in Brazilian Portuguese while preserving the user's intent.\n\nReply ONLY with a single JSON object (no extra text, no fences) in this exact shape:\n{\"approved\": true|false, \"reason\": \"<short PT-BR reason or null>\", \"cleaned_title\": \"<string>\", \"cleaned_description\": \"<string>\"}",
                'cache_control' => ['type' => 'ephemeral'],
            ]],
            'messages' => [[
                'role'    => 'user',
                'content' => "Title: {$title}\nDescription: {$description}\n\nReturn the JSON decision now.",
            ]],
        ];

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
            Log::channel('integrations')->error('ImprovementModerationService API call failed', [
                'error' => $e->getMessage(),
            ]);

            // Fail-open with original content; reviewer queue can still catch issues.
            return [
                'approved'            => true,
                'reason'              => null,
                'cleaned_title'       => $title,
                'cleaned_description' => $description,
            ];
        }

        $text = $this->extractText($response->json() ?? []);

        return $this->parseDecision($text, $title, $description);
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
     * @return array{approved: bool, reason: ?string, cleaned_title: string, cleaned_description: string}
     */
    private function parseDecision(string $text, string $fallbackTitle, string $fallbackDescription): array
    {
        $trimmed = trim($text);
        $trimmed = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $trimmed) ?? $trimmed;

        try {
            $decoded = json_decode($trimmed, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return [
                'approved'            => true,
                'reason'              => null,
                'cleaned_title'       => $fallbackTitle,
                'cleaned_description' => $fallbackDescription,
            ];
        }

        if (! is_array($decoded)) {
            return [
                'approved'            => true,
                'reason'              => null,
                'cleaned_title'       => $fallbackTitle,
                'cleaned_description' => $fallbackDescription,
            ];
        }

        $reason = $decoded['reason'] ?? null;

        return [
            'approved'            => (bool) ($decoded['approved'] ?? true),
            'reason'              => is_string($reason) && $reason !== '' ? $reason : null,
            'cleaned_title'       => trim((string) ($decoded['cleaned_title'] ?? $fallbackTitle)),
            'cleaned_description' => trim((string) ($decoded['cleaned_description'] ?? $fallbackDescription)),
        ];
    }
}
