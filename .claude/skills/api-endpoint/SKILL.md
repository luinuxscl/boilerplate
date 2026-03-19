---
name: api-endpoint
description: Crea endpoints REST versionados con autenticación por API key, scopes, rate limiting, Form Requests, API Resources y tests Pest completos. Úsala al crear un endpoint API, añadir una ruta API, implementar un recurso REST, o cuando digas "crea un endpoint", "add API endpoint", "nuevo endpoint para...", "expón esto como API", "create REST endpoint", "API route for...".
---

# API Endpoints en el Boilerplate

## Estructura de un endpoint completo

Por cada endpoint necesitas:

```
app/Http/
├── Controllers/Api/V1/
│   └── ResourceController.php
├── Requests/Api/V1/
│   ├── StoreResourceRequest.php
│   └── UpdateResourceRequest.php
└── Resources/
    ├── ResourceResource.php
    └── ResourceCollection.php   # opcional, si necesitas meta custom
routes/
└── api.php                      # añadir aquí
tests/Feature/Api/V1/
└── ResourceTest.php
```

## 1. Rutas en `routes/api.php`

```php
use App\Http\Controllers\Api\V1\ResourceController;

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {

    // Scopes por acción
    Route::get('/resources', [ResourceController::class, 'index'])
        ->middleware('ability:resource:read');

    Route::post('/resources', [ResourceController::class, 'store'])
        ->middleware('ability:resource:write');

    Route::get('/resources/{resource}', [ResourceController::class, 'show'])
        ->middleware('ability:resource:read');

    Route::put('/resources/{resource}', [ResourceController::class, 'update'])
        ->middleware('ability:resource:write');

    Route::delete('/resources/{resource}', [ResourceController::class, 'destroy'])
        ->middleware('ability:resource:write');
});
```

**Reglas:**
- Siempre bajo `/v1/` (o versión mayor correspondiente)
- `auth:sanctum` en el grupo, scopes en la ruta individual
- Registrar los scopes usados en `ScopeRegistry`

## 2. Registrar scopes en ScopeRegistry

```php
// En AppServiceProvider o en el módulo correspondiente
ScopeRegistry::register('resource:read', 'Read access to resources');
ScopeRegistry::register('resource:write', 'Create, update, and delete resources');
```

## 3. Rate limiting

En `app/Providers/AppServiceProvider.php` o `bootstrap/app.php`:

```php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by(
        $request->user()?->id ?: $request->ip()
    );
});

// Rate limit más estricto para endpoints de escritura
RateLimiter::for('api-write', function (Request $request) {
    return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
});
```

Aplicar en rutas:

```php
Route::post('/resources', [ResourceController::class, 'store'])
    ->middleware(['ability:resource:write', 'throttle:api-write']);
```

## 4. Controller

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreResourceRequest;
use App\Http\Requests\Api\V1\UpdateResourceRequest;
use App\Http\Resources\ResourceResource;
use App\Models\Resource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ResourceController extends Controller
{
    public function index(): ResourceCollection
    {
        return ResourceResource::collection(
            Resource::query()
                ->with(['relation']) // eager load siempre
                ->paginate()
        );
    }

    public function show(Resource $resource): ResourceResource
    {
        return new ResourceResource($resource->load('relation'));
    }

    public function store(StoreResourceRequest $request): JsonResponse
    {
        $resource = Resource::create($request->validated());

        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateResourceRequest $request, Resource $resource): ResourceResource
    {
        $resource->update($request->validated());

        return new ResourceResource($resource->fresh());
    }

    public function destroy(Resource $resource): JsonResponse
    {
        $resource->delete();

        return response()->json(null, 204);
    }
}
```

## 5. Form Requests

```php
<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Resource::class);
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status'      => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The resource name is required.',
            'status.in'     => 'Status must be active or inactive.',
        ];
    }
}
```

## 6. API Resource

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResourceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'status'      => $this->status,
            'relation'    => RelationResource::collection($this->whenLoaded('relation')),
            'created_at'  => $this->created_at->toISOString(),
            'updated_at'  => $this->updated_at->toISOString(),
        ];
    }
}
```

**Reglas:**
- Usar `$this->whenLoaded()` para relaciones — nunca acceder directamente
- Fechas siempre en ISO 8601 (`->toISOString()`)
- No exponer campos internos: `password`, `remember_token`, IDs internos no necesarios
- IDs siempre como enteros o UUIDs, nunca como strings

## 7. Manejo de errores estándar

Laravel ya retorna JSON automáticamente para rutas API cuando `Accept: application/json`.

Para errores de negocio:

```php
// En el controller
if ($resource->isLocked()) {
    return response()->json([
        'message' => 'Resource is locked and cannot be modified.',
        'code'    => 'RESOURCE_LOCKED',
    ], 422);
}

// Abortar con mensaje
abort(404, 'Resource not found.');
```

Formato estándar de error que Laravel genera:

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "name": ["The resource name is required."]
    }
}
```

## 8. Tests Pest completos

```php
<?php

use App\Models\Resource;
use App\Models\User;

describe('Resource API', function () {

    beforeEach(fn () => $this->seed(RolesAndPermissionsSeeder::class));

    describe('GET /api/v1/resources', function () {

        it('returns paginated resources with read scope', function () {
            $user = User::factory()->create();
            $token = $user->createToken('test', ['resource:read'])->plainTextToken;
            Resource::factory(3)->create();

            $this->withToken($token)
                ->getJson('/api/v1/resources')
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [['id', 'name', 'status']],
                    'meta' => ['total', 'per_page'],
                ]);
        });

        it('returns 401 when unauthenticated', function () {
            getJson('/api/v1/resources')->assertUnauthorized();
        });

        it('returns 403 when token lacks read scope', function () {
            $user = User::factory()->create();
            $token = $user->createToken('test', ['resource:write'])->plainTextToken;

            $this->withToken($token)
                ->getJson('/api/v1/resources')
                ->assertForbidden();
        });
    });

    describe('POST /api/v1/resources', function () {

        it('creates resource with write scope', function () {
            $user = User::factory()->admin()->create();
            $token = $user->createToken('test', ['resource:write'])->plainTextToken;

            $this->withToken($token)
                ->postJson('/api/v1/resources', [
                    'name'   => 'New Resource',
                    'status' => 'active',
                ])
                ->assertCreated()
                ->assertJsonPath('data.name', 'New Resource');

            expect(Resource::count())->toBe(1);
        });

        it('fails validation when name is missing', function () {
            $user = User::factory()->admin()->create();
            $token = $user->createToken('test', ['resource:write'])->plainTextToken;

            $this->withToken($token)
                ->postJson('/api/v1/resources', ['status' => 'active'])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['name']);
        });
    });
});
```
