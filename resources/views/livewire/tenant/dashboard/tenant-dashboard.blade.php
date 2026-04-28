@php
    use App\Enums\ReportStatus;
    use App\Enums\ReportType;
    use App\Enums\IntegrationJobStatus;
    use App\Enums\ExternalPlatform;

    /** Format avg resolution hours into a short pt-BR string. */
    $formatHours = static function (?float $hours): string {
        if ($hours === null) {
            return '—';
        }
        if ($hours < 1) {
            return '<1h';
        }
        if ($hours < 24) {
            return (int) round($hours) . 'h';
        }
        $days = (int) floor($hours / 24);
        $rest = (int) round($hours - ($days * 24));
        return $rest > 0 ? "{$days}d {$rest}h" : "{$days}d";
    };

    $deltaBadge = static function (int $delta): array {
        if ($delta > 0) {
            return ['symbol' => '▲', 'classes' => 'text-emerald-400'];
        }
        if ($delta < 0) {
            return ['symbol' => '▼', 'classes' => 'text-rose-400'];
        }
        return ['symbol' => '■', 'classes' => 'text-slate-400'];
    };

    $integrationDot = static function (array $integration): string {
        if (! $integration['is_active']) {
            return 'bg-slate-500';
        }
        if (($integration['failed_last_24h'] ?? 0) > 0) {
            return 'bg-rose-500';
        }
        if ($integration['last_job_status'] === IntegrationJobStatus::Pending
            || $integration['last_job_status'] === IntegrationJobStatus::Processing) {
            return 'bg-amber-400';
        }
        return 'bg-emerald-500';
    };

    $statusOrder = [
        ReportStatus::PendingReview,
        ReportStatus::Approved,
        ReportStatus::PublishedForVoting,
        ReportStatus::InProgress,
        ReportStatus::Done,
        ReportStatus::Rejected,
    ];

    $glassCard = 'rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md p-6 shadow-xl';
@endphp

