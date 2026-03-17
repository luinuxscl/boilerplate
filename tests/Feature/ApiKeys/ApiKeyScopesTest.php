<?php

use App\Models\User;
use App\Services\ApiKeys\ApiKeyManager;

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

test('wildcard scope grants access to all endpoints', function (): void {
    $user = User::factory()->create();
    $manager = app(ApiKeyManager::class);
    $result = $manager->create($user, 'Wildcard Key', scopes: ['*']);

    $this->withToken($result['plain'])
        ->getJson('/api/v1/me')
        ->assertOk();
});

test('specific scope grants access to matching endpoint', function (): void {
    $user = User::factory()->create();
    $manager = app(ApiKeyManager::class);
    $result = $manager->create($user, 'Scoped Key', scopes: ['profile.read']);

    $this->withToken($result['plain'])
        ->getJson('/api/v1/me')
        ->assertOk();
});

test('missing scope returns 403', function (): void {
    $user = User::factory()->create();
    $manager = app(ApiKeyManager::class);
    $result = $manager->create($user, 'No Scope Key', scopes: ['api-keys.read']);

    $this->withToken($result['plain'])
        ->getJson('/api/v1/me')
        ->assertForbidden();
});

test('hasScope returns true for wildcard key', function (): void {
    $user = User::factory()->create();
    $manager = app(ApiKeyManager::class);
    $result = $manager->create($user, 'Wildcard', scopes: ['*']);

    expect($result['api_key']->hasScope('anything'))->toBeTrue();
});

test('hasScope returns false for key without matching scope', function (): void {
    $user = User::factory()->create();
    $manager = app(ApiKeyManager::class);
    $result = $manager->create($user, 'Limited', scopes: ['profile.read']);

    expect($result['api_key']->hasScope('api-keys.write'))->toBeFalse();
});
