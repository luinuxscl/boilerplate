# Project Context: Laravel Livewire Starter Kit

This is a production-ready Laravel 12 boilerplate for new SaaS/app projects. It provides authentication, role-based access control, API key management, AI integration, outbound webhooks, and a modular extension system — all built with Livewire 4 and Flux UI. The codebase is the starting point that gets cloned and extended per client/project.

---

## Stack

| Layer | Package | Version |
|---|---|---|
| Runtime | PHP | 8.4 |
| Framework | Laravel | 12 |
| Reactive frontend | Livewire | 4 |
| UI components | Flux UI | 2 |
| Auth backend | Laravel Fortify | 1 |
| Permissions/Roles | Spatie Laravel Permission | latest |
| Testing | Pest | 4 |
| CSS | Tailwind CSS | 4 |
| Database (default) | SQLite | — |

---

## Features

### Authentication (Fortify — headless)
- Login, registration, password reset, email verification
- Two-factor authentication (TOTP) with QR code and recovery codes
- Rate limiting: 5 attempts/min per email+IP
- Views in `resources/views/pages/auth/`, config in `config/fortify.php`

### Authorization (Spatie Permissions)
Three built-in roles:
- `user` — default, no permissions
- `admin` — all permissions except role management
- `super-admin` — bypasses all gates via `Gate::before`

Permission groups: `users`, `roles`, `api-keys`, `ai`, `webhooks`
Seeder: `database/seeders/RolesAndPermissionsSeeder.php`

### User Management
- Livewire list + edit form at `/users` and `/users/{user}/edit`
- Assign roles from the edit form
- Requires `users.view` permission

### API Keys
- ULID-based keys, stored as HMAC-SHA256 hashes with visible prefix
- Scope-based access control (`*` wildcard supported)
- Per-key configurable rate limiting and expiry (`expires_at`)
- Middleware stack: `AuthenticateApiKey → EnforceApiScopes → ApiRateLimiter`
- Available scopes: `*`, `profile.read`, `api-keys.read`, `api-keys.write`
- Management UI at `/api-keys`

### REST API (v1)
- `GET /api/v1/me` — authenticated user profile (requires `profile.read`)
- Versioned routing via `routes/api_v1.php`
- Eloquent API Resources

### AI Gateway
- `AiGateway` service with pluggable driver pattern
- Drivers: `openrouter` (OpenAI-compatible), `null` (testing)
- `PromptRegistry` — reusable templates with `{{variable}}` substitution
- `UsageTracker` — logs driver, model, token counts, cost (integer cents), duration
- Models: `AiPrompt`, `AiUsageLog`
- UI: `/ai/prompts` and `/ai/usage`

### Webhooks (outbound)
- `WebhookEndpoint` model with per-endpoint event subscriptions
- HMAC-SHA256 signed payloads (`X-Webhook-Signature` header)
- Async delivery via `DispatchWebhook` queued job
- Failure count tracking
- Management UI at `/webhooks`

### Settings (Livewire full-page components)
| Route | Description |
|---|---|
| `/settings/profile` | Name and email update |
| `/settings/security` | Password change, 2FA setup/disable, account deletion |
| `/settings/appearance` | Light/dark/system theme |

### Module System
Extend the boilerplate with self-contained local Composer packages:
```
packages/
  acme/
    example-module/
      composer.json
      src/ExampleModuleServiceProvider.php
```
Modules can register navigation items via `NavigationRegistry` and API scopes via `ScopeRegistry`.
Generate scaffold: `php artisan make:module {name}`

---

## Architecture

### Directory Structure

