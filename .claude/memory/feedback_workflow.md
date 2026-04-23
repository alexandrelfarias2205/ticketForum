---
name: workflow-feedback
description: How the user wants phases to be executed — autonomous, with tests before commits
type: feedback
---

Never ask permission to proceed to the next phase. Execute all phases sequentially and autonomously.

**Why:** User explicitly said "não precisa me perguntar se é pra seguir para próximas fases."

**How to apply:**
- After each phase: run tests → fix errors with agents → commit → proceed immediately to next phase
- Always run `php artisan test` before committing
- Fix any test failures using the appropriate specialist agent before moving on
- Use `git commit` after each phase passes all tests
- Never pause and ask "shall I continue?"
