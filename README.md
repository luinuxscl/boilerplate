# Laravel Livewire Starter Kit

A production-ready Laravel 12 boilerplate with Livewire 4, Flux UI, Fortify authentication, role-based access control, API key management, AI integration, and a modular architecture.

## Stack

| Package | Version |
|---|---|
| PHP | 8.4 |
| Laravel | 12 |
| Livewire | 4 |
| Flux UI | 2 |
| Fortify | 1 |
| Pest | 4 |
| Tailwind CSS | 4 |

## Requirements

- PHP 8.2+
- Composer
- Node.js & NPM

## Installation

```bash
git clone <repo-url> my-project
cd my-project

composer install
npm install

cp .env.example .env
php artisan key:generate

touch database/database.sqlite
php artisan migrate --seed

npm run build
php artisan serve
```

Default test user after seeding: `test@example.com` / `password`

## Development

```bash
# Start full dev environment (PHP server + queue + Pail logs + Vite HMR)
composer run dev

# Run all tests
php artisan test --compact

# Format changed PHP files
vendor/bin/pint --dirty
```

## Features

### Authentication (Fortify)

Full headless authentication via Laravel Fortify:

- Login / Registration
- Password reset
- Email verification
- Two-factor authentication (TOTP)
- Rate limiting (5 attempts/min per email+IP)

Auth views: `resources/views/pages/auth/`
Fortify config: `config/fortify.php`
Actions: `app/Actions/Fortify/`

### Authorization (Spatie Permissions)

Role-based access control with three built-in roles:

| Role | Description |
|---|---|
| `user` | Default role, no permissions by default |
| `admin` | All permissions except role management |
| `super-admin` | Bypasses all gates |

Permissions are grouped by domain: `users`, `roles`, `api-keys`, `ai`, `webhooks`.

See `database/seeders/RolesAndPermissionsSeeder.php` for the full list.

### User Management

Full CRUD for users via Livewire components:
- `GET /users` — list with search/filter
- `GET /users/{user}/edit` — edit form (name, email, password, roles)

Requires `users.view` permission.

### API Keys

Token-based API authentication with scope and rate limiting:

- ULID-based keys stored as HMAC-SHA256 hashes
- Scope-based access control (wildcard `*` supported)
- Per-key configurable rate limits
- Keys managed at `GET /api-keys`

**Available scopes:**

| Scope | Description |
|---|---|
| `*` | Full access |
| `profile.read` | Read own profile |
| `api-keys.read` | Read API keys |
| `api-keys.write` | Create/revoke API keys |

**API endpoint:**

```
GET /api/v1/me
Authorization: Bearer <api-key>
```

### AI Integration

Driver-based AI service with usage tracking:

- `AiGateway` service with pluggable drivers
- Built-in drivers: `openrouter` (OpenAI-compatible), `null` (testing)
- `PromptRegistry` for reusable template management (`{{variable}}` syntax)
- `UsageTracker` logs tokens, cost (USD), and duration per request
- Prompt manager at `GET /ai/prompts`
- Usage dashboard at `GET /ai/usage`

Configure driver in `config/ai.php`. For OpenRouter, set `OPENROUTER_API_KEY` in `.env`.

### Webhooks

Outbound webhooks with event filtering and HMAC signature verification:

- Per-endpoint event subscriptions (array of event names)
- HMAC-SHA256 signed payloads (`X-Webhook-Signature` header)
- Async delivery via `DispatchWebhook` queued job
- Failure count tracking on `WebhookEndpoint`
- Managed at `GET /webhooks`

Verify incoming webhooks using `WebhookSigner::verify($payload, $signature, $secret)`.

### Settings

Three settings pages as Livewire full-page components:

| Route | Description |
|---|---|
| `/settings/profile` | Name and email |
| `/settings/security` | Password change and 2FA management |
| `/settings/appearance` | Light/dark/system theme |

### Module System

