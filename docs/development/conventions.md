# Development Conventions

## Naming

| Elemento | Convención | Ejemplo |
|----------|-----------|---------|
| Variables y métodos | camelCase descriptivo | `isRegisteredForDiscounts`, `hasExpiredAt` |
| Métodos booleanos | Prefijo `is`/`has`/`can` | `isActive()`, `hasExpired()`, `canDelete()` |
| Clases | PascalCase | `OrderController`, `CreateNewUser` |
| Enums (keys) | TitleCase | `Monthly`, `FavoritePerson` |
| Rutas nombradas | snake_case con puntos | `orders.index`, `api.v1.orders.show` |
| Permisos | `{module}.{action}` | `orders.view`, `api-keys.create` |
| Scopes API | `{module}:{permission}` | `orders:read`, `users:write` |
| Webhook events | `{module}.{event}` | `order.created`, `user.deleted` |

## PHP

### Constructor Property Promotion

```php
// Correcto
public function __construct(
    public readonly OrderService $orderService,
    private readonly UserRepository $users,
) {}

// Incorrecto — viejo estilo
private OrderService $orderService;
public function __construct(OrderService $orderService)
{
    $this->orderService = $orderService;
}
```

### Return Types

Siempre declarar el tipo de retorno:

```php
public function getActiveOrders(): Collection
public function isAdmin(): bool
public function findByEmail(string $email): ?User
```

### Control Structures

Siempre llaves, incluso para cuerpos de una línea:

```php
// Correcto
if ($user->isAdmin()) {
    return redirect()->route('admin.dashboard');
}

// Incorrecto
if ($user->isAdmin())
    return redirect()->route('admin.dashboard');
```

### PHPDoc

Preferir PHPDoc sobre comentarios inline. Añadir array shapes cuando sea útil:

```php
/**
 * @param array{name: string, email: string, role: string} $data
 * @return array{token: string, expires_at: Carbon}
 */
public function createUser(array $data): array
```

## Laravel

### Form Requests Obligatorios

Nunca validar inline en controllers:

```php
// Correcto
public function store(StoreOrderRequest $request): RedirectResponse
{
    Order::create($request->validated());
    return redirect()->route('orders.index');
}

// Incorrecto
public function store(Request $request): RedirectResponse
{
    $validated = $request->validate([...]);
}
```

### Eloquent sobre DB::

```php
// Correcto
Order::query()->where('status', 'pending')->get();

// Incorrecto
DB::table('orders')->where('status', 'pending')->get();
```

### Eager Loading (evitar N+1)

```php
// Correcto
Order::with(['user', 'items'])->latest()->get();

// Incorrecto — N+1 si hay muchas órdenes
Order::all()->each(fn ($o) => $o->user->name);
```

### Named Routes

```php
// Correcto
return redirect()->route('orders.index');
<a href="{{ route('orders.show', $order) }}">

// Incorrecto
return redirect('/orders');
```

## Validation Traits

Los traits de validación reutilizables viven en `app/Concerns/`:

- `PasswordValidationRules` — `passwordRules()`, `currentPasswordRules()`
- `ProfileValidationRules` — `profileRules()`, `nameRules()`, `emailRules()`

Usar estos traits en Form Requests y Livewire components en lugar de duplicar reglas.

## Testing

- Un test = un comportamiento
- Usar factories con estados: `User::factory()->admin()->create()`
- Tests de features en `tests/Feature/`, unitarios en `tests/Unit/`
- La mayoría deben ser feature tests (más confiables que unit tests aislados)
- Siempre seedear roles antes de tests con permisos:

```php
beforeEach(fn () => $this->seed(RolesAndPermissionsSeeder::class));
```

## Code Formatting

Ejecutar Pint antes de hacer commit:

```bash
vendor/bin/pint --dirty   # Solo archivos modificados
```

Pint aplica el preset `laravel` automáticamente.
