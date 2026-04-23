<?php declare(strict_types=1);

namespace App\Providers;

use App\Models\Label;
use App\Models\Report;
use App\Models\ReportAttachment;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vote;
use App\Policies\LabelPolicy;
use App\Policies\ReportPolicy;
use App\Policies\TenantPolicy;
use App\Policies\UserPolicy;
use App\Policies\VotePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Tenant::class, TenantPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Report::class, ReportPolicy::class);
        Gate::policy(ReportAttachment::class, ReportPolicy::class);
        Gate::policy(Label::class, LabelPolicy::class);
        Gate::policy(Vote::class, VotePolicy::class);
    }
}
