# ticketForum — Project Rules

## Agent Routing (MANDATORY RULE)

**Every construction, improvement, or repair task MUST be executed by the appropriate specialist agent — never inline by the orchestrator.**

### Core Engineering

| Task | Agent | Model |
|------|-------|-------|
| Any PHP/Laravel backend code | `backend-specialist` | sonnet |
| Migrations, schema, indexes, factories | `database-specialist` | haiku |
| Livewire components, Blade views, layouts | `frontend-specialist` | sonnet |
| Auth, authorization, multi-tenant isolation | `security-specialist` | opus |
| OWASP, secure coding, XSS/SQLi/CSRF patterns | `appsec-specialist` | opus |
| Dependency CVEs, composer audit, vuln mgmt | `vulnerability-analyst` | sonnet |
| Jira/GitHub Jobs, webhooks, external APIs | `integration-specialist` | sonnet |
| Alpine.js components, Vite, app.js | `javascript-specialist` | haiku |

### Design & UX

| Task | Agent | Model |
|------|-------|-------|
| Design orchestration — route to right design agent | `design-chief` | sonnet |
| UX decisions, user flows, accessibility, heuristics | `ux-specialist` | sonnet |
| Design system tokens, Blade component library | `design-system-architect` | sonnet |
| UI code production — Tailwind + Alpine.js + Blade | `ui-engineer` | haiku |

### Quality & Review

| Task | Agent | Model |
|------|-------|-------|
| Code review — DRY, rules, security, language | `code-reviewer` | sonnet |
| QA — test coverage gaps, missing tests, suite | `qa-specialist` | sonnet |

### Claude Code & Infrastructure

| Task | Agent | Model |
|------|-------|-------|
| Hooks — pre/post tool-use, stop events | `hooks-architect` | haiku |
| Settings, permissions, CLAUDE.md management | `config-engineer` | haiku |
| New skills and slash commands creation | `skill-craftsman` | sonnet |
| Multi-agent orchestration, parallel execution | `swarm-orchestrator` | opus |

### Strategy & Advisory

| Task | Agent | Model |
|------|-------|-------|
| SaaS metrics, NRR, churn, tenant health | `customer-success-advisor` | sonnet |
| Analytics KPIs, measurement, dashboards | `analytics-advisor` | sonnet |

---

### Model Selection Rule

**haiku** — formulaic, template-driven: migrations, alpine components, UI code, hooks config.
**sonnet** — standard implementation: Livewire, services, actions, integrations, design, review.
**opus** — deep reasoning only: security (multi-tenant isolation, OWASP), swarm orchestration.

The orchestrator's job is to route, coordinate, and verify — not to write code directly.

---

## Project Overview

Multi-tenant SaaS platform for bug reporting, improvement suggestions, and feature voting.
Built with Laravel 12, PHP 8.3, PostgreSQL, Blade + Livewire v3, Tailwind CSS, Alpine.js.

---

## Language Rules (STRICT)

- **Backend** (PHP classes, methods, variables, database columns, routes, config keys, comments in code): **English only**
- **Frontend** (Blade views, Livewire component templates, UI labels, button text, alerts, flash messages, validation error messages, tooltips, placeholders, modal text): **Portuguese-BR only**
- **Migrations**: column names in English, comments in English
- **Git commits**: English
- **Exception messages** (thrown in PHP): English
- **User-facing flash/toast/validation**: Portuguese-BR

---

## DRY — Don't Repeat Yourself (DEFAULT RULE)

- Every piece of logic must exist in exactly one place
- Extract repeated Blade markup into components (`resources/views/components/`)
- Extract repeated Livewire logic into traits (`app/Traits/`)
- Extract repeated business logic into Services (`app/Services/`)
- Extract repeated queries into Repositories or Scopes (`app/Repositories/`, model scopes)
- Extract repeated validation rules into Form Requests (`app/Http/Requests/`)
- Extract repeated authorization logic into Policies (`app/Policies/`) and Gates
- If you write the same code twice, stop and refactor before continuing

---

## Directory Rules (STRICT)

