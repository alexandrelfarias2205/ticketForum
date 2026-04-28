<?php declare(strict_types=1);

use App\Enums\ExternalPlatform;
use App\Enums\IntegrationJobStatus;
use App\Enums\ReportStatus;
use App\Enums\ReportType;
use App\Livewire\Tenant\Dashboard\TenantDashboard;
use App\Models\IntegrationJob;
use App\Models\Product;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\TenantIntegration;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Cache::flush();
});

test('renders for tenant_admin without "Meus tickets" but with integrations section', function (): void {
    $tenant = Tenant::factory()->create();
    $admin  = User::factory()->tenantAdmin($tenant)->create();

    Report::factory()->bug()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $admin->id,
    ]);

    Livewire::actingAs($admin)
        ->test(TenantDashboard::class)
        ->assertSee('Painel')
        ->assertSee('Saúde das integrações')
        ->assertDontSee('Meus tickets');
});

test('renders for tenant_user with "Meus tickets" but without integrations section', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    Livewire::actingAs($user)
        ->test(TenantDashboard::class)
        ->assertSee('Meus tickets')
        ->assertDontSee('Saúde das integrações');
});

test('multi-tenant isolation — tenant A user does not see tenant B reports', function (): void {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $userA   = User::factory()->tenantUser($tenantA)->create();
    $authorA = User::factory()->tenantUser($tenantA)->create();
    $authorB = User::factory()->tenantUser($tenantB)->create();

    // 2 open reports for tenant A
    Report::factory()->count(2)->create([
        'tenant_id' => $tenantA->id,
        'author_id' => $authorA->id,
        'status'    => ReportStatus::PendingReview,
    ]);

    // 5 open reports for tenant B (must not leak)
    Report::factory()->count(5)->create([
        'tenant_id' => $tenantB->id,
        'author_id' => $authorB->id,
        'status'    => ReportStatus::PendingReview,
    ]);

    $component = Livewire::actingAs($userA)->test(TenantDashboard::class);

    $cards = $component->get('headerCards');

    expect($cards['open_tickets'])->toBe(2);
});

test('header cards reflect the correct counts', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    // 3 open
    Report::factory()->count(3)->create([
        'tenant_id' => $tenant->id,
        'author_id' => $user->id,
        'status'    => ReportStatus::PendingReview,
    ]);

    // 1 done this month
    $resolved = Report::factory()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $user->id,
        'status'    => ReportStatus::Done,
    ]);
    // Force updated_at into the current month so the count picks it up
    $resolved->forceFill(['updated_at' => now()->startOfMonth()->addDay()])->saveQuietly();

    $component = Livewire::actingAs($user)->test(TenantDashboard::class);
    $cards     = $component->get('headerCards');

    expect($cards['open_tickets'])->toBe(3);
    expect($cards['resolved_this_month'])->toBe(1);
});

test('status funnel groups reports by type and status', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    // 2 bugs in PendingReview
    Report::factory()->count(2)->bug()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $user->id,
    ]);

    // 1 bug in Done
    Report::factory()->bug()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $user->id,
        'status'    => ReportStatus::Done,
    ]);

    // 3 improvements in PublishedForVoting
    Report::factory()->count(3)->improvement()->publishedForVoting()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $user->id,
    ]);

    $component = Livewire::actingAs($user)->test(TenantDashboard::class);
    $funnel    = $component->get('funnel');

    expect($funnel['bug'][ReportStatus::PendingReview->value])->toBe(2);
    expect($funnel['bug'][ReportStatus::Done->value])->toBe(1);
    expect($funnel['improvement'][ReportStatus::PublishedForVoting->value])->toBe(3);
});

test('top voted improvements ordered by vote_count desc after lazy load', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();
    $author = User::factory()->tenantUser($tenant)->create();

    Report::factory()->improvement()->publishedForVoting()->create([
        'tenant_id'  => $tenant->id,
        'author_id'  => $author->id,
        'title'      => 'Less popular',
        'vote_count' => 5,
    ]);
    Report::factory()->improvement()->publishedForVoting()->create([
        'tenant_id'  => $tenant->id,
        'author_id'  => $author->id,
        'title'      => 'Most popular',
        'vote_count' => 10,
    ]);
    Report::factory()->improvement()->publishedForVoting()->create([
        'tenant_id'  => $tenant->id,
        'author_id'  => $author->id,
        'title'      => 'Least popular',
        'vote_count' => 1,
    ]);

    $component = Livewire::actingAs($user)
        ->test(TenantDashboard::class)
        ->call('loadDeferredSections');

    $top = $component->get('topVoted');

    expect($top)->toHaveCount(3);
    expect($top[0]['title'])->toBe('Most popular');
    expect($top[0]['vote_count'])->toBe(10);
    expect($top[1]['vote_count'])->toBe(5);
});

