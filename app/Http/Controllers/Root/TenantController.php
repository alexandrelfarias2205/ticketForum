<?php declare(strict_types=1);

namespace App\Http\Controllers\Root;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Tenant::class);

        return view('root.tenants.index');
    }

    public function create(): View
    {
        $this->authorize('create', Tenant::class);

        return view('root.tenants.create');
    }

    public function show(Tenant $tenant): View
    {
        $this->authorize('view', $tenant);

        return view('root.tenants.show', compact('tenant'));
    }

    public function edit(Tenant $tenant): View
    {
        $this->authorize('update', $tenant);

        return view('root.tenants.edit', compact('tenant'));
    }
}
