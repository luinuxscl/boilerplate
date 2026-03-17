<?php

namespace Database\Factories;

use App\Models\AiPrompt;
use App\Models\AiUsageLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiUsageLog>
 */
class AiUsageLogFactory extends Factory
{
    public function definition(): array
    {
        $promptTokens      = $this->faker->numberBetween(50, 500);
        $completionTokens  = $this->faker->numberBetween(10, 300);

        return [
            'user_id'              => User::factory(),
            'ai_prompt_id'         => null,
            'driver'               => 'openrouter',
            'model'                => $this->faker->randomElement(['openai/gpt-4o-mini', 'anthropic/claude-3-haiku']),
            'prompt_tokens'        => $promptTokens,
            'completion_tokens'    => $completionTokens,
            'total_tokens'         => $promptTokens + $completionTokens,
            'cost_usd'             => round(($promptTokens + $completionTokens) * 0.000002, 8),
            'request_duration_ms'  => $this->faker->numberBetween(200, 3000),
            'metadata'             => null,
        ];
    }

    public function forPrompt(AiPrompt $prompt): static
    {
        return $this->state(['ai_prompt_id' => $prompt->id]);
    }

    public function withModel(string $model): static
    {
        return $this->state(['model' => $model]);
    }
}
