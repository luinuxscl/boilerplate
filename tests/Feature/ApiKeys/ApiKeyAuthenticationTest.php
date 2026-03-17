<?php

use App\Models\User;
use App\Services\ApiKeys\ApiKeyManager;

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

test('valid api key authenticates request', function (): void {
    $user = User::factory()->create();
    $manager = app(ApiKeyManager::class);
    $result = $manager->create($user, 'Test Key');

    $this->withToken($result['plain'])
        ->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('user.email', $user->email);
});

test('invalid api key returns 401', function (): void {
    $this->withToken('sk_invalid_key_that_does_not_exist')
        ->getJson('/api/v1/me')
        ->assertUnauthorized();
});

test('missing api key returns 401', function (): void {
    $this->getJson('/api/v1/me')
        ->assertUnauthorized();
});

test('expired api key returns 401', function (): void {
    $user = User::factory()->create();
    $manager = app(ApiKeyManager::class);
    $result = $manager->create($user, 'Expired Key', expiresAt: now()->subDay());

    $this->withToken($result['plain'])
        ->getJson('/api/v1/me')
        ->assertUnauthorized();
});

test('revoked api key returns 401', function (): void {
    $user = User::factory()->create();
    $manager = app(ApiKeyManager::class);
    $result = $manager->create($user, 'Revoked Key');
    $manager->revoke($result['api_key']);

    $this->withToken($result['plain'])
        ->getJson('/api/v1/me')
        ->assertUnauthorized();
});

test('x-api-key header is accepted', function (): void {
    $user = User::factory()->create();
    $manager = app(ApiKeyManager::class);
    $result = $manager->create($user, 'Header Key');

    $this->getJson('/api/v1/me', ['X-Api-Key' => $result['plain']])
        ->assertOk();
});

test('last used at is updated on successful authentication', function (): void {
    $user = User::factory()->create();
    $manager = app(ApiKeyManager::class);
    $result = $manager->create($user, 'Touch Key');

    expect($result['api_key']->fresh()->last_used_at)->toBeNull();

    $this->withToken($result['plain'])->getJson('/api/v1/me');

    expect($result['api_key']->fresh()->last_used_at)->not->toBeNull();
});
