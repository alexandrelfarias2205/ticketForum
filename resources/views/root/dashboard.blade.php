<x-layouts.root title="Painel">
    @php
        use App\Models\Tenant;
        use App\Models\User;
        use App\Models\Report;
        use App\Models\Scopes\TenantScope;
        use App\Enums\ReportStatus;

        $tenantsTotal = Tenant::count();
        $tenantsActive = Tenant::where('is_active', true)->count();
        $usersTotal = User::count();
        $reportsTotal = Report::withoutGlobalScope(TenantScope::class)->count();
        $reportsPending = Report::withoutGlobalScope(TenantScope::class)
            ->where('status', ReportStatus::PendingReview->value)->count();
        $reportsPublished = Report::withoutGlobalScope(TenantScope::class)
            ->where('status', ReportStatus::PublishedForVoting->value)->count();
        $reportsInProgress = Report::withoutGlobalScope(TenantScope::class)
            ->where('status', ReportStatus::InProgress->value)->count();
        $reportsDone = Report::withoutGlobalScope(TenantScope::class)
            ->where('status', ReportStatus::Done->value)->count();
    @endphp

    <div class="space-y-6">
        <header>
            <h1 class="page-title">Painel da plataforma</h1>
            <p class="page-subtitle">Visão geral de todos os tenants, usuários e tickets do ticketForum.</p>
        </header>

        {{-- KPI cards --}}
        <section aria-label="Indicadores" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card
                label="Empresas"
                :value="$tenantsTotal"
                tone="brand"
                :hint="$tenantsActive . ' ativas'"
                :href="route('root.tenants.index')"
                :icon="'<svg class=&quot;h-5 w-5&quot; fill=&quot;none&quot; viewBox=&quot;0 0 24 24&quot; stroke-width=&quot;1.8&quot; stroke=&quot;currentColor&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; d=&quot;M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21&quot; /></svg>'"
            />

            <x-stat-card
                label="Usuários"
                :value="$usersTotal"
                tone="info"
                hint="Em todos os tenants"
                :href="route('root.users.index')"
                :icon="'<svg class=&quot;h-5 w-5&quot; fill=&quot;none&quot; viewBox=&quot;0 0 24 24&quot; stroke-width=&quot;1.8&quot; stroke=&quot;currentColor&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; d=&quot;M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z&quot; /></svg>'"
            />

            <x-stat-card
                label="Aguardando revisão"
                :value="$reportsPending"
                tone="warning"
                hint="Tickets para triagem"
                :href="route('root.reports.index')"
                :icon="'<svg class=&quot;h-5 w-5&quot; fill=&quot;none&quot; viewBox=&quot;0 0 24 24&quot; stroke-width=&quot;1.8&quot; stroke=&quot;currentColor&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; d=&quot;M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z&quot; /></svg>'"
            />

            <x-stat-card
                label="Total de tickets"
                :value="$reportsTotal"
                tone="accent"
                hint="Todos os status"
                :icon="'<svg class=&quot;h-5 w-5&quot; fill=&quot;none&quot; viewBox=&quot;0 0 24 24&quot; stroke-width=&quot;1.8&quot; stroke=&quot;currentColor&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; d=&quot;M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z&quot; /></svg>'"
            />
        </section>

        {{-- Pipeline overview --}}
        <section aria-label="Pipeline" class="card">
            <h2 class="section-title mb-4">Pipeline</h2>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-xl border border-white/10 bg-white/[0.03] p-4">
                    <p class="text-xs uppercase tracking-wider text-slate-400">Em revisão</p>
                    <p class="mt-1 text-2xl font-bold text-warning-300">{{ $reportsPending }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/[0.03] p-4">
                    <p class="text-xs uppercase tracking-wider text-slate-400">Em votação</p>
                    <p class="mt-1 text-2xl font-bold text-brand-300">{{ $reportsPublished }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/[0.03] p-4">
                    <p class="text-xs uppercase tracking-wider text-slate-400">Em desenvolvimento</p>
                    <p class="mt-1 text-2xl font-bold text-info-300">{{ $reportsInProgress }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/[0.03] p-4">
                    <p class="text-xs uppercase tracking-wider text-slate-400">Concluídos</p>
                    <p class="mt-1 text-2xl font-bold text-success-300">{{ $reportsDone }}</p>
                </div>
            </div>
        </section>

        {{-- Quick links --}}
        <section aria-label="Atalhos">
            <h2 class="section-title mb-4">Atalhos</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <a href="{{ route('root.reports.index') }}" class="card card-hover">
                    <h3 class="text-base font-semibold text-white">Fila de revisão</h3>
                    <p class="mt-1 text-sm text-slate-400">Aprove ou rejeite os tickets enviados pelos usuários.</p>
                </a>
                <a href="{{ route('root.voting.index') }}" class="card card-hover">
                    <h3 class="text-base font-semibold text-white">Ranking de votos</h3>
                    <p class="mt-1 text-sm text-slate-400">Veja as melhorias mais votadas e priorize a entrega.</p>
                </a>
                <a href="{{ route('root.delivered.index') }}" class="card card-hover">
                    <h3 class="text-base font-semibold text-white">Entregas</h3>
                    <p class="mt-1 text-sm text-slate-400">Tickets concluídos e marcos recentes.</p>
                </a>
                <a href="{{ route('root.labels.index') }}" class="card card-hover">
                    <h3 class="text-base font-semibold text-white">Etiquetas</h3>
                    <p class="mt-1 text-sm text-slate-400">Gerencie etiquetas para categorização de tickets.</p>
                </a>
                <a href="{{ route('root.agent.dashboard') }}" class="card card-hover">
                    <h3 class="text-base font-semibold text-white">Atividade do agente</h3>
                    <p class="mt-1 text-sm text-slate-400">Acompanhe ações automatizadas e logs do bot.</p>
                </a>
                <a href="{{ route('root.users.index') }}" class="card card-hover">
                    <h3 class="text-base font-semibold text-white">Usuários</h3>
                    <p class="mt-1 text-sm text-slate-400">Lista global de usuários da plataforma.</p>
                </a>
            </div>
        </section>
    </div>
</x-layouts.root>
