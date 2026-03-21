# Module System

Sistema para añadir funcionalidad al boilerplate mediante módulos y paquetes Composer locales. Incluye 4 registries para integración limpia de cualquier módulo.

## Overview

Un módulo es un conjunto de models, controllers/Livewire, policies, migrations y tests que añaden una feature de negocio. El sistema de registries permite que cada módulo se integre en la navegación, API, webhooks y permisos sin modificar código core.

## Comando de Scaffold

```bash
php artisan make:module {Name}
```

Genera el esqueleto completo: migration, model, policy, form requests, Livewire components, tests Pest y seeder.

## Los 4 Registries

### 1. PermissionRegistrar

Registra los permisos CRUD del módulo:

```php
app(PermissionRegistrar::class)->register([
    'orders.view',
    'orders.create',
    'orders.edit',
    'orders.delete',
]);
```

### 2. NavigationRegistry

Añade una entrada al sidebar (si el módulo tiene UI):

```php
NavigationRegistry::register(
    key: 'orders',
    label: 'Orders',
    route: 'orders.index',
    icon: 'shopping-cart',       // Heroicon name
    permission: 'orders.view',
);
```

### 3. ScopeRegistry

Expone scopes de API (si el módulo tiene endpoints):

```php
ScopeRegistry::register('orders:read', 'Read access to orders');
ScopeRegistry::register('orders:write', 'Create and update orders');
```

### 4. WebhookEventRegistry

Registra eventos outbound (si el módulo emite webhooks):

```php
WebhookEventRegistry::register('order.created', OrderCreatedPayload::class);
WebhookEventRegistry::register('order.updated', OrderUpdatedPayload::class);
```

**Regla:** Solo registra lo que el módulo realmente usa. No registres scopes si no hay API, ni webhook events si no se emiten.

## Paquetes Composer Locales (Client Packages)

Para personalizaciones de cliente, el boilerplate soporta paquetes Composer via path repositories:

```json
// composer.json
"repositories": [
    {
        "type": "path",
        "url": "./packages/*/*/*"
    }
]
```

Los paquetes locales viven en `packages/{vendor}/{package-name}/`.

Para crear un paquete de cliente, activar el skill `laravel-package`.

## Checklist de Módulo Completo

- [ ] Migration ejecutada
- [ ] Model con fillable, casts y relaciones
- [ ] Factory y Seeder
- [ ] Policy registrada
- [ ] Form Requests con `authorize()` y `rules()`
- [ ] PermissionRegistrar — permisos CRUD
- [ ] NavigationRegistry — si tiene UI
- [ ] ScopeRegistry — si tiene API
- [ ] WebhookEventRegistry — si emite eventos
- [ ] Tests Pest: happy path + validación + autorización por rol
- [ ] `vendor/bin/pint --dirty` ejecutado

Para scaffold completo, activar el skill `laravel-module`.
