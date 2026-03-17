<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WebhookEndpoint;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WebhookEndpoint>
 */
class WebhookEndpointFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'       => User::factory(),
            'url'           => fake()->url(),
            'events'        => ['order.created', 'order.updated'],
            'secret'        => Str::random(64),
            'is_active'     => true,
            'failure_count' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function withEvents(array $events): static
    {
        return $this->state(['events' => $events]);
    }

    public function withFailures(int $count): static
    {
        return $this->state(['failure_count' => $count]);
    }
}
