---
name: design-audit
description: Audit existing Blade views and Livewire components against the ticketForum design system and UX heuristics. Identifies inconsistencies, accessibility violations, missing states, and DRY violations in UI code. Delegates to design-chief → ux-specialist + design-system-architect.
---

Delegate to the `design-chief` agent to orchestrate a full design audit.

## Instructions for the agent

1. Identify the scope:
   - If the user specified a path: audit only that folder/file
   - If no path given: audit all views in `resources/views/` and `resources/views/livewire/`

2. Coordinate with `ux-specialist` to evaluate:
   - Nielsen's 10 heuristics violations
   - Missing states (empty, loading, error, success) per view
   - Accessibility violations (missing labels, aria, focus rings, contrast)
   - Navigation and information architecture consistency across the three roles

3. Coordinate with `design-system-architect` to evaluate:
   - Token usage consistency (colors, spacing, typography)
   - Repeated patterns that should be extracted to Blade components
   - Badge/status components using enum methods vs magic strings
   - Missing `@props` declarations in anonymous components

4. Output a structured report:
   ```
   ## Design Audit Report
   ### Critical (blocks usability or accessibility)
   ### High (design system violations, inconsistency)
   ### Medium (missing states, DRY violations)
   ### Low (style improvements)
   
   ### Recommended new Blade components
   ### Accessibility fixes required
   ```

Usage:
- `/design-audit` — full audit of all views
- `/design-audit resources/views/livewire/reports/` — audit specific folder
- `/design-audit resources/views/auth/login.blade.php` — audit single file
