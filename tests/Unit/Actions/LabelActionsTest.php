<?php declare(strict_types=1);

use App\Actions\Labels\CreateLabelAction;
use App\Actions\Labels\DeleteLabelAction;
use App\Actions\Labels\UpdateLabelAction;
use App\Models\Label;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('CreateLabelAction creates label with given name and color', function (): void {
    $root  = User::factory()->root()->create();

    $label = app(CreateLabelAction::class)->handle($root, [
        'name'  => 'Backend',
        'color' => '#ff0000',
    ]);

    expect($label->name)->toBe('Backend')
        ->and($label->color)->toBe('#ff0000')
        ->and((string) $label->created_by)->toBe((string) $root->id);
});

test('CreateLabelAction uses default color when none provided', function (): void {
    $root = User::factory()->root()->create();

    $label = app(CreateLabelAction::class)->handle($root, ['name' => 'Frontend']);

    expect($label->color)->toBe('#6366f1');
});

test('UpdateLabelAction updates name and color', function (): void {
    $root  = User::factory()->root()->create();
    $label = Label::create([
        'id'         => Str::uuid(),
        'name'       => 'Old Name',
        'color'      => '#000000',
        'created_by' => $root->id,
    ]);

    $updated = app(UpdateLabelAction::class)->handle($label, [
        'name'  => 'New Name',
        'color' => '#ffffff',
    ]);

    expect($updated->name)->toBe('New Name')
        ->and($updated->color)->toBe('#ffffff');
});

test('DeleteLabelAction detaches label from reports and deletes it', function (): void {
    $root   = User::factory()->root()->create();
    $tenant = Tenant::factory()->create();
    $author = User::factory()->tenantUser($tenant)->create();
    $report = Report::factory()->pendingReview()->create([
        'tenant_id' => $tenant->id,
        'author_id' => $author->id,
    ]);
    $label = Label::create([
        'id'         => Str::uuid(),
        'name'       => 'To Delete',
        'color'      => '#aabbcc',
        'created_by' => $root->id,
    ]);
    $report->labels()->attach($label->id);

    app(DeleteLabelAction::class)->handle($label);

    expect(Label::find($label->id))->toBeNull()
        ->and($report->labels()->count())->toBe(0);
});
