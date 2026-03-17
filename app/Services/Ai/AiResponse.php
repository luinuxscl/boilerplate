<?php

namespace App\Services\Ai;

readonly class AiResponse
{
    public function __construct(
        public string $content,
        public string $model,
        public string $driver,
        public int $promptTokens,
        public int $completionTokens,
        public int $totalTokens,
        public float $costUsd,
        public int $durationMs,
    ) {}
}