- **Nothing goes inside `public/`** — no PHP files, no compiled assets directly, no uploads
- Assets are compiled to `public/build/` by Vite (automated, never manually placed)
- File uploads go to `storage/app/private/` (local) or S3/R2 (production) — never `public/`
- Serve files via signed URLs or dedicated download routes, never direct public paths
- All application code lives in `app/`, `resources/`, `routes/`, `config/`, `database/`

---

## Architecture & Organization

### Directory Structure
```
app/
├── Actions/          # Single-purpose action classes (one public method: handle())
├── Contracts/        # Interfaces for services and repositories
├── Events/           # Domain events
├── Exceptions/       # Custom exception classes
├── Http/
│   ├── Controllers/  # Thin controllers — delegate to Actions/Services
│   ├── Middleware/   # HTTP middleware
│   └── Requests/     # Form Request validation classes
├── Jobs/             # Queue jobs (integrations, notifications)
├── Listeners/        # Event listeners
├── Livewire/         # Livewire components (organized by domain)
│   ├── Admin/
│   ├── Reports/
│   ├── Voting/
│   └── Tenant/
├── Models/           # Eloquent models
├── Notifications/    # Laravel notifications
├── Observers/        # Model observers
├── Policies/         # Authorization policies
├── Providers/        # Service providers
├── Repositories/     # Database access abstraction
├── Services/         # Business logic services
├── Traits/           # Reusable PHP traits
└── ValueObjects/     # Immutable value objects (e.g., Money, Email)

resources/
├── views/
│   ├── components/   # Blade components (shared UI)
│   ├── layouts/      # Layout templates
│   ├── livewire/     # Livewire component views (mirrors app/Livewire/)
│   └── pages/        # Static or non-Livewire pages
```

### Layer Responsibilities

| Layer | Responsibility | Rule |
|-------|---------------|------|
| Controller | Receive HTTP request, delegate, return response | Max 10 lines per method |
| Action | Single business operation | One public `handle()` method |
| Service | Orchestrate multiple actions/repos | No direct DB queries |
| Repository | All DB queries for one model | No business logic |
| Model | Relationships, scopes, casts, accessors | No HTTP/business logic |
| Livewire | UI state + user interaction | Delegate to Actions/Services |
| Job | Async work (integrations, emails) | Idempotent, retriable |

---

## Code Quality Rules

### PHP / Laravel

- PHP 8.3 features: typed properties, enums, readonly classes, match expressions, named arguments, constructor promotion
- **Strict types**: every PHP file starts with `<?php declare(strict_types=1);`
- **Return types**: all methods must have explicit return types (including `void`)
- **Type hints**: all parameters and properties must be typed — no untyped variables
- No raw SQL — use Eloquent or Query Builder. Raw SQL only for complex CTEs/window functions, always with parameter binding
- No `DB::statement()` in application code — only in migrations
- Use Laravel Enums (`app/Enums/`) for all fixed value sets (status, roles, platforms)
- Use Form Requests for all validation — never validate in controllers or Livewire directly
- Use Policies for all authorization — never inline `if ($user->role === 'root')`
- Models must define `$fillable` or use `$guarded = []` explicitly — never leave unset
- Always use `->sole()` or explicit checks instead of assuming a query returns one result
- Eager load relationships — never N+1 queries (use `with()`, `withCount()`)

### Livewire v3

- Components in `app/Livewire/{Domain}/ComponentName.php`
- Views in `resources/views/livewire/{domain}/component-name.blade.php`
- Use `#[Validate]` attribute for inline rules only when not worth a Form Request
- Use `#[Computed]` for derived values instead of redundant properties
- Dispatch browser events for toasts/alerts, handled by Alpine.js
- Never put business logic directly in Livewire — delegate to Actions or Services
- Use `$this->authorize()` at the start of every action method

### Blade

- All UI text in Portuguese-BR
- Use Blade components (`<x-component>`) over `@include` for reusable pieces
- Never use PHP logic in Blade beyond simple conditionals — move to ViewComposers or component classes if complex
- Use `@csrf`, `@method` in all forms
- Escape output with `{{ }}` always — use `{!! !!}` only for explicitly trusted, sanitized HTML

