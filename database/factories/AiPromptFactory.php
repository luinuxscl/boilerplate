<?php

namespace Database\Factories;

use App\Models\AiPrompt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiPrompt>
 */
class AiPromptFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'name'        => $this->faker->unique()->slug(3),
            'description' => $this->faker->sentence(),
            'template'    => 'Answer the following question: {{question}}',
            'model'       => null,
            'is_active'   => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function withModel(string $model): static
    {
        return $this->state(['model' => $model]);
    }

    public function system(): static
    {
        return $this->state(['user_id' => null]);
    }
}
