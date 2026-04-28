<?php declare(strict_types=1);

use App\Models\Report;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AI\BugRiskAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    Config::set('services.anthropic.api_key', 'test-key');
    Config::set('services.anthropic.api_url', 'https://api.anthropic.com/v1/messages');
    Config::set('services.anthropic.version', '2023-06-01');
});

function makeReport(int $index = 0): Report
{
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    return Report::factory()->bug()->approved()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);
}

test('high risk bugs return safe_to_proceed false', function (): void {
    Http::fake([
        'https://api.anthropic.com/*' => Http::response([
            'content' => [
                ['type' => 'text', 'text' => json_encode([
                    'risk_level'        => 'high',
                    'interdependencies' => ['auth-service'],
                    'safe_to_proceed'   => false,
                    'reasoning'         => 'Too many interdependencies.',
                ])],
            ],
        ], 200),
    ]);

    $report = makeReport();
    $service = new BugRiskAnalysisService();
    $result  = $service->analyze($report, new Collection());

    expect($result['risk_level'])->toBe('high')
        ->and($result['safe_to_proceed'])->toBeFalse()
        ->and($result['interdependencies'])->toContain('auth-service');
});

test('low risk bugs return safe_to_proceed true', function (): void {
    Http::fake([
        'https://api.anthropic.com/*' => Http::response([
            'content' => [
                ['type' => 'text', 'text' => json_encode([
                    'risk_level'        => 'low',
                    'interdependencies' => [],
                    'safe_to_proceed'   => true,
                    'reasoning'         => 'Isolated change.',
                ])],
            ],
        ], 200),
    ]);

    $report = makeReport();
    $service = new BugRiskAnalysisService();
    $result  = $service->analyze($report, new Collection());

    expect($result['risk_level'])->toBe('low')
        ->and($result['safe_to_proceed'])->toBeTrue();
});

test('api failure returns safe default', function (): void {
    Http::fake([
        'https://api.anthropic.com/*' => fn (): never => throw new \Illuminate\Http\Client\ConnectionException('Timeout'),
    ]);

    $report = makeReport();
    $service = new BugRiskAnalysisService();
    $result  = $service->analyze($report, new Collection());

    expect($result['safe_to_proceed'])->toBeTrue()
        ->and($result['risk_level'])->toBe('low');
});
