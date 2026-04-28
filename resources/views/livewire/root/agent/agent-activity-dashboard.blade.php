<div wire:poll.30000ms="$refresh">
    {{-- Cabeçalho --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Atividade dos Agentes</h1>
            <p class="mt-1 text-sm text-white/60">Relatórios em processamento automático pelos agentes de IA.</p>
        </div>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-500/20 px-3 py-1 text-xs font-medium text-indigo-300 border border-indigo-500/30">
            <span class="h-1.5 w-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
            Atualização automática a cada 30s
        </span>
    </div>

    @if ($this->agentReports->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-2xl border border-white/10 bg-white/5 backdrop-blur-sm py-16 text-center">
            <svg class="mb-3 h-10 w-10 text-white/20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.169.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 01-6.23-.693L4.2 15.3m15.6 0l-1.897 6.623A1.875 1.875 0 0116.11 23H7.89a1.875 1.875 0 01-1.793-1.077L4.2 15.3" />
            </svg>
            <p class="text-sm font-medium text-white/40">Nenhum relatório com atividade de agente no momento.</p>
        </div>
    @else
        <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5 backdrop-blur-sm shadow-xl">
            <table class="min-w-full divide-y divide-white/10 text-sm">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-white/50">Produto</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-white/50">Título do Relatório</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-white/50">Branch do Agente</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-white/50">Merge Request</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-white/50">Última Ação</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-white/50">Status</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wide text-white/50">Logs</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach ($this->agentReports as $report)
                    <tr class="hover:bg-white/5 transition-colors">
                        {{-- Produto --}}
                        <td class="px-5 py-4 text-white/70">
                            @if ($report->product)
                                <span class="inline-flex items-center rounded-lg bg-indigo-500/10 border border-indigo-500/20 px-2.5 py-1 text-xs font-medium text-indigo-300">
                                    {{ $report->product->name }}
                                </span>
                            @else
                                <span class="text-white/30 text-xs">—</span>
                            @endif
                        </td>

                        {{-- Título --}}
                        <td class="px-5 py-4 max-w-xs">
                            <p class="font-medium text-white truncate" title="{{ $report->title }}">
                                {{ Str::limit($report->title, 60) }}
                            </p>
                        </td>

                        {{-- Branch --}}
                        <td class="px-5 py-4">
                            <code class="rounded bg-white/10 px-2 py-0.5 text-xs font-mono text-emerald-300">
                                {{ $report->agent_branch }}
                            </code>
                        </td>

                        {{-- Merge Request URL --}}
                        <td class="px-5 py-4">
                            @if ($report->merge_request_url)
                                <a
                                    href="{{ $report->merge_request_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-purple-500/10 border border-purple-500/20 px-2.5 py-1 text-xs font-medium text-purple-300 hover:bg-purple-500/20 transition-colors"
                                >
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                    </svg>
                                    Ver MR
                                </a>
                            @else
                                <span class="text-white/30 text-xs">Pendente</span>
                            @endif
                        </td>

                        {{-- Última ação do agente --}}
                        <td class="px-5 py-4 max-w-xs">
                            @if ($report->agentLogs->isNotEmpty())
                                @php $lastLog = $report->agentLogs->first(); @endphp
                                <div>
                                    <p class="text-white/80 text-xs truncate" title="{{ $lastLog->action }}">
                                        {{ Str::limit($lastLog->action, 50) }}
                                    </p>
                                    <p class="text-white/30 text-xs mt-0.5">
                                        {{ $lastLog->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            @else
                                <span class="text-white/30 text-xs">Sem registros</span>
                            @endif
                        </td>

                        {{-- Status badge --}}
                        <td class="px-5 py-4">
                            @php
                                $statusConfig = match($report->status->value) {
                                    'pending_review'       => ['label' => 'Analisando risco',      'class' => 'bg-yellow-500/15 text-yellow-300 border-yellow-500/30'],
                                    'approved'             => ['label' => 'Construindo fix',        'class' => 'bg-blue-500/15 text-blue-300 border-blue-500/30'],
                                    'published_for_voting' => ['label' => 'Aguardando pipeline',   'class' => 'bg-orange-500/15 text-orange-300 border-orange-500/30'],
                                    'in_progress'          => ['label' => 'MR aberto',              'class' => 'bg-purple-500/15 text-purple-300 border-purple-500/30'],
                                    'done'                 => ['label' => 'Code Review',            'class' => 'bg-green-500/15 text-green-300 border-green-500/30'],
                                    default                => ['label' => $report->status->label(), 'class' => 'bg-white/10 text-white/60 border-white/20'],
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium {{ $statusConfig['class'] }}">
                                {{ $statusConfig['label'] }}
                            </span>
                        </td>

                        {{-- Contagem de logs --}}
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center justify-center h-6 min-w-6 rounded-full bg-white/10 text-xs font-semibold text-white/60">
                                {{ $report->agent_logs_count }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginação --}}
        <div class="mt-6">
            {{ $this->agentReports->links() }}
        </div>
    @endif
</div>