```
app/
  Actions/Fortify/          # CreateNewUser, ResetUserPassword
  Concerns/                 # PasswordValidationRules, ProfileValidationRules (traits)
  Console/Commands/         # MakeModuleCommand, ModuleListCommand
  Contracts/                # AiDriverContract, WebhookEventContract
  Http/Middleware/          # AuthenticateApiKey, EnforceApiScopes, ApiRateLimiter
  Jobs/                     # DispatchWebhook
  Livewire/                 # Components by domain (Ai/, ApiKeys/, Users/, Webhooks/, Actions/)
  Models/                   # User, Role, Permission, ApiKey, AiPrompt, AiUsageLog, WebhookEndpoint
  Providers/                # AppServiceProvider, FortifyServiceProvider, AiServiceProvider
  Services/
    Ai/                     # AiGateway, PromptRegistry, UsageTracker, drivers
    ApiKeys/                # ApiKeyManager, ScopeRegistry, ApiRateLimiter
    Modules/                # NavigationRegistry
    Webhooks/               # WebhookDispatcher, WebhookSigner

resources/views/
  pages/auth/               # Fortify auth views
  livewire/                 # Blade for Livewire components
  layouts/                  # app.blade.php, auth.blade.php
  components/               # Reusable Blade components (x-table, etc.)

routes/
  web.php                   # Main web routes
  settings.php              # Settings routes
  api.php                   # API entry with versioning
  api_v1.php                # v1 endpoints

packages/
  acme/example-module/      # Reference module implementation
```

### Models

All domain models use ULIDs as primary keys (not auto-increment integers).

| Model | Description |
|---|---|
| `User` | Authenticatable, has 2FA, roles, `initials()` helper |
| `Role` | Extends Spatie Role, ULID PK |
| `Permission` | Extends Spatie Permission, ULID PK |
| `ApiKey` | API credentials with scopes, rate limit, expiry |
| `AiPrompt` | Template with `render(array $data)` for `{{variable}}` substitution |
| `AiUsageLog` | AI request log: driver, model, tokens, cost (integer cents), duration |
| `WebhookEndpoint` | Outbound endpoint with event filter and failure tracking |

### Layouts

| Layout | Usage |
|---|---|
| `layouts/app.blade.php` | Authenticated pages with Flux sidebar |
| `layouts/auth.blade.php` | Unauthenticated pages (login, register, etc.) |

### Key Design Decisions

- **ULID PKs** — sortable, non-sequential, safe to expose in API URLs
- **SQLite by default** — zero-config local dev; tests use `:memory:`; production uses PostgreSQL/MySQL via `.env`
- **Fortify headless** — auth views are plain Blade, no framework coupling, features togglable in `config/fortify.php`
- **Livewire full-page components for Settings** — reactive forms without JS, logic and view co-located

---

## Code Conventions

### Naming

| Element | Convention | Example |
|---|---|---|
| Variables & methods | camelCase, descriptive | `isRegisteredForDiscounts` |
| Boolean methods | `is`/`has`/`can` prefix | `isActive()`, `hasExpired()` |
| Classes | PascalCase | `OrderController` |
| Enum keys | TitleCase | `Monthly`, `FavoritePerson` |
| Named routes | dot notation | `users.index`, `api.v1.profile` |
| Permissions | `module.action` | `users.view`, `api-keys.create` |
| API scopes | `module:permission` | `orders:read`, `users:write` |
| Webhook events | `module.event` | `order.created`, `user.deleted` |

### PHP

- Constructor property promotion always
- Always declare return types
- Always use curly braces, even for single-line bodies

### Laravel

- Form Requests for validation — never inline in controllers
- `Model::query()` over `DB::`
- Eager loading to prevent N+1
- Named routes (`route()` helper), never hardcoded URLs
- `config('key')` not `env()` outside config files
- Validation traits in `app/Concerns/` — reuse `PasswordValidationRules`, `ProfileValidationRules`

---

## Testing

- Framework: Pest 4
- 128 tests, 237 assertions — all pass on fresh clone
- Most tests are **feature tests** (more reliable than unit tests)
- Run all: `php artisan test --compact`
- Run single: `php artisan test --compact --filter=UserManagementTest`
- Always seed roles in permission tests: `$this->seed(RolesAndPermissionsSeeder::class)`
- Factory states: `User::factory()->admin()->create()`

Coverage: auth, settings, users, roles, API keys, AI gateway, webhooks, module commands.

---

## Development Commands

```bash
composer run dev               # PHP server + queue + Pail logs + Vite HMR
php artisan test --compact     # Run all tests
vendor/bin/pint --dirty        # Format changed PHP files
php artisan migrate:fresh --seed  # Reset database with seed data
npm run build                  # Build frontend assets
```

Default seeded user: `test@example.com` / `password`
