<?php

namespace Database\Factories;

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ApiKey>
 */
class ApiKeyFactory extends Factory
{
    public function definition(): array
    {
        $raw = 'sk_' . Str::random(40);

        return [
            'user_id'              => User::factory(),
            'name'                 => fake()->words(3, true),
            'key_hash'             => hash('sha256', $raw),
            'key_prefix'           => Str::substr($raw, 0, 8),
            'scopes'               => ['*'],
            'rate_limit_per_minute' => 60,
            'expires_at'           => null,
            'last_used_at'         => null,
            'is_active'            => true,
        ];
    }

    public function withScopes(array $scopes): static
    {
        return $this->state(['scopes' => $scopes]);
    }

    public function expired(): static
    {
        return $this->state(['expires_at' => now()->subDay()]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function withRateLimit(int $perMinute): static
    {
        return $this->state(['rate_limit_per_minute' => $perMinute]);
    }
}
