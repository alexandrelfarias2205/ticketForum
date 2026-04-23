---
name: design-system
description: Create, update or document the ticketForum design system — tokens, Blade components, patterns, and usage guidelines. Delegates to design-system-architect (sonnet). Use when adding new design tokens, creating shared components, or documenting the component library.
---

Delegate to the `design-system-architect` agent.

## Instructions for the agent

Pass the user's full request. The agent will:

**For new tokens:**
- Add to `tailwind.config.js` (extend section) and/or `resources/css/app.css` (CSS custom properties)
- Document usage in the design system reference

**For new Blade components:**
- Create in `resources/views/components/{name}.blade.php`
- Follow `@props` pattern with typed defaults
- Include all states: default, hover, focus, disabled, loading, error
- All text in Portuguese-BR

**For documentation:**
- Produce a component usage guide with Blade examples

**Component categories to maintain:**
- `buttons` — primary, secondary, danger, ghost, icon-only
- `forms` — input, textarea, select, checkbox, radio, label, error
- `feedback` — alert, toast, badge, spinner, skeleton
- `layout` — page-header, card, divider, empty-state
- `data` — table, pagination, filter-bar
- `navigation` — sidebar-link, breadcrumb, tab

Usage:
- `/design-system novo componente badge de prioridade`
- `/design-system documentar todos os componentes existentes`
- `/design-system adicionar tokens de cor para tema escuro`
