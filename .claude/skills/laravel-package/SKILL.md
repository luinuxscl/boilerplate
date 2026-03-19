---
name: laravel-package
description: Crea paquetes Composer privados para personalización de clientes sobre el boilerplate. Úsala al crear un paquete para un cliente nuevo, añadir personalizaciones aisladas, o cuando digas "crea un paquete para el cliente X", "nuevo paquete cliente", "create client package", "package for client", "personalización del cliente", "paquete composer privado".
---

# Paquetes Composer Privados para Clientes

## Cuándo crear un paquete

Crea un paquete cuando el cliente necesita:
- Vistas o layouts personalizados
- Modelos o relaciones adicionales
- Rutas propias (sin tocar el boilerplate)
- Migrations adicionales
- Config específica del cliente
- Overrides de comportamiento base

**No crear paquete** para: configuración de `.env`, roles/permisos base (van en seeders), o features que deberían estar en el boilerplate.

## Estructura del paquete

```
packages/
└── acme/                          # nombre del cliente en minúsculas
    ├── composer.json
    ├── src/
    │   ├── AcmeServiceProvider.php
    │   ├── Models/                # Modelos adicionales del cliente
    │   ├── Http/
    │   │   ├── Controllers/
    │   │   └── Requests/
    │   └── Livewire/              # Componentes Livewire del cliente
    ├── config/
    │   └── acme.php
    ├── database/
    │   ├── migrations/
    │   └── seeders/
    ├── resources/
    │   └── views/
    ├── routes/
    │   └── web.php
    └── tests/
        └── Feature/
```

## Crear el paquete paso a paso

### 1. `composer.json` del paquete

```json
{
    "name": "cliente/acme",
    "description": "Acme client customizations",
    "type": "library",
    "license": "proprietary",
    "autoload": {
        "psr-4": {
            "Cliente\\Acme\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Cliente\\Acme\\Tests\\": "tests/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "Cliente\\Acme\\AcmeServiceProvider"
            ]
        }
    },
    "require": {
        "php": "^8.4",
        "laravel/framework": "^12.0"
    },
    "require-dev": {
        "pestphp/pest": "^4.0"
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}
```

### 2. Service Provider

```php
<?php

namespace Cliente\Acme;

use Illuminate\Support\ServiceProvider;

class AcmeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/acme.php',
            'acme'
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'acme');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/acme.php' => config_path('acme.php'),
            ], 'acme-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/acme'),
            ], 'acme-views');
        }

        // Registrar componentes Livewire del cliente
        \Livewire\Livewire::component('acme::dashboard', \Cliente\Acme\Livewire\Dashboard::class);
    }
}
```

### 3. Configurar path repository (desarrollo local)

En el `composer.json` raíz del proyecto:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/acme",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        "cliente/acme": "*"
    }
}
```

```bash
composer require cliente/acme
```

### 4. Extender modelos base

```php
// src/Models/AcmeUser.php
namespace Cliente\Acme\Models;

use App\Models\User;

class AcmeUser extends User
{
    protected $table = 'users'; // misma tabla

    // Relaciones adicionales
    public function acmeProfile(): HasOne
    {
        return $this->hasOne(AcmeProfile::class);
    }
}
```

O mediante traits si no quieres heredar:

```php
// src/Concerns/HasAcmeProfile.php
trait HasAcmeProfile
{
    public function acmeProfile(): HasOne
    {
        return $this->hasOne(AcmeProfile::class);
    }
}

// Bind en el Service Provider
$this->app->extend(User::class, function (User $user) {
    // usar macros o mixins según el caso
});
```

### 5. Rutas del paquete

```php
// routes/web.php
use Illuminate\Support\Facades\Route;
use Cliente\Acme\Http\Controllers\DashboardController;

Route::middleware(['web', 'auth'])->prefix('acme')->name('acme.')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
});
```

### 6. Vistas — override de vistas del boilerplate

Para sobrescribir una vista del boilerplate, publícala y edítala:

```bash
# Publicar vistas del boilerplate
php artisan vendor:publish --tag=acme-views
```

O en el Service Provider, añade el namespace con prioridad:

```php
// Las vistas del paquete tienen prioridad sobre las del proyecto
$this->loadViewsFrom(__DIR__ . '/../resources/views', 'acme');

// Para override de vistas del proyecto base:
// usar view()->prependNamespace() con el namespace correcto
```

### 7. Test base del paquete

```php
// tests/Feature/AcmeServiceProviderTest.php
use Cliente\Acme\AcmeServiceProvider;

it('registers service provider correctly', function () {
    expect(app()->getProviders(AcmeServiceProvider::class))
        ->not->toBeEmpty();
});

it('loads package config', function () {
    expect(config('acme'))->not->toBeNull();
});

it('loads package routes', function () {
    expect(route('acme.dashboard'))->toContain('/acme/dashboard');
});
```

## Migrar a Private Packagist (producción)

1. Crear repo privado en GitHub/GitLab para el paquete
2. Push del directorio `packages/acme/` como repo independiente
3. Configurar en [Private Packagist](https://packagist.com) o Satis
4. En `composer.json` raíz, cambiar el repository:

```json
{
    "repositories": [
        {
            "type": "composer",
            "url": "https://packagist.tu-empresa.com"
        }
    ]
}
```

5. `composer update cliente/acme`
6. Eliminar el directorio `packages/acme/` del repo del proyecto

**Regla:** En producción nunca usar `path` repositories. En CI/CD usar el token de Private Packagist como variable de entorno.
