---
name: ui-build
description: Build UI — translate a design spec, wireframe description, or design-flow output into production Blade + Tailwind + Alpine.js code. Delegates to ui-engineer (haiku). Use AFTER design-flow has defined the structure.
---

Delegate to the `ui-engineer` agent.

## Instructions for the agent

Pass the user's full request. The agent will produce:

1. Complete Blade markup (for static/layout elements)
2. Alpine.js `x-data` for client-side interactions
3. Tailwind CSS classes following the ticketForum design tokens
4. All required states: default, hover, focus, disabled, loading, empty, error

**Rules the agent must follow:**
- All user-visible text in Portuguese-BR
- No PHP logic in Blade — only `{{ }}`, `@if`, `@foreach`, `@component`
- Use existing Blade components (`<x-{name}>`) before creating inline markup
- Alpine.js for interactions — no vanilla JS `document.querySelector`
- `wire:loading` on all Livewire-triggered buttons
- `aria-*` attributes on all interactive and dynamic elements
- Focus rings on all focusable elements

Usage:
- `/ui-build tela de login com identidade visual do ticketForum`
- `/ui-build card de votação com botão de voto otimista`
- `/ui-build tabela responsiva de relatórios com filtros`
