<?php

namespace App\Contracts;

use App\Services\Ai\AiResponse;

interface AiDriverContract
{
    /**
     * Send a completion request to the AI provider.
     *
     * @param  array<string, mixed>  $options
     */
    public function complete(string $prompt, array $options = []): AiResponse;
}
