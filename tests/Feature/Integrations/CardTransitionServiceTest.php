<?php declare(strict_types=1);

use App\Enums\ExternalPlatform;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\TenantIntegration;
use App\Models\User;
use App\Services\Integrations\CardTransitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function makeReportWithPlatform(ExternalPlatform $platform, array $config): array
{
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->approved()->create([
        'tenant_id'         => $tenant->id,
        'author_id'         => $author->id,
        'external_platform' => $platform,
        'external_issue_id' => '42',
    ]);

    TenantIntegration::withoutGlobalScopes()->create([
        'tenant_id' => $tenant->id,
        'platform'  => $platform->value,
        'config'    => encrypt($config),
        'is_active' => true,
    ]);

    return [$report, $tenant];
}

test('transitions jira card to code review', function (): void {
    Http::fake([
        '*.atlassian.net/rest/api/3/issue/*/transitions' => Http::sequence()
            ->push([
                'transitions' => [
                    ['id' => '31', 'name' => 'Code Review'],
                ],
            ], 200)
            ->push([], 204),
    ]);

    [$report] = makeReportWithPlatform(ExternalPlatform::Jira, [
        'base_url'    => 'https://example.atlassian.net',
        'email'       => 'agent@example.com',
        'api_token'   => 'fake-token',
        'project_key' => 'PRJ',
    ]);

    (new CardTransitionService())->transitionToCodeReview($report);

    Http::assertSentCount(2);
});

test('adds code-review label on github issue', function (): void {
    Http::fake([
        'api.github.com/*' => Http::response([], 200),
    ]);

    [$report] = makeReportWithPlatform(ExternalPlatform::GitHub, [
        'token' => 'ghp_secret',
        'owner' => 'acme',
        'repo'  => 'my-repo',
    ]);

    (new CardTransitionService())->transitionToCodeReview($report);

    Http::assertSentCount(2); // add label + remove in-progress
});

test('updates gitlab issue labels', function (): void {
    Http::fake([
        'gitlab.com/*' => Http::response([], 200),
    ]);

    [$report] = makeReportWithPlatform(ExternalPlatform::GitLab, [
        'token'      => 'glpat-secret',
        'project_id' => '123',
        'base_url'   => 'https://gitlab.com',
    ]);

    (new CardTransitionService())->transitionToCodeReview($report);

    Http::assertSentCount(1);
});

test('does not throw when external_issue_id is null', function (): void {
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->approved()->create([
        'tenant_id'         => $tenant->id,
        'author_id'         => $author->id,
        'external_issue_id' => null,
        'external_platform' => null,
    ]);

    Http::fake();

    (new CardTransitionService())->transitionToCodeReview($report);

    Http::assertNothingSent();
});
