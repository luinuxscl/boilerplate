# Estructura Completa de un Módulo

## Árbol de archivos esperado

```
app/
├── Models/
│   └── Resource.php
├── Policies/
│   └── ResourcePolicy.php
├── Http/
│   ├── Controllers/
│   │   └── Api/V1/
│   │       └── ResourceController.php    # si tiene API
│   └── Requests/
│       ├── StoreResourceRequest.php
│       └── UpdateResourceRequest.php
├── Livewire/
│   └── Resources/
│       ├── ResourceIndex.php             # si tiene UI
│       └── ResourceForm.php
├── Webhooks/
│   └── Payloads/
│       └── ResourceCreatedPayload.php    # si emite webhooks
└── Http/
    └── Resources/
        └── ResourceResource.php          # si tiene API

database/
├── migrations/
│   └── 2024_01_01_000001_create_resources_table.php
├── factories/
│   └── ResourceFactory.php
└── seeders/
    └── ResourceSeeder.php

resources/views/
└── livewire/
    └── resources/
        ├── resource-index.blade.php
        └── resource-form.blade.php

tests/Feature/
└── Modules/
    └── Resources/
        └── ResourceTest.php

routes/
└── web.php  # añadir rutas del módulo aquí
```

## Ejemplo completo: Módulo "Projects"

### Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'status',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'due_date'   => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
```

### Factory con estados de rol

```php
<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'name'        => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'status'      => fake()->randomElement(['active', 'paused', 'completed']),
            'due_date'    => fake()->dateTimeBetween('now', '+6 months'),
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function completed(): static
    {
        return $this->state(['status' => 'completed']);
    }

    public function overdue(): static
    {
        return $this->state([
            'status'   => 'active',
            'due_date' => fake()->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }
}
```

### Registro en registries (ProjectServiceProvider)

```php
<?php

namespace App\Providers;

use App\Webhooks\Payloads\ProjectCreatedPayload;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\PermissionRegistrar;

class ProjectServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Permisos
        app(PermissionRegistrar::class)->registerPermissions([
            'view-projects',
            'create-projects',
            'edit-projects',
            'delete-projects',
        ]);

        // Navegación
        \App\Support\NavigationRegistry::register(
            key: 'projects',
            label: 'Projects',
            route: 'projects.index',
            icon: 'folder',
            permission: 'view-projects',
            order: 10,
        );

        // Scopes API
        \App\Support\ScopeRegistry::register('projects:read', 'View projects and their details');
        \App\Support\ScopeRegistry::register('projects:write', 'Create, update, and delete projects');

        // Webhook events
        \App\Support\WebhookEventRegistry::register('project.created', ProjectCreatedPayload::class);
        \App\Support\WebhookEventRegistry::register('project.completed', ProjectCompletedPayload::class);
    }
}
```

Registrar en `bootstrap/providers.php`:

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Providers\ProjectServiceProvider::class,  // añadir
];
```

### Policy

```php
<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view-projects');
    }

    public function view(User $user, Project $project): bool
    {
        return $user->can('view-projects');
    }

    public function create(User $user): bool
    {
        return $user->can('create-projects');
    }

    public function update(User $user, Project $project): bool
    {
        return $user->can('edit-projects');
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->can('delete-projects');
    }

    public function restore(User $user, Project $project): bool
    {
        return $user->can('delete-projects');
    }

    public function forceDelete(User $user, Project $project): bool
    {
        return $user->hasRole('super-admin');
    }
}
```

### Form Request

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Project::class);
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status'      => ['required', 'in:active,paused,completed'],
            'due_date'    => ['nullable', 'date', 'after:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'   => 'The project name is required.',
            'status.in'       => 'Status must be active, paused, or completed.',
            'due_date.after'  => 'Due date must be in the future.',
        ];
    }
}
```

### Tests Pest completos

```php
<?php

use App\Models\Project;
use App\Models\User;

describe('Projects Module', function () {

    beforeEach(fn () => $this->seed(RolesAndPermissionsSeeder::class));

    describe('listing', function () {

        it('shows projects to users with view permission', function () {
            $user = User::factory()->admin()->create();
            Project::factory(3)->create(['user_id' => $user->id]);

            actingAs($user)
                ->get(route('projects.index'))
                ->assertOk()
                ->assertSeeLivewire('resources.project-index');
        });

        it('denies access to users without permission', function () {
            $viewer = User::factory()->viewer()->create();

            actingAs($viewer)
                ->get(route('projects.index'))
                ->assertForbidden();
        });
    });

    describe('creation', function () {

        it('admin can create a project', function () {
            $admin = User::factory()->admin()->create();

            actingAs($admin)
                ->postJson(route('api.v1.projects.store'), [
                    'name'   => 'New Project',
                    'status' => 'active',
                ])
                ->assertCreated()
                ->assertJsonPath('data.name', 'New Project');

            expect(Project::count())->toBe(1);
        });

        it('fails validation with missing name', function () {
            $admin = User::factory()->admin()->create();

            actingAs($admin)
                ->postJson(route('api.v1.projects.store'), ['status' => 'active'])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['name']);
        });

        it('editor cannot create projects', function () {
            $editor = User::factory()->editor()->create();

            actingAs($editor)
                ->postJson(route('api.v1.projects.store'), [
                    'name'   => 'Project',
                    'status' => 'active',
                ])
                ->assertForbidden();
        });
    });

    describe('permissions by role', function () {

        it('enforces create permission by role', function (string $role, int $expectedStatus) {
            $user = User::factory()->{$role}()->create();

            actingAs($user)
                ->postJson(route('api.v1.projects.store'), [
                    'name'   => 'Test',
                    'status' => 'active',
                ])
                ->assertStatus($expectedStatus);

        })->with([
            'super-admin' => ['superAdmin', 201],
            'admin'       => ['admin', 201],
            'editor'      => ['editor', 403],
            'viewer'      => ['viewer', 403],
        ]);
    });
});
```
