<?php declare(strict_types=1);

namespace App\Services\AI;

use App\Models\Report;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class BugRiskAnalysisService
{
    /**
     * Analyse the risk of proceeding with a fix for the given report,
     * taking into account other reports currently in progress.
     *
     * @param  Collection<int, Report>  $pendingReports
     * @return array{risk_level: string, interdependencies: array<int, string>, safe_to_proceed: bool, reasoning: string}
     */
    public function analyze(Report $report, Collection $pendingReports): array
    {
        $apiKey = (string) config('services.anthropic.api_key');

        if ($apiKey === '') {
            return $this->defaultSafe();
        }

        $payload = $this->buildPayload($report, $pendingReports);

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => (string) config('services.anthropic.version', '2023-06-01'),
                'content-type'      => 'application/json',
            ])
                ->timeout(30)
                ->connectTimeout(10)
                ->post((string) config('services.anthropic.api_url'), $payload);

            $response->throw();
        } catch (Throwable $e) {
            Log::channel('integrations')->error('BugRiskAnalysisService API call failed', [
                'report_id' => $report->id,
                'error'     => $e->getMessage(),
            ]);

            return $this->defaultSafe();
        }

        $text = $this->extractText($response->json() ?? []);

        return $this->parseResult($text);
    }

    /**
     * @param  Collection<int, Report>  $pendingReports
     * @return array<string, mixed>
     */
    private function buildPayload(Report $report, Collection $pendingReports): array
    {
        $pendingList = $pendingReports->map(
            fn (Report $r): string => "- [{$r->id}] {$r->title}"
        )->implode("\n");

        $systemContext = $pendingList !== ''
            ? "REPORTS CURRENTLY IN PROGRESS:\n{$pendingList}"
            : 'No other reports are currently in progress.';

        return [
            'model'      => 'claude-sonnet-4-6',
            'max_tokens' => 512,
            'system'     => [
                [
                    'type' => 'text',
                    'text' => "You are a risk-analysis engine for an autonomous bug-fixing agent. "
                        . "Given a bug report and a list of reports currently being worked on, "
                        . "determine the risk level of proceeding with a fix right now.\n\n"
                        . "Reply ONLY with a single JSON object (no extra text, no code fences):\n"
                        . '{"risk_level":"low|medium|high","interdependencies":[],"safe_to_proceed":true|false,"reasoning":"..."}' . "\n\n"
                        . 'Mark safe_to_proceed=false ONLY when risk_level is "high".',
                    'cache_control' => ['type' => 'ephemeral'],
                ],
                [
                    'type'          => 'text',
                    'text'          => $systemContext,
                    'cache_control' => ['type' => 'ephemeral'],
                ],
            ],
            'messages' => [[
                'role'    => 'user',
                'content' => "BUG REPORT TO ANALYSE:\nTitle: {$report->title}\nDescription: {$report->description}\n\nReturn the JSON risk assessment now.",
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
     * @return array{risk_level: string, interdependencies: array<int, string>, safe_to_proceed: bool, reasoning: string}
     */
    private function parseResult(string $text): array
    {
        $trimmed = trim($text);
        if ($trimmed === '') {
            return $this->defaultSafe();
        }

        $trimmed = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $trimmed) ?? $trimmed;

        try {
            $decoded = json_decode($trimmed, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return $this->defaultSafe();
        }

        if (! is_array($decoded)) {
            return $this->defaultSafe();
        }

        $riskLevel          = in_array($decoded['risk_level'] ?? '', ['low', 'medium', 'high'], true)
            ? (string) $decoded['risk_level']
            : 'low';
        $interdependencies  = is_array($decoded['interdependencies'] ?? null)
            ? array_values(array_map('strval', $decoded['interdependencies']))
            : [];

        return [
            'risk_level'        => $riskLevel,
            'interdependencies' => $interdependencies,
            'safe_to_proceed'   => (bool) ($decoded['safe_to_proceed'] ?? true),
            'reasoning'         => (string) ($decoded['reasoning'] ?? ''),
        ];
    }

    /**
     * @return array{risk_level: string, interdependencies: array<int, string>, safe_to_proceed: bool, reasoning: string}
     */
    private function defaultSafe(): array
    {
        return [
            'risk_level'        => 'low',
            'interdependencies' => [],
            'safe_to_proceed'   => true,
            'reasoning'         => 'Default safe: API unavailable or no context.',
        ];
    }
}
