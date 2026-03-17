<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Permissions grouped by domain.
     *
     * @var array<string, list<string>>
     */
    private array $permissions = [
        'users'    => ['view', 'create', 'edit', 'delete'],
        'roles'    => ['view', 'assign'],
        'api-keys' => ['view', 'create', 'revoke'],
        'ai'       => ['use', 'manage-prompts', 'view-usage'],
        'webhooks' => ['view', 'create', 'edit', 'delete'],
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissions as $domain => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$domain}.{$action}"]);
            }
        }

        Role::firstOrCreate(['name' => 'user']);

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(
            Permission::whereNotIn('name', ['roles.view', 'roles.assign'])->pluck('name'),
        );

        // super-admin bypasses all gates via Gate::before in AppServiceProvider
        Role::firstOrCreate(['name' => 'super-admin']);
    }
}
