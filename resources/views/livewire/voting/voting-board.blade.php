<div>
    {{-- Cabeçalho --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Quadro de Votação</h1>
        <p class="mt-1 text-sm text-gray-500">Vote nas sugestões de melhoria publicadas pela comunidade.</p>
    </div>

    {{-- Filtros --}}
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
        <input
            wire:model.live.debounce.300ms="search"
            type="search"
            placeholder="Buscar por título…"
            class="flex-1 min-w-48 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
        />

        @if ($this->products->isNotEmpty())
        <select
            wire:model.live="filterProductId"
            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
        >
            <option value="">Todos os produtos</option>
            @foreach ($this->products as $product)
                <option value="{{ $product->id }}">{{ $product->name }}</option>
            @endforeach
        </select>
        @endif

        <select
            wire:model.live="filterType"
            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
        >
            <option value="">Todos os tipos</option>
            <option value="bug">Bug</option>
            <option value="improvement">Melhoria</option>
            <option value="feature_request">Nova Funcionalidade</option>
        </select>

        <select
            wire:model.live="sortBy"
            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
        >
            <option value="votes">Mais votados</option>
            <option value="newest">Mais recentes</option>
        </select>
    </div>

    {{-- Grid de cards --}}
    @if ($this->reports->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 bg-white py-16 text-center">
            <svg class="mb-3 h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" />
            </svg>
            <p class="text-sm font-medium text-gray-500">Nenhuma sugestão disponível para votação no momento.</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->reports as $report)
                <div
                    class="flex flex-col justify-between rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md"
                    x-data="{
                        voted: {{ $report->voted_by_me ? 'true' : 'false' }},
                        count: {{ $report->vote_count }},
                        loading: false,
                        async toggle() {
                            if (this.loading) return;
                            this.loading = true;
                            const prev = { voted: this.voted, count: this.count };
                            // Optimistic update
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
                                // Rollback on error
                                this.voted = prev.voted;
                                this.count = prev.count;
                            } finally {
                                this.loading = false;
                            }
                        }
                    }"
                >
                    {{-- Topo: badges + empresa --}}
                    <div class="mb-3 flex items-start justify-between gap-2">
                        <div class="flex flex-wrap items-center gap-1.5">
                            {{-- Type badge --}}
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                @if($report->type->value === 'bug') bg-red-100 text-red-700
                                @elseif($report->type->value === 'improvement') bg-yellow-100 text-yellow-700
                                @else bg-blue-100 text-blue-700
                                @endif
                            ">
                                {{ $report->type->label() }}
                            </span>
                            {{-- Labels --}}
                            @foreach ($report->labels as $label)
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600">
                                    {{ $label->name }}
                                </span>
                            @endforeach
                        </div>
                        {{-- Tenant --}}
                        <span class="shrink-0 text-xs text-gray-400">{{ $report->tenant->name }}</span>
                    </div>

                    {{-- Título e descrição --}}
                    <div class="mb-4 flex-1">
                        <h2 class="mb-1 text-base font-semibold text-gray-900 leading-snug">{{ $report->title }}</h2>
                        <p class="text-sm text-gray-500 leading-relaxed">
                            {{ Str::limit(strip_tags($report->description), 150) }}
                        </p>
                    </div>

                    {{-- Rodapé: autor + botão de voto --}}
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                        <div class="flex flex-col gap-1">
                            <div class="text-xs text-gray-400">
                                Por {{ $report->author->name }}
                                @if ($report->published_at)
                                    &bull; {{ $report->published_at->diffForHumans() }}
                                @endif
                            </div>
                            {{-- Já votei badge --}}
                            <span
                                x-show="voted"
                                class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700"
                            >
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                Já votei
                            </span>
                        </div>

                        {{-- Vote button --}}
                        <button
                            @click="!voted && toggle()"
                            :disabled="loading || voted"
                            :class="voted
                                ? 'bg-indigo-600 text-white cursor-not-allowed opacity-70'
                                : 'bg-white text-gray-600 border border-gray-300 hover:border-indigo-400 hover:text-indigo-600'"
                            class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 disabled:opacity-60"
                            :title="voted ? 'Você já votou' : 'Votar'"
                        >
                            {{-- Triangle up --}}
                            <svg class="h-4 w-4" viewBox="0 0 20 20" :fill="voted ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75L10 4.5l5.5 11.25H4.5z" />
                            </svg>
                            <span x-text="count"></span>
                            <span class="sr-only">votos</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Paginação --}}
        <div class="mt-6">
            {{ $this->reports->links() }}
        </div>
    @endif
</div>
