# Database

## Models

| Model | Table | PK Type | Description |
|-------|-------|---------|-------------|
| `User` | `users` | auto-increment | Usuario autenticable |
| `Role` | `roles` | auto-increment | Extiende Spatie Role |
| `Permission` | `permissions` | auto-increment | Extiende Spatie Permission |
| `ApiKey` | `api_keys` | ULID | Claves de API con scopes |
| `AiPrompt` | `ai_prompts` | ULID | Templates de prompts |
| `AiUsageLog` | `ai_usage_logs` | ULID | Log de requests al LLM |
| `WebhookEndpoint` | `webhook_endpoints` | ULID | Endpoints externos |

## Relationships

```
User ──has many──► ApiKey
User ──has many──► AiUsageLog
User ──has many──► WebhookEndpoint
User ──belongs to many──► Role (via Spatie)
Role ──belongs to many──► Permission (via Spatie)
```

## User Model

```php
// Traits relevantes
use HasFactory, Notifiable;
use HasRoles;                    // Spatie
use TwoFactorAuthenticatable;    // Fortify 2FA

// Campos notables
$table->string('name');
$table->string('email')->unique();
$table->timestamp('email_verified_at')->nullable();
$table->string('two_factor_secret')->nullable();
$table->text('two_factor_recovery_codes')->nullable();
$table->timestamp('two_factor_confirmed_at')->nullable();

// Método helper
public function initials(): string
// Devuelve iniciales del nombre (e.g., "John Doe" → "JD")
```

## ApiKey Model

```php
$table->ulid('id')->primary();
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
$table->string('name');
$table->string('key_hash');          // HMAC-SHA256 del secret
$table->string('key_prefix');        // ULID — parte pública del token
$table->json('scopes')->default('[]');
$table->integer('rate_limit_per_minute')->nullable();
$table->timestamp('last_used_at')->nullable();
$table->timestamp('expires_at')->nullable();
```

## AiUsageLog Model

```php
$table->ulid('id')->primary();
$table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
$table->string('model');
$table->integer('prompt_tokens');
$table->integer('completion_tokens');
$table->integer('total_tokens');
$table->integer('cost_in_cents');    // USD en centavos enteros (no float)
$table->integer('duration_ms');
$table->timestamps();
```

**Importante:** `cost_in_cents` es integer — nunca float — para evitar errores de precisión en sumas y comparaciones.

## WebhookEndpoint Model

```php
$table->ulid('id')->primary();
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
$table->string('url');
$table->string('secret');            // Para verificar firma HMAC en el receptor
$table->json('events')->default('[]'); // ['*'] para todos, o lista específica
$table->boolean('is_active')->default(true);
$table->integer('failed_attempts')->default(0);
$table->timestamp('last_triggered_at')->nullable();
```

## Testing Strategy

Tests usan SQLite `:memory:` configurado en `phpunit.xml`:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

Beneficios:
- Sin estado persistente entre test runs
- Sin necesidad de limpiar base de datos manualmente
- Velocidad: SQLite en memoria es muy rápido

Cada test que necesita datos de roles debe llamar:
```php
beforeEach(fn () => $this->seed(RolesAndPermissionsSeeder::class));
```

## Casts

Los modelos usan el método `casts()` (no la propiedad `$casts`):

```php
protected function casts(): array
{
    return [
        'scopes'     => 'array',
        'events'     => 'array',
        'expires_at' => 'datetime',
    ];
}
```
