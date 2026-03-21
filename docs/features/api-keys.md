# API Keys

Sistema de autenticación API mediante claves con control de acceso por scopes y rate limiting configurable por clave.

## Overview

Las API Keys permiten a terceros (o servicios internos) autenticarse en la API REST del boilerplate sin usar credenciales de usuario. Cada clave tiene su propio conjunto de scopes y un límite de requests por minuto.

## Key Format

- **ID**: ULID (Universally Unique Lexicographically Sortable Identifier)
- **Almacenamiento**: Hash HMAC-SHA256 — el valor en texto plano solo se muestra una vez al crear
- **Formato al crear**: `{ulid}.{secret}` — el cliente recibe este token completo

## Authentication Header

```http
Authorization: Bearer {ulid}.{secret}
```

El middleware `AuthenticateApiKey` valida el token, extrae el ULID, busca la clave, verifica el hash del secret y comprueba la expiración.

## Scopes

Controlan qué endpoints puede llamar cada clave:

```php
// Scope exacto
'users:read'

// Wildcard — acceso a todos los scopes de users
'users:*'

// Super wildcard — acceso a todo
'*'
```

El middleware `EnforceScopes` verifica que la clave tenga el scope requerido por el endpoint.

Registrar scopes disponibles en un ServiceProvider:

```php
ScopeRegistry::register('users:read', 'Read access to users');
ScopeRegistry::register('users:write', 'Create and update users');
```

## Rate Limiting

Configurable por clave en el campo `rate_limit_per_minute`. El middleware `ApiRateLimiter` aplica el límite usando el ULID de la clave como identificador.

Valor `null` = sin límite.

## Expiración

Las claves pueden tener una fecha de expiración (`expires_at`). El middleware rechaza claves expiradas con `401`.

## Model

```php
// app/Models/ApiKey.php
ApiKey::create([
    'name'                  => 'My Service',
    'scopes'                => ['users:read', 'webhooks:*'],
    'rate_limit_per_minute' => 60,
    'expires_at'            => now()->addYear(),
]);
```

## UI

Las API Keys se gestionan desde `/api-keys` (requiere permiso `api-keys.view`).

## Extending

Para añadir un scope nuevo en un módulo:

```php
// En AppServiceProvider o un ServiceProvider dedicado
ScopeRegistry::register('orders:read', 'Read access to orders');
ScopeRegistry::register('orders:write', 'Create and update orders');
```

Para requerir un scope en un endpoint API, ver el skill `api-endpoint`.
