---
name: qa
description: Run full QA analysis — finds missing test coverage, writes missing tests, runs the test suite, and reports failures. Delegates to qa-specialist agent (sonnet). Use before commits or after adding new features.
---

Delegate to the `qa-specialist` agent.

## Instructions for the agent

1. Run `APP_ENV=testing ./vendor/bin/pest 2>&1` to get the current test suite status.

2. List all classes in:
   - `app/Actions/` (all subdirectories)
   - `app/Policies/`
   - `app/Jobs/`
   - `app/Livewire/` (key components)

3. List all test files in `tests/Feature/` and `tests/Unit/`.

4. Cross-reference to find gaps — any class with no corresponding test file or test coverage.

5. For each gap found: write the missing Pest test file following the conventions in your agent definition.

6. Run `APP_ENV=testing ./vendor/bin/pest 2>&1` again after writing tests.

7. Fix any test failures using the appropriate specialist agent before reporting.

8. Output the full QA report.

If the user says `/qa` with no arguments: run the full suite and full gap analysis.
If the user says `/qa app/Actions/Votes/`: focus the gap analysis on that folder only.
If the user says `/qa --fix`: also attempt to fix any failing tests automatically.
