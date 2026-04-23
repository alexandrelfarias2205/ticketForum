---
name: create-migration
description: Generate a PostgreSQL migration. Delegates to database-specialist (haiku).
---

Delegate to the `database-specialist` agent to create the migration.

Pass the user's full request. The agent will produce a complete migration file with UUID PK, tenant_id if needed, all indexes, FK constraints with explicit onDelete, and a working down() method.
