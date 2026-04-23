---
name: create-action
description: Scaffold a single-purpose Action class. Delegates to backend-specialist (sonnet).
---

Delegate to the `backend-specialist` agent to create the Action class.

Pass the user's full request. The agent will produce a complete `app/Actions/{Domain}/{Name}Action.php` with a single typed `handle()` method, strict_types, and DB transaction if needed.
