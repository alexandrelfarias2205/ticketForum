---
name: accessibility-check
description: Check Blade views and Livewire components for WCAG 2.1 AA accessibility violations. Delegates to ux-specialist (sonnet). Produces a findings report with exact fixes for each violation.
---

Delegate to the `ux-specialist` agent.

## Instructions for the agent

1. Identify the scope (user-specified path, or all views if none given).

2. Read each Blade/Livewire view file and check against the full accessibility checklist:

   **Labels & Forms:**
   - [ ] Every `<input>`, `<select>`, `<textarea>` has an associated `<label for="...">`
   - [ ] No placeholder-only labels (placeholder is not a substitute for label)
   - [ ] Form errors linked via `aria-describedby` to their field

   **Interactive Elements:**
   - [ ] All buttons and links have accessible text (visible or `aria-label`)
   - [ ] Icon-only buttons have `aria-label="Ação em português"`
   - [ ] Focus rings visible: `focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2`
   - [ ] No `tabindex="-1"` on focusable elements without justification

   **Dynamic Content:**
   - [ ] Toast/notification container has `aria-live="polite"`
   - [ ] Loading states announced via `aria-live` or `role="status"`
   - [ ] Modals: `role="dialog"`, `aria-modal="true"`, focus trap active

   **Color & Contrast:**
   - [ ] Color is never the sole means of conveying information
   - [ ] Status badges use text label alongside color
   - [ ] Estimated contrast ≥ 4.5:1 for body text, ≥ 3:1 for large text

   **Images & Icons:**
   - [ ] Meaningful images have descriptive `alt` text in Portuguese-BR
   - [ ] Decorative images/icons have `alt=""` or `aria-hidden="true"`

3. Output report:
   ```
   ## Accessibility Report

   ### WCAG Violations Found
   [LEVEL] File: path — Line X
   Issue: description
   Fix: exact corrected code

   ### Passed ✓
   ```

Usage:
- `/accessibility-check` — full audit
- `/accessibility-check resources/views/auth/` — auth views only
- `/accessibility-check resources/views/livewire/reports/create-report.blade.php`
