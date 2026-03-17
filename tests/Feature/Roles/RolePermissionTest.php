<?php

use App\Models\User;

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

test('super admin bypasses all permission checks', function (): void {
    $superAdmin = User::factory()->superAdmin()->create();

    expect($superAdmin->can('users.delete'))->toBeTrue();
    expect($superAdmin->can('roles.assign'))->toBeTrue();
    expect($superAdmin->can('ai.manage-prompts'))->toBeTrue();
});

test('admin has correct permissions', function (): void {
    $admin = User::factory()->admin()->create();

    expect($admin->can('users.view'))->toBeTrue();
    expect($admin->can('users.edit'))->toBeTrue();
    expect($admin->can('roles.assign'))->toBeFalse();
    expect($admin->can('roles.view'))->toBeFalse();
});

test('regular user has no admin permissions', function (): void {
    $user = User::factory()->create();

    expect($user->can('users.view'))->toBeFalse();
    expect($user->can('users.delete'))->toBeFalse();
});

test('withRole factory state assigns role correctly', function (): void {
    $user = User::factory()->withRole('admin')->create();

    expect($user->hasRole('admin'))->toBeTrue();
    expect($user->hasRole('super-admin'))->toBeFalse();
});

test('seeder creates all required roles', function (): void {
    expect(\App\Models\Role::where('name', 'super-admin')->exists())->toBeTrue();
    expect(\App\Models\Role::where('name', 'admin')->exists())->toBeTrue();
    expect(\App\Models\Role::where('name', 'user')->exists())->toBeTrue();
});