<div wire:init="loadDeferredSections" class="-m-6 p-6 min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 text-slate-100">
    <div class="mx-auto max-w-7xl space-y-6">

        {{-- Header --}}
        <header class="flex items-end justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white">Painel</h1>
                <p class="mt-1 text-sm text-slate-400">Visão geral do seu workspace</p>
            </div>
        </header>

        {{-- SECTION 1 — KPI Cards --}}
        @php($cards = $this->headerCards)
        <section aria-label="Indicadores" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

            <div class="{{ $glassCard }}">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Tickets abertos</p>
                <p class="mt-2 text-3xl font-bold text-white">{{ $cards['open_tickets'] }}</p>
                @php($d = $deltaBadge($cards['open_tickets_delta']))
                <p class="mt-2 text-xs {{ $d['classes'] }}">
                    {{ $d['symbol'] }} {{ abs($cards['open_tickets_delta']) }} vs semana anterior
                </p>
            </div>

            <div class="{{ $glassCard }}">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Resolvidos no mês</p>
                <p class="mt-2 text-3xl font-bold text-white">{{ $cards['resolved_this_month'] }}</p>
                @php($d = $deltaBadge($cards['resolved_delta']))
                <p class="mt-2 text-xs {{ $d['classes'] }}">
                    {{ $d['symbol'] }} {{ abs($cards['resolved_delta']) }} vs mês anterior
                </p>
            </div>

            <div class="{{ $glassCard }}">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Tempo médio de resolução</p>
                <p class="mt-2 text-3xl font-bold text-white">{{ $formatHours($cards['avg_resolution_hours']) }}</p>
                <p class="mt-2 text-xs text-slate-400">últimos 90 dias</p>
            </div>

            <div class="{{ $glassCard }}">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Votos ativos</p>
                <p class="mt-2 text-3xl font-bold text-white">{{ $cards['active_votes'] }}</p>
                @php($d = $deltaBadge($cards['votes_delta']))
                <p class="mt-2 text-xs {{ $d['classes'] }}">
                    {{ $d['symbol'] }} {{ abs($cards['votes_delta']) }} vs semana anterior
                </p>
            </div>

        </section>

        {{-- SECTION 2 — Status Funnel (Bug + Improvement) --}}
        @php($funnel = $this->funnel)
        <section aria-label="Funil de status" class="grid grid-cols-1 gap-6 md:grid-cols-2">

            @foreach ([
                'bug' => 'Funil — Bugs',
                'improvement' => 'Funil — Melhorias',
            ] as $bucket => $title)
                @php($buckets = $funnel[$bucket] ?? [])
                @php($total = array_sum($buckets))
                <div class="{{ $glassCard }}">
                    <h2 class="mb-4 text-lg font-semibold text-white">{{ $title }}</h2>
                    @if ($total === 0)
                        <p class="text-sm text-slate-400">Nenhum registro neste segmento ainda.</p>
                    @else
                        <ul class="space-y-3">
                            @foreach ($statusOrder as $status)
                                @php($count = $buckets[$status->value] ?? 0)
                                @php($pct = $total > 0 ? (int) round(($count / $total) * 100) : 0)
                                <li>
                                    <button
                                        type="button"
                                        wire:click="filterByStatus('{{ $status->value }}')"
                                        class="group flex w-full items-center justify-between gap-3 rounded-lg px-2 py-2 text-left transition hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                        aria-label="Filtrar relatórios com status {{ $status->label() }}"
                                    >
                                        <span class="flex items-center gap-3 min-w-0 flex-1">
                                            <span class="w-36 shrink-0 text-xs text-slate-300">{{ $status->label() }}</span>
                                            <span class="relative h-2 flex-1 overflow-hidden rounded-full bg-white/5">
                                                <span
                                                    class="absolute inset-y-0 left-0 rounded-full bg-gradient-to-r from-indigo-500 to-violet-500"
                                                    style="width: {{ $pct }}%"
                                                ></span>
                                            </span>
                                        </span>
                                        <span class="w-10 shrink-0 text-right text-sm font-semibold text-white">{{ $count }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach

        </section>

        {{-- SECTION 3 + SECTION 4 — Activity Feed + Top Voted --}}
        <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            {{-- Activity feed --}}
            <div class="{{ $glassCard }}">
                <h2 class="mb-4 text-lg font-semibold text-white">Atividade recente</h2>
                @php($feed = $this->feed)
                @if (empty($feed))
                    <p class="text-sm text-slate-400">Nenhuma atividade recente registrada.</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($feed as $event)
                            <li class="flex items-start gap-3 rounded-lg border border-white/5 bg-white/5 p-3">
                                <span aria-hidden="true" class="mt-1 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-indigo-500/20 text-indigo-300">
                                    @switch($event['type'])
                                        @case('vote_received')
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.5c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 012.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 00.322-1.672V3a.75.75 0 01.75-.75A2.25 2.25 0 0116.5 4.5c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 01-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 00-1.423-.23H5.904" /></svg>
                                            @break
                                        @case('integration_sync')
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 004.5 4.5H18a3.75 3.75 0 001.332-7.257 3 3 0 00-3.758-3.848 5.25 5.25 0 00-10.233 2.33A4.502 4.502 0 002.25 15z" /></svg>
                                            @break
                                        @case('agent_event')
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.847.814a4.5 4.5 0 00-3.09 3.09z" /></svg>
                                            @break
                                        @default
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                                    @endswitch
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm text-slate-200">{{ $event['message'] }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $event['created_at']->diffForHumans() }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Top voted improvements (lazy section) --}}
            <div class="{{ $glassCard }}">
                <h2 class="mb-4 text-lg font-semibold text-white">Top melhorias votadas</h2>

                @if (! $loadDeferred)
                    <div class="animate-pulse space-y-3" aria-hidden="true">
                        @for ($i = 0; $i < 4; $i++)
                            <div class="h-12 rounded-lg bg-white/5"></div>
                        @endfor
                    </div>
                @else
                    @php($topVoted = $this->topVoted)
                    @if (empty($topVoted))
                        <p class="text-sm text-slate-400">Nenhuma melhoria publicada para votação.</p>
                    @else
                        <ul class="space-y-3">
                            @foreach ($topVoted as $item)
                                <li class="flex items-center justify-between gap-3 rounded-lg border border-white/5 bg-white/5 p-3">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-white">{{ $item['title'] }}</p>
                                        <div class="mt-1 flex items-center gap-2">
                                            @if (! empty($item['product_name']))
                                                <span class="text-xs text-slate-400">{{ $item['product_name'] }}</span>
                                                <span class="text-xs text-slate-600">·</span>
                                            @endif
                                            <span class="inline-flex items-center rounded-full bg-indigo-500/15 px-2 py-0.5 text-xs font-medium text-indigo-300">
                                                {{ $item['status']->label() }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-3">
                                        <span class="text-sm font-bold text-white">{{ $item['vote_count'] }}</span>
                                        @if ($item['voted_by_me'])
                                            <button
                                                type="button"
                                                disabled
                                                class="cursor-not-allowed rounded-lg border border-emerald-400/40 bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold text-emerald-300"
                                            >
                                                Você votou
                                            </button>
                                        @else
                                            <button
                                                type="button"
                                                wire:click="vote('{{ $item['id'] }}')"
                                                wire:loading.attr="disabled"
                                                wire:target="vote('{{ $item['id'] }}')"
                                                class="rounded-lg border border-indigo-400/40 bg-indigo-500/20 px-3 py-1.5 text-xs font-semibold text-indigo-200 transition hover:bg-indigo-500/30 disabled:cursor-wait disabled:opacity-60"
                                            >
                                                Votar
                                            </button>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                @endif
            </div>

        </section>

        {{-- SECTION 5 — My Tickets (tenant_user only) --}}
        @if ($this->canSeeMyTickets)
            @php($myTickets = $this->myTickets)
            <section class="{{ $glassCard }}" aria-label="Meus tickets">
                <h2 class="mb-4 text-lg font-semibold text-white">Meus tickets</h2>
                @if (empty($myTickets))
                    <p class="text-sm text-slate-400">Você ainda não criou nenhum ticket.</p>
                @else
                    <ul class="divide-y divide-white/5">
                        @foreach ($myTickets as $ticket)
                            <li class="flex items-center justify-between gap-3 py-3">
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('app.reports.show', $ticket['id']) }}"
                                       class="truncate text-sm font-medium text-white hover:text-indigo-300">
                                        {{ $ticket['title'] }}
                                    </a>
                                    <p class="mt-1 text-xs text-slate-500">{{ $ticket['created_at']->diffForHumans() }}</p>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <span class="inline-flex items-center rounded-full bg-slate-500/20 px-2 py-0.5 text-xs font-medium text-slate-300">
                                        {{ $ticket['type']->label() }}
                                    </span>
                                    <span class="inline-flex items-center rounded-full bg-indigo-500/15 px-2 py-0.5 text-xs font-medium text-indigo-300">
                                        {{ $ticket['status']->label() }}
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endif

        {{-- SECTION 6 — By Product (lazy, only when tenant has > 1 product) --}}
        @if ($loadDeferred && ! empty($this->byProduct))
            <section aria-label="Por produto">
                <h2 class="mb-4 text-lg font-semibold text-white">Por produto</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($this->byProduct as $product)
                        <div class="{{ $glassCard }}">
                            <p class="truncate text-sm font-semibold text-white">{{ $product['product_name'] }}</p>
                            <dl class="mt-3 grid grid-cols-3 gap-2 text-center">
                                <div>
                                    <dt class="text-xs text-slate-400">Abertos</dt>
                                    <dd class="mt-1 text-lg font-bold text-white">{{ $product['open'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-slate-400">Em andamento</dt>
                                    <dd class="mt-1 text-lg font-bold text-amber-300">{{ $product['in_progress'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-slate-400">Concluídos</dt>
                                    <dd class="mt-1 text-lg font-bold text-emerald-300">{{ $product['done'] }}</dd>
                                </div>
                            </dl>
                        </div>
                    @endforeach
                </div>
            </section>
        @elseif (! $loadDeferred)
            <section aria-label="Por produto" class="{{ $glassCard }}">
                <h2 class="mb-4 text-lg font-semibold text-white">Por produto</h2>
                <div class="animate-pulse grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3" aria-hidden="true">
                    @for ($i = 0; $i < 3; $i++)
                        <div class="h-24 rounded-lg bg-white/5"></div>
                    @endfor
                </div>
            </section>
        @endif

        {{-- SECTION 7 — Integrations Health (tenant_admin only, lazy) --}}
        @if ($this->canSeeIntegrations)
            <section class="{{ $glassCard }}" aria-label="Saúde das integrações">
                <h2 class="mb-4 text-lg font-semibold text-white">Saúde das integrações</h2>

                @if (! $loadDeferred)
                    <div class="animate-pulse space-y-3" aria-hidden="true">
                        @for ($i = 0; $i < 2; $i++)
                            <div class="h-12 rounded-lg bg-white/5"></div>
                        @endfor
                    </div>
                @else
                    @php($integrations = $this->integrations)
                    @if (empty($integrations))
                        <p class="text-sm text-slate-400">Nenhuma integração configurada.</p>
                    @else
                        <ul class="space-y-3">
                            @foreach ($integrations as $integration)
                                <li class="flex items-center justify-between gap-3 rounded-lg border border-white/5 bg-white/5 p-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="inline-block h-2.5 w-2.5 rounded-full {{ $integrationDot($integration) }}" aria-hidden="true"></span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-white">{{ $integration['platform']->label() }}</p>
                                            <p class="mt-0.5 text-xs text-slate-400">
                                                @if ($integration['last_job_at'] !== null)
                                                    Última sincronização {{ $integration['last_job_at']->diffForHumans() }}
                                                @else
                                                    Sem sincronizações registradas
                                                @endif
                                                @if (($integration['failed_last_24h'] ?? 0) > 0)
                                                    · <span class="text-rose-300">{{ $integration['failed_last_24h'] }} falha(s) em 24h</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                                        'bg-emerald-500/15 text-emerald-300' => $integration['is_active'],
                                        'bg-slate-500/20 text-slate-300' => ! $integration['is_active'],
                                    ])>
                                        {{ $integration['is_active'] ? 'Ativa' : 'Inativa' }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                @endif
            </section>
        @endif

    </div>
</div>
