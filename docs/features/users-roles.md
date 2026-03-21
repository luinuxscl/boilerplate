# Users & Roles

Control de acceso basado en roles (RBAC) usando **Spatie Laravel Permission**. El boilerplate incluye 3 roles preconfigurados y un sistema de permisos por grupos.

## Overview

Cada usuario tiene un rol que agrupa permisos. Los permisos se verifican en Policies, middleware y componentes Livewire. La UI de gestión está en `/users`.

## Roles Preconfigurados

| Rol | Descripción |
|-----|-------------|
| `user` | Usuario estándar. Asignado por defecto al registrarse. |
| `admin` | Acceso a gestión de usuarios, API Keys, AI y Webhooks. |
| `super-admin` | Acceso total. Bypassa todas las policies por convención de Spatie. |

Se crean en `database/seeders/RolesAndPermissionsSeeder.php`.

## Grupos de Permisos

Los permisos están organizados en grupos por feature:

| Grupo | Permisos |
|-------|---------|
| `users` | `users.view`, `users.create`, `users.edit`, `users.delete` |
| `roles` | `roles.view`, `roles.assign` |
| `api-keys` | `api-keys.view`, `api-keys.create`, `api-keys.delete` |
| `ai` | `ai.view`, `ai.manage` |
| `webhooks` | `webhooks.view`, `webhooks.create`, `webhooks.delete` |

## Verificar Permisos

**En PHP:**
```php
$user->can('users.view')
$user->hasPermissionTo('users.edit')
$user->hasRole('admin')
```

**En Blade:**
```blade
@can('users.view')
    <flux:button>Manage Users</flux:button>
@endcan

@role('admin')
    ...
@endrole
```

**En Policies:**
```php
public function viewAny(User $user): bool
{
    return $user->can('users.view');
}
```

## Registrar Permisos de un Módulo Nuevo

En `AppServiceProvider` o un ServiceProvider dedicado:

```php
app(PermissionRegistrar::class)->register([
    'orders.view',
    'orders.create',
    'orders.edit',
    'orders.delete',
]);
```

Luego asignar al rol `admin` en el seeder.

## User Management UI

- `/users` — Lista de usuarios (requiere `users.view`)
- `/users/{user}/edit` — Editar usuario y asignar rol (requiere `users.edit`)

Componentes Livewire en `app/Livewire/Users/`.

## Model

```php
// app/Models/User.php
// Usa los traits HasRoles y HasPermissions de Spatie
$user->assignRole('admin');
$user->syncRoles(['admin']);
$user->givePermissionTo('users.view');
$user->revokePermissionTo('users.view');
```

## Settings Pages

Los usuarios gestionan su propio perfil desde:
- `/settings/profile` — Nombre y email
- `/settings/security` — Contraseña y 2FA
- `/settings/appearance` — Tema (light/dark/system)
