# Changelog

All notable changes to this project will be documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0] - 2026-03-17

Initial release of the Laravel Livewire Starter Kit.

### Added

#### Authentication
- Login, registration, password reset, and email verification via Laravel Fortify
- Two-factor authentication (TOTP) with QR code setup and recovery codes
- Rate limiting: 5 attempts/min per email+IP for login and 2FA challenge
- Secure password rules enforced via `PasswordValidationRules` trait

#### Authorization
- Role-based access control via Spatie Laravel Permission
- Three built-in roles: `user`, `admin`, `super-admin`
- Permission groups: `users`, `roles`, `api-keys`, `ai`, `webhooks`
- `Gate::before` bypass for `super-admin` role
- Permission-gated routes via middleware

#### User Management
- Livewire user list (`/users`) and edit form (`/users/{user}/edit`)
- Assign roles to users from the edit form
- Requires `users.view` permission

#### API Keys
- ULID-based API keys stored as HMAC-SHA256 hashes with visible prefix
- Scope-based access control with wildcard (`*`) support
- Per-key rate limiting (configurable `rate_limit_per_minute`)
- Expiry support (`expires_at`)
- Middleware stack: `AuthenticateApiKey` → `EnforceApiScopes` → `ApiRateLimiter`
- Management UI at `/api-keys`
- Initial scope registry: `*`, `profile.read`, `api-keys.read`, `api-keys.write`

#### REST API (v1)
- `GET /api/v1/me` — returns authenticated user profile (requires `profile.read`)
- Versioned routing via `routes/api_v1.php`

#### AI Integration
- `AiGateway` service with pluggable driver pattern
- `OpenRouterDriver` (OpenAI-compatible, via `openai-php/laravel`)
- `NullDriver` for development/testing
- `PromptRegistry` — reusable templates with `{{variable}}` substitution
- `UsageTracker` — logs driver, model, token counts, cost (USD), duration
- `AiPrompt` and `AiUsageLog` Eloquent models
- Prompt manager UI (`/ai/prompts`) and usage dashboard (`/ai/usage`)

#### Webhooks
- `WebhookEndpoint` model with per-endpoint event subscriptions
- HMAC-SHA256 payload signing via `WebhookSigner`
- Async delivery via `DispatchWebhook` queued job
- Failure count tracking on endpoint model
- Management UI: list, create, edit (`/webhooks`)

#### Settings
- Profile settings (`/settings/profile`): name and email update
- Security settings (`/settings/security`): password change, 2FA setup/disable
- Appearance settings (`/settings/appearance`): light/dark/system theme
- Account deletion with confirmation modal

#### Module System
- `NavigationRegistry` for dynamic sidebar navigation with permission gating
- `ScopeRegistry` for API scope registration from modules
- `MakeModuleCommand` artisan command (`php artisan make:module`)
- `ModuleListCommand` artisan command (`php artisan module:list`)
- Reference module at `packages/acme/example-module/`
- Composer path repository support for local packages

#### UI & Layouts
- Authenticated layout with Flux sidebar and responsive navigation
- Auth layout with card, simple, and split-pane variants
- Reusable `<x-table>` component set (index, columns, column, rows, row, cell)
- Custom Flux icon components in `resources/views/components/flux/icon/`

#### Developer Experience
- Pest 4 test suite — 128 tests, 237 assertions, all passing
- Full test coverage: auth, settings, users, roles, API keys, AI, webhooks, commands
- Laravel Pint code formatting (`vendor/bin/pint --dirty`)
- Laravel Sail Docker environment
- Laravel Pail real-time log viewer
- `composer run dev` starts PHP server + queue + Pail + Vite HMR simultaneously
- SQLite by default (zero-config local development)

[1.0.0]: https://github.com/laravel/livewire-starter-kit/releases/tag/v1.0.0
