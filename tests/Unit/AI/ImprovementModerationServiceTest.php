<?php declare(strict_types=1);

use App\Services\AI\ImprovementModerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    Config::set('services.anthropic.api_key', 'test-key');
    Config::set('services.anthropic.api_url', 'https://api.anthropic.com/v1/messages');
    Config::set('services.anthropic.version', '2023-06-01');
    Config::set('services.anthropic.moderation_model', 'claude-haiku-4-5');
});

test('approved content passes through with cleaned title and description', function (): void {
    Http::fake([
        'https://api.anthropic.com/*' => Http::response([
            'content' => [
                ['type' => 'text', 'text' => json_encode([
                    'approved'            => true,
                    'reason'              => null,
                    'cleaned_title'       => 'Add dark mode support',
                    'cleaned_description' => 'Users want a dark theme option.',
                ])],
            ],
        ], 200),
    ]);

    $service = new ImprovementModerationService();
    $result  = $service->moderate('Add dark mode suport', 'Users want a dark theme option');

    expect($result['approved'])->toBeTrue()
        ->and($result['cleaned_title'])->toBe('Add dark mode support')
        ->and($result['reason'])->toBeNull();
});

test('rejected content gets rejection reason', function (): void {
    Http::fake([
        'https://api.anthropic.com/*' => Http::response([
            'content' => [
                ['type' => 'text', 'text' => json_encode([
                    'approved'            => false,
                    'reason'              => 'Conteúdo inapropriado',
                    'cleaned_title'       => '',
                    'cleaned_description' => '',
                ])],
            ],
        ], 200),
    ]);

    $service = new ImprovementModerationService();
    $result  = $service->moderate('Título ofensivo', 'Conteúdo com palavrão');

    expect($result['approved'])->toBeFalse()
        ->and($result['reason'])->toBe('Conteúdo inapropriado');
});

test('api failure returns fail-open with original content', function (): void {
    Http::fake([
        'https://api.anthropic.com/*' => fn (): never => throw new \Illuminate\Http\Client\ConnectionException('Timeout'),
    ]);

    $service = new ImprovementModerationService();
    $result  = $service->moderate('My title', 'My description');

    expect($result['approved'])->toBeTrue()
        ->and($result['cleaned_title'])->toBe('My title')
        ->and($result['cleaned_description'])->toBe('My description');
});
