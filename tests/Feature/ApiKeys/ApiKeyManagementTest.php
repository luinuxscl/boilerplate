<?php

use App\Livewire\ApiKeys\ApiKeyList;
use App\Models\ApiKey;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

test('admin can view api keys page', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('api-keys.index'))
        ->assertOk();
});

test('regular user cannot access api keys page', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('api-keys.index'))
        ->assertForbidden();
});

test('guests are redirected from api keys page', function (): void {
    $this->get(route('api-keys.index'))
        ->assertRedirect(route('login'));
});

test('admin can create an api key', function (): void {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    Livewire::test(ApiKeyList::class)
        ->set('newKeyName', 'My Integration')
        ->set('newKeyScopes', 'profile.read')
        ->set('newKeyRateLimit', 30)
        ->call('createKey')
        ->assertHasNoErrors()
        ->assertSet('showCreatedKey', true);

    expect(ApiKey::where('user_id', $admin->id)->count())->toBe(1);
});

test('api key name is required', function (): void {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    Livewire::test(ApiKeyList::class)
        ->set('newKeyName', '')
        ->call('createKey')
        ->assertHasErrors(['newKeyName']);
});

test('admin can revoke an api key', function (): void {
    $admin = User::factory()->admin()->create();
    $apiKey = ApiKey::factory()->for($admin)->create();
    $this->actingAs($admin);

    Livewire::test(ApiKeyList::class)
        ->call('revokeKey', $apiKey->id)
        ->assertHasNoErrors();

    expect($apiKey->fresh()->is_active)->toBeFalse();
});

test('created key is dismissed after acknowledgement', function (): void {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    Livewire::test(ApiKeyList::class)
        ->set('newKeyName', 'Temp Key')
        ->call('createKey')
        ->call('dismissCreatedKey')
        ->assertSet('showCreatedKey', false)
        ->assertSet('createdPlain', '');
});
