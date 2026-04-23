<?php declare(strict_types=1);

namespace App\Http\Controllers\Root;

use App\Actions\Integrations\SaveIntegrationConfigAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Integrations\StoreGitHubConfigRequest;
use App\Http\Requests\Integrations\StoreJiraConfigRequest;
use App\Models\Tenant;
use App\Models\TenantIntegration;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class IntegrationController extends Controller
{
    public function edit(Tenant $tenant): View
    {
        $this->authorize('update', $tenant);

        $integration = TenantIntegration::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->first();

        return view('root.integrations.edit', compact('tenant', 'integration'));
    }

    public function storeJira(
        StoreJiraConfigRequest $request,
        Tenant $tenant,
        SaveIntegrationConfigAction $action,
    ): RedirectResponse {
        $action->handle($tenant, 'jira', $request->validated());

        return redirect()
            ->route('root.tenants.integration.edit', $tenant)
            ->with('success', 'Jira integration saved successfully.');
    }

    public function storeGitHub(
        StoreGitHubConfigRequest $request,
        Tenant $tenant,
        SaveIntegrationConfigAction $action,
    ): RedirectResponse {
        $action->handle($tenant, 'github', $request->validated());

        return redirect()
            ->route('root.tenants.integration.edit', $tenant)
            ->with('success', 'GitHub integration saved successfully.');
    }
}
