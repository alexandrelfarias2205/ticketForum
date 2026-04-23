<?php declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReportStatus;
use App\Enums\ReportType;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        $tenant = Tenant::factory()->create();
        $author = User::factory()->tenantUser($tenant)->create();

        return [
            'id'          => Str::uuid(),
            'tenant_id'   => $tenant->id,
            'author_id'   => $author->id,
            'type'        => ReportType::Bug,
            'title'       => fake()->sentence(6),
            'description' => fake()->paragraph(3),
            'status'      => ReportStatus::PendingReview,
            'vote_count'  => 0,
        ];
    }

    // Type states

    public function bug(): static
    {
        return $this->state(['type' => ReportType::Bug]);
    }

    public function improvement(): static
    {
        return $this->state(['type' => ReportType::Improvement]);
    }

    public function featureRequest(): static
    {
        return $this->state(['type' => ReportType::FeatureRequest]);
    }

    // Status states

    public function pendingReview(): static
    {
        return $this->state(['status' => ReportStatus::PendingReview]);
    }

    public function approved(): static
    {
        return $this->state(['status' => ReportStatus::Approved]);
    }

    public function publishedForVoting(): static
    {
        return $this->state(['status' => ReportStatus::PublishedForVoting]);
    }

    public function rejected(): static
    {
        return $this->state(['status' => ReportStatus::Rejected]);
    }
}
