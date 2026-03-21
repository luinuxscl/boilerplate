# Architecture Overview

Laravel 12 Livewire starter kit de producción con autenticación completa, RBAC, API Keys, AI Gateway y Webhooks outbound.

## Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| Runtime | PHP | 8.4 |
| Framework | Laravel | 12 |
| Frontend reactivo | Livewire | 4 |
| UI Components | Flux UI | 2 |
| Auth backend | Laravel Fortify | 1 |
| Permissions | Spatie Laravel Permission | latest |
| Testing | Pest | 4 |
| Queue | Laravel Queue (sync/database) | — |
| DB default | SQLite | — |

## Application Layers

```
┌─────────────────────────────────────────────┐
│  Browser / API Client                       │
└──────────────┬──────────────────────────────┘
               │
┌──────────────▼──────────────────────────────┐
│  Routes                                     │
│  routes/web.php, routes/settings.php        │
│  routes/api.php, routes/api_v1.php          │
└──────────────┬──────────────────────────────┘
               │
┌──────────────▼──────────────────────────────┐
│  Middleware                                 │
│  auth, AuthenticateApiKey,                  │
│  EnforceScopes, ApiRateLimiter              │
└──────────────┬──────────────────────────────┘
               │
        ┌──────┴──────┐
        │             │
┌───────▼───────┐  ┌──▼──────────────────────┐
│  Livewire     │  │  API Controllers        │
│  Components   │  │  app/Http/Controllers/  │
│  app/Livewire │  │  Api/V1/                │
└───────┬───────┘  └──┬──────────────────────┘
        │             │
        └──────┬──────┘
               │
┌──────────────▼──────────────────────────────┐
│  Services / Actions                         │
│  app/Services/, app/Actions/                │
└──────────────┬──────────────────────────────┘
               │
┌──────────────▼──────────────────────────────┐
│  Models / Eloquent ORM                      │
│  app/Models/                                │
└──────────────┬──────────────────────────────┘
               │
┌──────────────▼──────────────────────────────┐
│  Database (SQLite local / PostgreSQL prod)  │
└─────────────────────────────────────────────┘
```

## Key Design Decisions

### ULID como Primary Keys
Los modelos `ApiKey`, `AiPrompt`, `AiUsageLog` y `WebhookEndpoint` usan ULIDs en lugar de auto-increment integers. Razones:
- Sortables lexicográficamente (orden cronológico en strings)
- No revelan cantidad de registros en URLs de API
- Seguros para exponer en tokens públicos

### SQLite por Defecto
El boilerplate usa SQLite en local y tests. Razones:
- Zero config: no requiere servidor de base de datos para empezar
- Tests usan `:memory:` — rápidos y sin estado entre runs
- Producción debería usar PostgreSQL o MySQL via `.env`

### Fortify Headless
La autenticación usa Fortify sin las vistas de Jetstream. Razones:
- Las vistas son Blade puras y fáciles de personalizar
- Sin acoplamiento a un frontend framework específico
- El sistema de features es granular (habilitar/deshabilitar 2FA, email verification, etc.)

### Livewire Full-Page Components para Settings
Las páginas de settings son componentes Livewire full-page en lugar de controllers+views. Razones:
- Reactividad sin JS: los formularios validan y actualizan en tiempo real
- Encapsulan lógica y vista en un solo archivo
- Coherente con el patrón de UI del resto del boilerplate

## Directory Structure

```
app/
├── Actions/Fortify/          # CreateNewUser, ResetUserPassword
├── Concerns/                 # Traits de validación reutilizables
├── Console/Commands/         # Artisan commands (make:module, module:list)
├── Contracts/                # Interfaces (AiDriver, WebhookEvent)
├── Http/Middleware/          # AuthenticateApiKey, EnforceScopes, ApiRateLimiter
├── Jobs/                     # DispatchWebhook
├── Livewire/                 # Componentes reactivos por dominio
├── Models/                   # 7 modelos Eloquent
├── Providers/                # AppServiceProvider, FortifyServiceProvider
└── Services/                 # AiGateway, ApiKeyService, WebhookService

resources/views/
├── pages/auth/              # Vistas de autenticación (Fortify)
├── livewire/                # Blade de componentes Livewire
├── layouts/                 # app.blade.php, auth.blade.php
└── components/              # Componentes Blade reutilizables

routes/
├── web.php                  # Rutas web principales
├── settings.php             # Rutas de settings
├── api.php                  # API entry point con versioning
└── api_v1.php               # Endpoints v1
```