Extend the boilerplate with self-contained modules as local Composer packages.

```
packages/
  acme/
    example-module/
      composer.json
      src/
        ExampleModuleServiceProvider.php
```

A module service provider can register:
- Navigation items via `NavigationRegistry`
- API scopes via `ScopeRegistry`
- Any standard Laravel service bindings

See `packages/acme/example-module/` for a working reference.

Generate a new module scaffold:

```bash
php artisan make:module {name}
```

## Architecture

### Directory Structure

```
app/
  Actions/Fortify/        # CreateNewUser, ResetUserPassword
  Console/Commands/       # MakeModuleCommand, ModuleListCommand
  Concerns/               # PasswordValidationRules, ProfileValidationRules
  Contracts/              # AiDriverContract, WebhookEventContract, etc.
  Http/Middleware/        # AuthenticateApiKey, EnforceApiScopes, ApiRateLimiter
  Jobs/                   # DispatchWebhook
  Livewire/               # Livewire components (Actions, Ai, ApiKeys, Users, Webhooks)
  Models/                 # User, Role, Permission, ApiKey, AiPrompt, AiUsageLog, WebhookEndpoint
  Providers/              # AppServiceProvider, FortifyServiceProvider, AiServiceProvider, ModuleServiceProvider
  Services/
    Ai/                   # AiGateway, PromptRegistry, UsageTracker, drivers
    ApiKeys/              # ApiKeyManager, ScopeRegistry, ApiRateLimiter
    Modules/              # NavigationRegistry
    Webhooks/             # WebhookDispatcher, WebhookSigner
routes/
  web.php                 # Web routes (dashboard, users, api-keys, ai, webhooks)
  settings.php            # Settings routes
  api.php / api_v1.php    # REST API routes
```

### Models

All domain models use ULIDs as primary keys.

| Model | Description |
|---|---|
| `User` | Authenticatable with 2FA, roles, initials() |
| `Role` | Extends Spatie Role, ULID PK |
| `Permission` | Extends Spatie Permission, ULID PK |
| `ApiKey` | API credentials with scopes and rate limits |
| `AiPrompt` | Prompt template with `render(array $data)` |
| `AiUsageLog` | AI request log with token/cost tracking |
| `WebhookEndpoint` | Outbound webhook with event filter |

### Layouts

| Layout | Usage |
|---|---|
| `layouts/app.blade.php` | Authenticated pages with sidebar |
| `layouts/auth.blade.php` | Unauthenticated pages (login, register, etc.) |

### Validation Traits

Reusable validation logic in `app/Concerns/`:
- `PasswordValidationRules` — `passwordRules()`, `currentPasswordRules()`
- `ProfileValidationRules` — `profileRules()`, `nameRules()`, `emailRules()`

Use these traits in Livewire components and Form Requests instead of duplicating rules.

## Testing

Tests are written with Pest 4. All 128 tests pass out of the box.

```bash
# All tests
php artisan test --compact

# Single file
php artisan test --compact tests/Feature/Auth/AuthenticationTest.php

# Filter by name
php artisan test --compact --filter=UserManagementTest
```

Test coverage spans:
- Authentication (login, register, password reset, email verification, 2FA)
- Settings (profile update, security/2FA)
- User management (CRUD, permission gating)
- Role & permission seeding
- API key authentication, scope enforcement, rate limiting
- AI gateway, prompt registry, usage tracking
- Webhook dispatch, signature verification
- Module scaffolding command

## Database

SQLite by default. Override via `.env` for production (PostgreSQL/MySQL recommended).

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=myapp
DB_USERNAME=myapp
DB_PASSWORD=secret
```

```bash
# Fresh install with seed data
php artisan migrate:fresh --seed
```

## Contributing

1. Follow existing conventions — check sibling files before creating new ones.
2. Every change must have a test.
3. Run `vendor/bin/pint --dirty` before committing.
4. Do not introduce new top-level directories without discussion.
