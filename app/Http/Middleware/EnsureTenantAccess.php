<?php declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureTenantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Root users bypass tenant checks
        if ($user->role->isRoot()) {
            return $next($request);
        }

        // Tenant users must belong to an active tenant
        if (! $user->tenant_id || ! $user->tenant || ! $user->tenant->is_active) {
            abort(403, 'Empresa não encontrada ou inativa.');
        }

        return $next($request);
    }
}
