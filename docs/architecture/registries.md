# Registries

Los 4 registries permiten que los módulos se integren en el boilerplate sin modificar código core. Se registran en el `boot()` de un ServiceProvider.

## PermissionRegistrar

Registra los permisos de un módulo con Spatie.

```php
use App\Services\Modules\PermissionRegistrar;

app(PermissionRegistrar::class)->register([
    'orders.view',
    'orders.create',
    'orders.edit',
    'orders.delete',
]);
```

**Convención de naming:** `{module}.{action}` — e.g., `orders.view`, `api-keys.create`.

Los permisos se sincronizan en base de datos al ejecutar el seeder `RolesAndPermissionsSeeder`.

---

## NavigationRegistry

Gestiona las entradas del sidebar de la aplicación. Solo registrar si el módulo tiene UI web.

```php
use App\Services\Modules\NavigationRegistry;

NavigationRegistry::register(
    key: 'orders',
    label: 'Orders',
    route: 'orders.index',
    icon: 'shopping-cart',       // Heroicon name
    permission: 'orders.view',   // Solo se muestra si el usuario tiene este permiso
);
```

El sidebar en `resources/views/layouts/app/sidebar.blade.php` itera sobre los items del registry.

---

## ScopeRegistry

Gestiona los scopes disponibles para API Keys. Solo registrar si el módulo expone endpoints API.

```php
use App\Services\Modules\ScopeRegistry;

ScopeRegistry::register('orders:read', 'Read access to orders');
ScopeRegistry::register('orders:write', 'Create and update orders');
```

**Convención de naming:** `{module}:{permission}` — e.g., `orders:read`, `users:write`.

Los scopes registrados aparecen en la UI de creación de API Keys para que el usuario pueda seleccionarlos.

---

## WebhookEventRegistry

Registra los eventos que pueden disparar webhooks outbound. Solo registrar si el módulo emite eventos.

```php
use App\Services\Modules\WebhookEventRegistry;

WebhookEventRegistry::register('order.created', OrderCreatedPayload::class);
WebhookEventRegistry::register('order.updated', OrderUpdatedPayload::class);
WebhookEventRegistry::register('order.deleted', OrderDeletedPayload::class);
```

**Convención de naming:** `{module}.{event}` — e.g., `order.created`, `user.deleted`.

Los eventos registrados aparecen en la UI de configuración de Webhook Endpoints.

---

## Cuándo Crear un ServiceProvider Dedicado

Usar `AppServiceProvider` para módulos pequeños o del core. Crear un `{Module}ServiceProvider` dedicado cuando:

- El módulo tiene más de 4-5 registraciones combinadas entre todos los registries
- El módulo se va a extraer como paquete Composer (cliente package)
- El módulo tiene su propia configuración (`config/{module}.php`)

```php
// bootstrap/providers.php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\OrderServiceProvider::class,  // dedicado para Orders
];
```