test('vote action creates a vote and increments vote_count', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->improvement()->publishedForVoting()->create([
        'tenant_id'  => $tenant->id,
        'author_id'  => $author->id,
        'vote_count' => 0,
    ]);

    Livewire::actingAs($user)
        ->test(TenantDashboard::class)
        ->call('loadDeferredSections')
        ->call('vote', $report->id);

    expect(Vote::where('user_id', $user->id)->where('report_id', $report->id)->exists())->toBeTrue();
    expect(Report::withoutGlobalScopes()->find($report->id)->vote_count)->toBe(1);
});

test('voting twice on same report fails gracefully', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $report = Report::factory()->improvement()->publishedForVoting()->create([
        'tenant_id'  => $tenant->id,
        'author_id'  => $author->id,
        'vote_count' => 0,
    ]);

    $component = Livewire::actingAs($user)
        ->test(TenantDashboard::class)
        ->call('loadDeferredSections')
        ->call('vote', $report->id)
        ->call('vote', $report->id);

    $component->assertDispatched('notify', type: 'error', message: 'Você já votou nesta melhoria');

    expect(Vote::where('user_id', $user->id)->where('report_id', $report->id)->count())->toBe(1);
});

test('byProduct returns empty when tenant has only one product', function (): void {
    $tenant  = Tenant::factory()->create();
    $user    = User::factory()->tenantUser($tenant)->create();
    $product = Product::factory()->forTenant($tenant)->create();

    Report::factory()->count(3)->create([
        'tenant_id'  => $tenant->id,
        'author_id'  => $user->id,
        'product_id' => $product->id,
    ]);

    $component = Livewire::actingAs($user)
        ->test(TenantDashboard::class)
        ->call('loadDeferredSections');

    expect($component->get('byProduct'))->toBe([]);
});

test('byProduct returns aggregated rows when tenant has multiple products', function (): void {
    $tenant   = Tenant::factory()->create();
    $user     = User::factory()->tenantUser($tenant)->create();
    $productA = Product::factory()->forTenant($tenant)->create(['name' => 'Alpha']);
    $productB = Product::factory()->forTenant($tenant)->create(['name' => 'Beta']);

    Report::factory()->create([
        'tenant_id'  => $tenant->id,
        'author_id'  => $user->id,
        'product_id' => $productA->id,
        'status'     => ReportStatus::PendingReview,
    ]);
    Report::factory()->create([
        'tenant_id'  => $tenant->id,
        'author_id'  => $user->id,
        'product_id' => $productB->id,
        'status'     => ReportStatus::Done,
    ]);

    $component = Livewire::actingAs($user)
        ->test(TenantDashboard::class)
        ->call('loadDeferredSections');

    $byProduct = $component->get('byProduct');

    expect($byProduct)->toHaveCount(2);
    $names = array_column($byProduct, 'product_name');
    expect($names)->toContain('Alpha');
    expect($names)->toContain('Beta');
});

test('integrations health is empty for tenant_user', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    TenantIntegration::create([
        'id'        => Str::uuid()->toString(),
        'tenant_id' => $tenant->id,
        'platform'  => ExternalPlatform::Jira,
        'config'    => 'irrelevant',
        'is_active' => true,
    ]);

    $component = Livewire::actingAs($user)
        ->test(TenantDashboard::class)
        ->call('loadDeferredSections');

    expect($component->get('canSeeIntegrations'))->toBeFalse();
    expect($component->get('integrations'))->toBe([]);
});

test('integrations health is populated for tenant_admin', function (): void {
    $tenant = Tenant::factory()->create();
    $admin  = User::factory()->tenantAdmin($tenant)->create();
    $author = User::factory()->tenantUser($tenant)->create();

    TenantIntegration::create([
        'id'        => Str::uuid()->toString(),
        'tenant_id' => $tenant->id,
        'platform'  => ExternalPlatform::Jira,
        'config'    => 'irrelevant',
        'is_active' => true,
    ]);

    $report = Report::factory()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    IntegrationJob::create([
        'id'         => Str::uuid()->toString(),
        'report_id'  => $report->id,
        'platform'   => ExternalPlatform::Jira,
        'status'     => IntegrationJobStatus::Done,
        'attempts'   => 1,
        'created_at' => now()->subHour(),
        'updated_at' => now()->subHour(),
    ]);

    $component = Livewire::actingAs($admin)
        ->test(TenantDashboard::class)
        ->call('loadDeferredSections');

    expect($component->get('canSeeIntegrations'))->toBeTrue();
    $integrations = $component->get('integrations');
    expect($integrations)->toHaveCount(1);
    expect($integrations[0]['platform'])->toBe(ExternalPlatform::Jira);
    expect($integrations[0]['is_active'])->toBeTrue();
});

test('filterByStatus redirects to reports index with status query', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    Livewire::actingAs($user)
        ->test(TenantDashboard::class)
        ->call('filterByStatus', ReportStatus::PendingReview->value)
        ->assertRedirect(route('app.reports.index', ['status' => ReportStatus::PendingReview->value]));
});
