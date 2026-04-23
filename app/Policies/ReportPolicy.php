<?php declare(strict_types=1);

namespace App\Policies;

use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // authenticated users (root or same-tenant enforced by TenantScope)
    }

    public function view(User $user, Report $report): bool
    {
        if ($user->role->isRoot()) {
            return true;
        }

        return $user->tenant_id === $report->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::TenantUser || $user->role === UserRole::TenantAdmin;
    }

    public function update(User $user, Report $report): bool
    {
        if ($user->role->isRoot()) {
            return true;
        }

        return $user->id === $report->author_id
            && $report->status === ReportStatus::PendingReview;
    }

    public function delete(User $user, Report $report): bool
    {
        return $user->role->isRoot();
    }

    public function approve(User $user, Report $report): bool
    {
        return $user->role->isRoot();
    }

    public function publish(User $user, Report $report): bool
    {
        return $user->role->isRoot();
    }

    public function createIssue(User $user, Report $report): bool
    {
        return $user->role->isRoot();
    }
}
