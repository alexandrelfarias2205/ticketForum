<?php declare(strict_types=1);

use App\Actions\Labels\CreateLabelAction;
use App\Actions\Labels\DeleteLabelAction;
use App\Actions\Labels\UpdateLabelAction;
use App\Http\Requests\Labels\StoreLabelRequest;
use App\Models\Label;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('root can view label list', function (): void {
    $root = User::factory()->root()->create();

    $this->actingAs($root)
        ->get('/root/labels')
        ->assertStatus(200);
});

test('root can create label', function (): void {
    $root = User::factory()->root()->create();
    $this->actingAs($root);

    $label = app(CreateLabelAction::class)->handle($root, [
        'name'  => 'Bug Crítico',
        'color' => '#ef4444',
    ]);

    expect($label->name)->toBe('Bug Crítico')
        ->and($label->color)->toBe('#ef4444')
        ->and($label->created_by)->toBe($root->id);
});

test('root can update label', function (): void {
    $root = User::factory()->root()->create();
    $this->actingAs($root);

    $label = app(CreateLabelAction::class)->handle($root, [
        'name'  => 'Original',
        'color' => '#6366f1',
    ]);

    $updated = app(UpdateLabelAction::class)->handle($label, [
        'name'  => 'Atualizado',
        'color' => '#10b981',
    ]);

    expect($updated->name)->toBe('Atualizado')
        ->and($updated->color)->toBe('#10b981');
});

test('root can delete label', function (): void {
    $root = User::factory()->root()->create();
    $this->actingAs($root);

    $label = app(CreateLabelAction::class)->handle($root, [
        'name'  => 'Para Excluir',
        'color' => '#6366f1',
    ]);

    $labelId = $label->id;

    app(DeleteLabelAction::class)->handle($label);

    $this->assertDatabaseMissing('labels', ['id' => $labelId]);
});

test('label is detached from reports on delete', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();

    $label  = app(CreateLabelAction::class)->handle($root, [
        'name'  => 'Etiqueta Vinculada',
        'color' => '#6366f1',
    ]);

    $report = Report::factory()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);

    $report->labels()->attach($label->id);

    $this->assertDatabaseHas('report_labels', [
        'report_id' => $report->id,
        'label_id'  => $label->id,
    ]);

    app(DeleteLabelAction::class)->handle($label);

    $this->assertDatabaseMissing('report_labels', [
        'report_id' => $report->id,
        'label_id'  => $label->id,
    ]);
});

test('tenant_admin cannot access label routes', function (): void {
    $tenant = Tenant::factory()->create();
    $admin  = User::factory()->tenantAdmin($tenant)->create();

    $this->actingAs($admin)
        ->get('/root/labels')
        ->assertStatus(403);
});

test('label name must be unique', function (): void {
    $root = User::factory()->root()->create();
    $this->actingAs($root);

    app(CreateLabelAction::class)->handle($root, [
        'name'  => 'Duplicado',
        'color' => '#6366f1',
    ]);

    $request = StoreLabelRequest::create('/root/labels', 'POST', [
        'name'  => 'Duplicado',
        'color' => '#6366f1',
    ]);

    $request->setUserResolver(fn () => $root);

    $validator = validator($request->all(), (new StoreLabelRequest())->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('name'))->toBeTrue();
});
