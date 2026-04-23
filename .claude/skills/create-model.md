---
name: create-model
description: Generate an Eloquent model. Delegates to backend-specialist (sonnet).
---

Delegate to the `backend-specialist` agent to create the Eloquent model.

Pass the user's full request. The agent will produce a complete `app/Models/{Name}.php` with UUID PK, fillable, casts with PHP Backed Enums, TenantScope if tenant-scoped, all relationships typed, and relevant query scopes.