---

## Security Rules

- **Multi-tenancy isolation**: every query that touches tenant-scoped data MUST include `->where('tenant_id', auth()->user()->tenant_id)` or use a global scope
- Use `TenantScope` global scope on all tenant-scoped models — never rely on developers remembering to add the where clause
- File uploads: validate MIME type server-side (not just extension), store outside public/, generate UUIDs for filenames, strip EXIF metadata from images
- Credentials (Jira/GitHub API keys): always encrypted at rest using `encrypt()`/`decrypt()` — never store plain text in DB
- Signed URLs for all file downloads (S3 or local via `Storage::temporaryUrl()`)
- CSRF protection on all forms (Laravel default, never disable)
- Rate limiting on auth routes and public-facing endpoints (`RateLimiter::for()`)
- Use `$request->safe()` in controllers after Form Request validation
- Authorize before fetching — run `$this->authorize()` before any DB query
- Never trust user-supplied IDs without ownership check
- Log security events (failed logins, permission denials) via Laravel's logging channels
- Run `php artisan route:list` to audit — no routes should be unprotected unintentionally

---

## Database Rules

### Database Names
- **Production DB**: `ticketForum` — `.env`: `DB_DATABASE=ticketForum`
- **Test DB**: `ticketForum_test` — `.env.testing`: `DB_DATABASE=ticketForum_test`
- Both use `DB_CONNECTION=pgsql`
- Never use the default `DB_DATABASE=laravel`

- PostgreSQL only — do not use MySQL-specific syntax
- All tables have: `id` (UUID), `created_at`, `updated_at`
- Soft deletes (`deleted_at`) on: users, reports, tenants
- Tenant-scoped tables must have `tenant_id UUID NOT NULL` with foreign key to `tenants.id`
- All foreign keys must have explicit `onDelete()` behavior defined in migrations
- Indexes on: all `tenant_id` columns, all foreign keys, all `status` enum columns, `(tenant_id, status)` composite indexes
- Use PostgreSQL enums via Laravel string columns + PHP Enums + check constraints (not DB-level enums, for migration flexibility)
- Never store sensitive data unencrypted: passwords (Bcrypt via Laravel), credentials (encrypt()), tokens (hashed)
- Migrations are irreversible in production — always write `down()` for local dev only

---

## Testing Rules

- Feature tests for all HTTP endpoints and Livewire components
- Unit tests for all Services, Actions, and complex model methods
- Use factories for all test data — never hardcode IDs or emails in tests
- Tenant isolation must be tested: assert a tenant cannot access another tenant's data
- Queue jobs must be tested with `Queue::fake()`
- File uploads must be tested with `Storage::fake()`
- Minimum coverage targets: Services 90%, Actions 90%, Controllers 80%, Livewire 80%

---

## Multi-Tenant Rules

- Three roles: `root` (platform owner), `tenant_admin` (company admin), `tenant_user` (end user)
- `root` users have `tenant_id = null`
- Every Eloquent model scoped to a tenant must use `TenantScope` global scope
- Middleware `EnsureTenantAccess` applied to all tenant routes
- Root users bypass tenant scopes via `withoutGlobalScope(TenantScope::class)`
- Tenant resolution: via authenticated user's `tenant_id`, not subdomain or URL (simplicity)

---

## Integration Rules (Jira / GitHub)

- All external API calls happen inside Queue Jobs — never in the HTTP request lifecycle
- Jobs are idempotent: check if issue was already created before calling external API
- Max 3 retry attempts with exponential backoff
- Store raw API response in `integration_jobs.response_payload` for debugging
- Credentials decrypted inside the Job, never passed as plain text via queue payload
- Use HTTP client (`Http::`) with explicit timeouts — never default (no timeout)

---

## Commit & PR Rules

- Commits in English, imperative mood: `Add TenantScope to Report model`
- One logical change per commit
- PR must reference what phase/feature it implements
- No commented-out code in PRs
- No `dd()`, `dump()`, `var_dump()`, `print_r()` in committed code
- No `TODO` comments — open a GitHub issue instead
