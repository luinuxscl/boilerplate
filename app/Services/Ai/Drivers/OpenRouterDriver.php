<?php

namespace App\Services\Ai\Drivers;

use App\Contracts\AiDriverContract;
use App\Services\Ai\AiResponse;
use OpenAI\Client;

class OpenRouterDriver implements AiDriverContract
{
    public function __construct(
        private readonly Client $client,
        private readonly string $defaultModel,
    ) {}

    /**
     * @param  array<string, mixed>  $options
     */
    public function complete(string $prompt, array $options = []): AiResponse
    {
        $startTime = hrtime(true);

        $model    = $options['model'] ?? $this->defaultModel;
        $messages = $options['messages'] ?? [['role' => 'user', 'content' => $prompt]];

        $payload = array_merge(
            array_diff_key($options, array_flip(['model', 'messages'])),
            ['model' => $model, 'messages' => $messages],
        );

        $response = $this->client->chat()->create($payload);

        $durationMs = (int) ((hrtime(true) - $startTime) / 1_000_000);

        return new AiResponse(
            content: $response->choices[0]->message->content ?? '',
            model: $response->model,
            driver: 'openrouter',
            promptTokens: $response->usage->promptTokens,
            completionTokens: $response->usage->completionTokens,
            totalTokens: $response->usage->totalTokens,
            costUsd: 0.0,
            durationMs: $durationMs,
        );
    }
}
