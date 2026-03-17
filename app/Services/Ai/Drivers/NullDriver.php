<?php

namespace App\Services\Ai\Drivers;

use App\Contracts\AiDriverContract;
use App\Services\Ai\AiResponse;

class NullDriver implements AiDriverContract
{
    private ?AiResponse $nextResponse = null;

    /**
     * @param  array<string, mixed>  $options
     */
    public function complete(string $prompt, array $options = []): AiResponse
    {
        return $this->nextResponse ?? new AiResponse(
            content: 'This is a null driver response.',
            model: $options['model'] ?? 'null',
            driver: 'null',
            promptTokens: 10,
            completionTokens: 10,
            totalTokens: 20,
            costUsd: 0.0,
            durationMs: 1,
        );
    }

    public function setNextResponse(AiResponse $response): void
    {
        $this->nextResponse = $response;
    }
}
