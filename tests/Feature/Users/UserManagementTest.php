<?php

use App\Livewire\Users\UserForm;
use App\Livewire\Users\UserList;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

test('admin can view users list', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('users.index'))
        ->assertOk();
});

test('regular user cannot access users list', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertForbidden();
});

test('guests are redirected from users list', function (): void {
    $this->get(route('users.index'))
        ->assertRedirect(route('login'));
});

test('user list shows users with roles', function (): void {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'Jane Doe']);

    $this->actingAs($admin);

    Livewire::test(UserList::class)
        ->assertSee('Jane Doe');
});

test('admin can update user roles', function (): void {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->create();

    $this->actingAs($admin);

    Livewire::test(UserForm::class, ['user' => $target])
        ->set('selectedRoles', ['user'])
        ->call('save')
        ->assertHasNoErrors();

    expect($target->fresh()->hasRole('user'))->toBeTrue();
});

test('admin can delete a user', function (): void {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->create();

    $this->actingAs($admin);

    Livewire::test(UserList::class)
        ->call('deleteUser', $target->id)
        ->assertHasNoErrors();

    expect($target->fresh())->toBeNull();
});
