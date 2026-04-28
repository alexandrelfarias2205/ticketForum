<?php declare(strict_types=1);

use App\Services\AI\IssueSimilarityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

uses(Tests\TestCase::class, RefreshDatabase::class);

function claudeResponse(array $body): mixed
{
    return Http::response([
        'content' => [
            ['type' => 'text', 'text' => json_encode($body)],
        ],
    ], 200);
}

beforeEach(function (): void {
    Config::set('services.anthropic.api_key', 'test-key');
    Config::set('services.anthropic.api_url', 'https://api.anthropic.com/v1/messages');
    Config::set('services.anthropic.version', '2023-06-01');
    Config::set('services.anthropic.similarity_model', 'claude-haiku-4-5');
});

test('returns is_duplicate true with high confidence when api says so', function (): void {
    Http::fake([
        'https://api.anthropic.com/*' => claudeResponse([
            'is_duplicate'     => true,
            'matched_issue_id' => 'GH-42',
            'confidence'       => 0.95,
        ]),
    ]);

    $service = new IssueSimilarityService();
    $result  = $service->findSimilar('Login button broken', 'Users cannot login', [
        ['id' => 'GH-42', 'title' => 'Login broken', 'description' => 'Cannot login'],
    ]);

    expect($result['is_duplicate'])->toBeTrue()
        ->and($result['matched_issue_id'])->toBe('GH-42')
        ->and($result['confidence'])->toBe(0.95);
});

test('returns is_duplicate false with low confidence', function (): void {
    Http::fake([
        'https://api.anthropic.com/*' => claudeResponse([
            'is_duplicate'     => false,
            'matched_issue_id' => null,
            'confidence'       => 0.2,
        ]),
    ]);

    $service = new IssueSimilarityService();
    $result  = $service->findSimilar('Dark mode not working', 'Dark mode toggle has no effect', [
        ['id' => 'GH-42', 'title' => 'Login broken', 'description' => 'Cannot login'],
    ]);

    expect($result['is_duplicate'])->toBeFalse()
        ->and($result['matched_issue_id'])->toBeNull();
});

test('handles api timeout gracefully and returns safe default', function (): void {
    Http::fake([
        'https://api.anthropic.com/*' => fn (): never => throw new \Illuminate\Http\Client\ConnectionException('Connection timed out'),
    ]);

    $service = new IssueSimilarityService();
    $result  = $service->findSimilar('Title', 'Description', [
        ['id' => 'X-1', 'title' => 'Something'],
    ]);

    expect($result['is_duplicate'])->toBeFalse()
        ->and($result['matched_issue_id'])->toBeNull()
        ->and($result['confidence'])->toBe(0.0);
});

test('returns not duplicate immediately when existing issues list is empty', function (): void {
    Http::fake();

    $service = new IssueSimilarityService();
    $result  = $service->findSimilar('Title', 'Description', []);

    Http::assertNothingSent();
    expect($result['is_duplicate'])->toBeFalse();
});

test('high confidence but is_duplicate false is respected', function (): void {
    Http::fake([
        'https://api.anthropic.com/*' => claudeResponse([
            'is_duplicate'     => false,
            'matched_issue_id' => 'GH-99',
            'confidence'       => 0.90,
        ]),
    ]);

    $service = new IssueSimilarityService();
    $result  = $service->findSimilar('Something', 'Desc', [
        ['id' => 'GH-99', 'title' => 'Related issue'],
    ]);

    expect($result['is_duplicate'])->toBeFalse();
});
