<?php declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ExternalPlatform;
use App\Models\ProductIntegration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Test the credentials of a single ProductIntegration in the background.
 *
 * Result is broadcast via a Livewire 'notify' event surfaced through a private
 * channel (channel: integration-test.{integrationId}). For the first iteration
 * we just log success/failure; the Livewire UI polls or refreshes after dispatch.
 */
final class TestIntegrationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 30;

    public function __construct(
        private readonly string $integrationId,
    ) {}

    public function handle(): void
    {
        $integration = ProductIntegration::find($this->integrationId);
        if ($integration === null) {
            return;
        }

        $config = $integration->decryptedConfig();

        try {
            $success = match ($integration->platform) {
                ExternalPlatform::Jira   => $this->testJira($config),
                ExternalPlatform::GitHub => $this->testGitHub($config),
                ExternalPlatform::GitLab => $this->testGitLab($config),
            };

            Log::channel('integrations')->info('TestIntegrationJob result', [
                'integration_id' => $this->integrationId,
                'platform'       => $integration->platform->value,
                'success'        => $success,
            ]);
        } catch (Throwable $e) {
            Log::channel('integrations')->error('TestIntegrationJob failed', [
                'integration_id' => $this->integrationId,
                'platform'       => $integration->platform->value,
                'error'          => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function testJira(array $config): bool
    {
        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $email   = (string) ($config['email'] ?? '');
        $token   = (string) ($config['api_token'] ?? '');

        $response = Http::timeout(10)
            ->withBasicAuth($email, $token)
            ->get("{$baseUrl}/rest/api/3/myself");

        return $response->successful();
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function testGitHub(array $config): bool
    {
        $token = (string) ($config['token'] ?? '');

        $response = Http::timeout(10)
            ->withToken($token)
            ->withHeaders(['Accept' => 'application/vnd.github+json'])
            ->get('https://api.github.com/user');

        return $response->successful();
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function testGitLab(array $config): bool
    {
        $baseUrl = rtrim((string) ($config['base_url'] ?? 'https://gitlab.com'), '/');
        $token   = (string) ($config['token'] ?? '');

        $response = Http::timeout(10)
            ->withHeaders(['PRIVATE-TOKEN' => $token])
            ->get("{$baseUrl}/api/v4/user");

        return $response->successful();
    }
}
