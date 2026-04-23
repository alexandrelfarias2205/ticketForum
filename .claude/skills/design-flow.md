---
name: design-flow
description: Design a complete user flow for a feature before implementation. Produces user journey map, screen inventory, component list, and Blade/Livewire structure. Delegates to design-chief → ux-specialist → frontend-specialist. Use BEFORE writing any code for a new feature.
---

Delegate to the `design-chief` agent to orchestrate the full flow design.

## Instructions for the agent

1. Clarify the feature and which role(s) it serves (root / tenant_admin / tenant_user).

2. Coordinate with `ux-specialist` to produce:
   - **Current state map** (if feature modifies existing flow): pain points highlighted
   - **Future state map**: step-by-step user journey with decision points
   - **Screen inventory**: list of every view/state needed
   - **Edge cases**: empty state, error state, loading state, permission-denied state for each screen
   - **Information hierarchy**: what's primary vs secondary on each screen

3. Coordinate with `design-system-architect` to identify:
   - Which existing Blade components can be reused
   - Which new components need to be created
   - Which design tokens apply

4. Coordinate with `frontend-specialist` to produce:
   - Livewire component structure (which components, properties, methods)
   - Route structure needed
   - View file inventory

5. Output deliverable:
   ```
   ## Flow Design: [Feature Name]
   
   ### User Journey (role: X)
   Step 1 → Step 2 → ... → Success state
   
   ### Screen Inventory
   | Screen | View file | Livewire component | New component? |
   
   ### Component Reuse
   ### New components needed
   ### Blade/Livewire structure
   ### Edge cases to handle
   ```

Usage:
- `/design-flow nova tela de relatório para tenant_user`
- `/design-flow dashboard de métricas para root`
