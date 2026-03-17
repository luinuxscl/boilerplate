<?php

use App\Models\ApiKey;
use App\Services\ApiKeys\ApiKeyManager;
use App\Services\ApiKeys\ApiRateLimiter;
use App\Models\User;

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

test('requests within rate limit are allowed', function (): void {
    $user = User::factory()->create();
    $manager = app(ApiKeyManager::class);
    $result = $manager->create($user, 'Rate Key', rateLimitPerMinute: 5);

    $this->withToken($result['plain'])
        ->getJson('/api/v1/me')
        ->assertOk()
        ->assertHeader('X-RateLimit-Limit', '5');
});

test('requests exceeding rate limit return 429', function (): void {
    $user = User::factory()->create();
    $manager = app(ApiKeyManager::class);
    $result = $manager->create($user, 'Limited Key', rateLimitPerMinute: 2);

    $limiter = app(ApiRateLimiter::class);
    $limiter->attempt($result['api_key']);
    $limiter->attempt($result['api_key']);

    $this->withToken($result['plain'])
        ->getJson('/api/v1/me')
        ->assertStatus(429);
});

test('rate limit remaining header decreases with requests', function (): void {
    $user = User::factory()->create();
    $manager = app(ApiKeyManager::class);
    $result = $manager->create($user, 'Decreasing Key', rateLimitPerMinute: 10);

    $response = $this->withToken($result['plain'])->getJson('/api/v1/me');
    $remaining = (int) $response->headers->get('X-RateLimit-Remaining');

    expect($remaining)->toBeLessThan(10);
});

test('rate limiter attempt returns false when limit exceeded', function (): void {
    $apiKey = ApiKey::factory()->withRateLimit(1)->create();
    $limiter = app(ApiRateLimiter::class);

    expect($limiter->attempt($apiKey))->toBeTrue();
    expect($limiter->attempt($apiKey))->toBeFalse();
});

test('rate limiter can be reset', function (): void {
    $apiKey = ApiKey::factory()->withRateLimit(1)->create();
    $limiter = app(ApiRateLimiter::class);

    $limiter->attempt($apiKey);
    $limiter->attempt($apiKey);
    $limiter->resetFor($apiKey);

    expect($limiter->attempt($apiKey))->toBeTrue();
});
