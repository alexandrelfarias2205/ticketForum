<div>
    {{-- Cabeçalho --}}
    <div class="mb-6">
        <h1 class="page-title">Em votação</h1>
        <p class="page-subtitle">Vote nas sugestões de melhoria publicadas pela comunidade.</p>
    </div>

    {{-- Filtros --}}
    <div class="card-compact mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
            <div class="relative min-w-48 flex-1">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input wire:model.live.debounce.300ms="search" type="search" placeholder="Buscar por título…"
                       class="input-dark pl-10" />
            </div>

            @if ($this->products->isNotEmpty())
                <select wire:model.live="filterProductId" class="input-dark sm:w-52">
                    <option value="">Todos os produtos</option>
                    @foreach ($this->products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            @endif

            <select wire:model.live="filterType" class="input-dark sm:w-44">
                <option value="">Todos os tipos</option>
                <option value="bug">Bug</option>
                <option value="improvement">Melhoria</option>
                <option value="feature_request">Nova Funcionalidade</option>
            </select>

            <select wire:model.live="sortBy" class="input-dark sm:w-44">
                <option value="votes">Mais votados</option>
                <option value="newest">Mais recentes</option>
            </select>
        </div>
    </div>

    {{-- Grid de cards --}}
    @if ($this->reports->isEmpty())
        <div class="card flex flex-col items-center justify-center py-16 text-center">
            <svg class="mb-3 h-10 w-10 text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" />
            </svg>
            <p class="text-sm font-medium text-slate-300">Nenhuma sugestão disponível para votação no momento.</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->reports as $report)
                <div
                    class="card card-hover flex flex-col justify-between"
                    x-data="{
                        voted: {{ $report->voted_by_me ? 'true' : 'false' }},
                        count: {{ $report->vote_count }},
                        loading: false,
                        async toggle() {
                            if (this.loading) return;
                            this.loading = true;
                            const prev = { voted: this.voted, count: this.count };
                            this.voted = !this.voted;
                            this.count = this.voted ? this.count + 1 : this.count - 1;
                            try {
                                const res = await fetch('{{ route('app.votes.toggle', $report) }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/json',
                                    },
                                });
                                if (!res.ok) throw new Error('Erro ao registrar voto.');
                                const data = await res.json();
                                this.voted = data.voted;
                                this.count = data.vote_count;
                            } catch (e) {
                                this.voted = prev.voted;
                                this.count = prev.count;
                            } finally {
                                this.loading = false;
                            }
                        }
                    }"
                >
                    {{-- Topo --}}
                    <div class="mb-3 flex items-start justify-between gap-2">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <x-badge :status="$report->type" />
                            @foreach ($report->labels as $label)
                                <span class="badge badge-neutral">{{ $label->name }}</span>
                            @endforeach
                        </div>
                        <span class="shrink-0 text-xs text-slate-500">{{ $report->tenant->name }}</span>
                    </div>

                    {{-- Conteúdo --}}
                    <div class="mb-4 flex-1">
                        <h2 class="mb-1 text-base font-semibold leading-snug text-white">{{ $report->title }}</h2>
                        <p class="text-sm leading-relaxed text-slate-400">
                            {{ Str::limit(strip_tags($report->description), 150) }}
                        </p>
                    </div>

                    {{-- Rodapé --}}
                    <div class="flex items-center justify-between border-t border-white/10 pt-3">
                        <div class="flex flex-col gap-1">
                            <div class="text-xs text-slate-500">
                                Por {{ $report->author->name }}
                                @if ($report->published_at)
                                    &bull; {{ $report->published_at->diffForHumans() }}
                                @endif
                            </div>
                            <span x-show="voted"
                                  class="badge badge-success"
                                  style="display: none">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                Você votou
                            </span>
                        </div>

                        <button
                            @click="!voted && toggle()"
                            :disabled="loading || voted"
                            :class="voted
                                ? 'bg-gradient-brand text-white shadow-glow-brand'
                                : 'border border-white/15 bg-white/5 text-slate-200 hover:border-brand-400/40 hover:bg-brand-500/10 hover:text-white'"
                            class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-semibold transition focus-ring disabled:opacity-60"
                            :title="voted ? 'Você já votou' : 'Votar'"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 20 20" :fill="voted ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75L10 4.5l5.5 11.25H4.5z" />
                            </svg>
                            <span x-text="count"></span>
                            <span class="sr-only">votos</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $this->reports->links() }}
        </div>
    @endif
</div>
