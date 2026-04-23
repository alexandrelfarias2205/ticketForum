<?php declare(strict_types=1);

use App\Livewire\Reports\CreateReport;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('tenant_user can submit report via livewire component', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    $this->actingAs($user);

    Livewire::test(CreateReport::class)
        ->set('type', 'bug')
        ->set('title', 'A reproducible crash on checkout')
        ->set('description', 'This crashes every time on mobile.')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    expect(Report::withoutGlobalScopes()->where('author_id', $user->id)->exists())->toBeTrue();
});

test('save fails validation with missing title', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    $this->actingAs($user);

    Livewire::test(CreateReport::class)
        ->set('type', 'bug')
        ->set('title', '')
        ->set('description', 'Some description here.')
        ->call('save')
        ->assertHasErrors(['title']);
});

test('save fails validation with invalid type', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    $this->actingAs($user);

    Livewire::test(CreateReport::class)
        ->set('type', 'invalid')
        ->set('title', 'Valid title')
        ->set('description', 'Valid description text here.')
        ->call('save')
        ->assertHasErrors(['type']);
});

test('save fails validation with description too short', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    $this->actingAs($user);

    Livewire::test(CreateReport::class)
        ->set('type', 'bug')
        ->set('title', 'Valid title here')
        ->set('description', 'Short')
        ->call('save')
        ->assertHasErrors(['description']);
});

test('root is forbidden from saving report', function (): void {
    $root = User::factory()->root()->create();

    $this->actingAs($root);

    Livewire::test(CreateReport::class)
        ->set('type', 'bug')
        ->set('title', 'Title for root')
        ->set('description', 'Description long enough to pass min:10.')
        ->call('save')
        ->assertForbidden();
});

test('addLink appends valid url and clears input', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    $this->actingAs($user);

    Livewire::test(CreateReport::class)
        ->set('newLink', 'https://example.com')
        ->call('addLink')
        ->assertSet('links', ['https://example.com'])
        ->assertSet('newLink', '');
});

test('removeLink removes the link at given index', function (): void {
    $tenant = Tenant::factory()->create();
    $user   = User::factory()->tenantUser($tenant)->create();

    $this->actingAs($user);

    Livewire::test(CreateReport::class)
        ->set('links', ['https://first.com', 'https://second.com'])
        ->call('removeLink', 0)
        ->assertSet('links', ['https://second.com']);
});
