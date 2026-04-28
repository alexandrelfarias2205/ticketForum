<?php declare(strict_types=1);

use App\Enums\ExternalPlatform;
use App\Enums\ReportStatus;
use App\Models\Product;
use App\Models\ProductIntegration;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->tenant  = Tenant::factory()->create();
    $this->author  = User::factory()->tenantUser($this->tenant)->create();
    $this->product = Product::factory()->create();
    $this->tenant->products()->attach($this->product);

    // Provide a real ProductIntegration so DispatchIssueCreationAction can resolve a platform
    ProductIntegration::create([
        'product_id' => $this->product->id,
        'platform'   => ExternalPlatform::GitHub,
        'config'     => encrypt([
            'token' => 'ghp_test',
            'owner' => 'acme',
            'repo'  => 'repo',
        ]),
        'is_active'  => true,
    ]);

    Queue::fake();
});

test('promotes top N improvements to in_progress', function (): void {
    Report::factory()->improvement()->publishedForVoting()->create([
        'tenant_id'  => $this->tenant->id,
        'author_id'  => $this->author->id,
        'product_id' => $this->product->id,
        'vote_count' => 10,
    ]);
    Report::factory()->improvement()->publishedForVoting()->create([
        'tenant_id'  => $this->tenant->id,
        'author_id'  => $this->author->id,
        'product_id' => $this->product->id,
        'vote_count' => 5,
    ]);

    $this->artisan('improvements:promote', ['--limit' => 2])->assertSuccessful();

    $count = Report::withoutGlobalScopes()
        ->where('status', ReportStatus::InProgress)
        ->count();

    expect($count)->toBe(2);
});

test('respects --limit option', function (): void {
    for ($i = 0; $i < 5; $i++) {
        Report::factory()->improvement()->publishedForVoting()->create([
            'tenant_id'  => $this->tenant->id,
            'author_id'  => $this->author->id,
            'product_id' => $this->product->id,
            'vote_count' => $i,
        ]);
    }

    $this->artisan('improvements:promote', ['--limit' => 2])->assertSuccessful();

    $count = Report::withoutGlobalScopes()
        ->where('status', ReportStatus::InProgress)
        ->count();

    expect($count)->toBe(2);
});

test('outputs message when no eligible improvements exist', function (): void {
    $this->artisan('improvements:promote')
        ->assertSuccessful()
        ->expectsOutput('No improvements eligible for promotion.');
});

test('does not promote duplicate improvements', function (): void {
    Report::factory()->improvement()->publishedForVoting()->create([
        'tenant_id'    => $this->tenant->id,
        'author_id'    => $this->author->id,
        'product_id'   => $this->product->id,
        'vote_count'   => 20,
        'is_duplicate' => true,
    ]);
    Report::factory()->improvement()->publishedForVoting()->create([
        'tenant_id'  => $this->tenant->id,
        'author_id'  => $this->author->id,
        'product_id' => $this->product->id,
        'vote_count' => 5,
    ]);

    $this->artisan('improvements:promote', ['--limit' => 5])->assertSuccessful();

    $count = Report::withoutGlobalScopes()
        ->where('status', ReportStatus::InProgress)
        ->count();

    expect($count)->toBe(1);
});
