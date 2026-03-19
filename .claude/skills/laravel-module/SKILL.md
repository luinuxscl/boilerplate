---
name: laravel-module
description: Scaffold completo de módulos del boilerplate: migration, model, controller/Livewire, Form Request, Policy, registros en los 4 registries (ScopeRegistry, WebhookEventRegistry, NavigationRegistry, PermissionRegistrar), tests Pest y seeder demo. Úsala al crear un módulo nuevo, hacer scaffold de una feature, o cuando digas "crea un módulo", "make module", "nuevo módulo para...", "scaffold module", "genera el módulo de...", "quiero un módulo de X", "create module".
---

# Scaffold de Módulos del Boilerplate

Ver árbol completo y ejemplo real en `references/module-structure.md`.

## Proceso de creación (sigue este orden)

### 1. Migration

```bash
php artisan make:migration create_resources_table --no-interaction
```

Estructura base:

```php
Schema::create('resources', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('status')->default('active');
    $table->timestamps();
    $table->softDeletes(); // si el módulo lo requiere
});
```

### 2. Model

```bash
php artisan make:model Resource --no-interaction
```

```php
class Resource extends Model
{
    protected $fillable = ['user_id', 'name', 'status'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

Con factory y seeder:

```bash
php artisan make:factory ResourceFactory --model=Resource --no-interaction
php artisan make:seeder ResourceSeeder --no-interaction
```

### 3. Policy

```bash
php artisan make:policy ResourcePolicy --model=Resource --no-interaction
```

Usa permisos Spatie en cada método:

```php
class ResourcePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view-resources');
    }

    public function view(User $user, Resource $resource): bool
    {
        return $user->can('view-resources');
    }

    public function create(User $user): bool
    {
        return $user->can('create-resources');
    }

    public function update(User $user, Resource $resource): bool
    {
        return $user->can('edit-resources');
    }

    public function delete(User $user, Resource $resource): bool
    {
        return $user->can('delete-resources');
    }
}
```

### 4. Form Requests

```bash
php artisan make:request StoreResourceRequest --no-interaction
php artisan make:request UpdateResourceRequest --no-interaction
```

### 5. Controller (o Livewire según contexto)

**Si es UI web → Livewire component:**
```bash
php artisan make:livewire Resources/ResourceIndex --no-interaction
php artisan make:livewire Resources/ResourceForm --no-interaction
```

**Si es solo API → Controller:**
```bash
php artisan make:controller Api/V1/ResourceController --no-interaction
```

Ver skill `api-endpoint` para el patrón completo de API.

### 6. Registrar en los 4 registries

En `AppServiceProvider` o en un `ResourceServiceProvider` dedicado:

```php
public function boot(): void
{
    // 1. Permisos
    app(PermissionRegistrar::class)->register([
        'view-resources',
        'create-resources',
        'edit-resources',
        'delete-resources',
    ]);

    // 2. Navegación (si tiene UI)
    NavigationRegistry::register(
        key: 'resources',
        label: 'Resources',
        route: 'resources.index',
        icon: 'squares-2x2',         // Heroicon name
        permission: 'view-resources',
    );

    // 3. Scopes API (si expone endpoints)
    ScopeRegistry::register('resource:read', 'Read access to resources');
    ScopeRegistry::register('resource:write', 'Create, update, and delete resources');

    // 4. Webhook events (si emite eventos outbound)
    WebhookEventRegistry::register('resource.created', ResourceCreatedPayload::class);
    WebhookEventRegistry::register('resource.updated', ResourceUpdatedPayload::class);
    WebhookEventRegistry::register('resource.deleted', ResourceDeletedPayload::class);
}
```

**Regla:** Solo registra lo que el módulo realmente usa. No registres scopes si el módulo no tiene API, ni webhook events si no los emite.

### 7. Seeder de datos demo

```php
class ResourceSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::role('admin')->first();

        Resource::factory(10)->create([
            'user_id' => $admin->id,
        ]);
    }
}
```

Añadir al `DatabaseSeeder`:

```php
$this->call([
    RolesAndPermissionsSeeder::class,
    ResourceSeeder::class,
]);
```

### 8. Tests Pest

```bash
php artisan make:test --pest Modules/Resources/ResourceTest --no-interaction
```

Estructura mínima — ver skill `pest-testing` para patrones detallados:

```php
describe('Resource Module', function () {

    beforeEach(fn () => $this->seed(RolesAndPermissionsSeeder::class));

    it('allows admin to create resource', function () { ... });
    it('prevents viewer from creating resource', function () { ... });
    it('validates required fields', function () { ... });
    it('soft deletes resource', function () { ... });
});
```

## Checklist de módulo completo

Antes de declarar el módulo terminado:

- [ ] Migration ejecutada (`php artisan migrate`)
- [ ] Model con fillable, casts y relaciones
- [ ] Factory con estados de rol
- [ ] Policy registrada y testeada
- [ ] Form Requests con `authorize()` y `rules()`
- [ ] PermissionRegistrar — permisos CRUD registrados
- [ ] NavigationRegistry — entrada de menú (si tiene UI)
- [ ] ScopeRegistry — scopes registrados (si tiene API)
- [ ] WebhookEventRegistry — eventos registrados (si los emite)
- [ ] Seeder de demo funcional
- [ ] Tests: happy path + validación + autorización por rol
- [ ] `vendor/bin/pint --dirty` ejecutado

## Referencia

Ver `references/module-structure.md` para el árbol completo de archivos y un ejemplo real de módulo implementado.
