---
name: create-livewire
description: Scaffold a Livewire v3 component + Blade view pair. Delegates to frontend-specialist (sonnet).
---

Delegate to the `frontend-specialist` agent to create the Livewire component.

Pass the user's full request. The agent will produce:
1. `app/Livewire/{Domain}/{ComponentName}.php`
2. `resources/views/livewire/{domain}/{component-name}.blade.php`

Both files complete, with authorize-first pattern, wire:loading on buttons, and all UI text in Portuguese-BR.
