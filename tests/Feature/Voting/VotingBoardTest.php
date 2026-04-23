<?php declare(strict_types=1);

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can view voting board', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    $this->actingAs($user)
        ->get(route('app.voting.index'))
        ->assertOk();
});

test('only published reports appear on voting board', function (): void {
    $tenant  = Tenant::factory()->create();
    $user    = User::factory()->tenantUser($tenant)->create();
    $author  = User::factory()->tenantUser($tenant)->create();

    $published = Report::factory()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
        'status'    => ReportStatus::PublishedForVoting,
        'title'     => 'Sugestão publicada',
    ]);

    $pending = Report::factory()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
        'status'    => ReportStatus::PendingReview,
        'title'     => 'Relatório pendente',
    ]);

    $approved = Report::factory()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
        'status'    => ReportStatus::Approved,
        'title'     => 'Relatório aprovado',
    ]);

    $this->actingAs($user)
        ->get(route('app.voting.index'))
        ->assertOk()
        ->assertSee('Sugestão publicada')
        ->assertDontSee('Relatório pendente')
        ->assertDontSee('Relatório aprovado');
});

test('reports from all tenants appear on voting board', function (): void {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $userA  = User::factory()->tenantUser($tenantA)->create();
    $authorA = User::factory()->tenantUser($tenantA)->create();
    $authorB = User::factory()->tenantUser($tenantB)->create();

    Report::factory()->create([
        'tenant_id' => $tenantA->id,
        'author_id' => $authorA->id,
        'status'    => ReportStatus::PublishedForVoting,
        'title'     => 'Sugestão do Tenant A',
    ]);

    Report::factory()->create([
        'tenant_id' => $tenantB->id,
        'author_id' => $authorB->id,
        'status'    => ReportStatus::PublishedForVoting,
        'title'     => 'Sugestão do Tenant B',
    ]);

    $this->actingAs($userA)
        ->get(route('app.voting.index'))
        ->assertOk()
        ->assertSee('Sugestão do Tenant A')
        ->assertSee('Sugestão do Tenant B');
});

test('unauthenticated user cannot view voting board', function (): void {
    $this->get(route('app.voting.index'))
        ->assertRedirect(route('login'));
});
