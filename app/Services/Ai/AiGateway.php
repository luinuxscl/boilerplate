<?php

namespace App\Services\Ai;

use App\Contracts\AiDriverContract;
use App\Models\User;

class AiGateway
{
    private ?User $contextUser = null;

    public function __construct(
        private readonly AiDriverContract $driver,
        private readonly UsageTracker $tracker,
        private readonly PromptRegistry $registry,
    ) {}

    /**
     * Return a new instance scoped to a specific user for usage tracking.
     */
    public function forUser(User $user): static
    {
        $clone              = clone $this;
        $clone->contextUser = $user;

        return $clone;
    }

    /**
     * Send a raw prompt to the AI driver.
     *
     * @param  array<string, mixed>  $options
     */
    public function complete(string $prompt, array $options = []): AiResponse
    {
        $response = $this->driver->complete($prompt, $options);

        if (config('ai.track_usage')) {
            $this->tracker->log($response, $this->contextUser);
        }

        return $response;
    }

    /**
     * Render a registered prompt by name and send it to the AI driver.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $options
     */
    public function prompt(string $name, array $data = [], array $options = []): AiResponse
    {
        $rendered   = $this->registry->render($name, $data);
        $promptModel = $this->registry->findModel($name);

        if ($promptModel !== null && isset($promptModel->model) && ! isset($options['model'])) {
            $options['model'] = $promptModel->model;
        }

        $response = $this->driver->complete($rendered, $options);

        if (config('ai.track_usage')) {
            $this->tracker->log($response, $this->contextUser, $promptModel);
        }

        return $response;
    }

    public function driver(): AiDriverContract
    {
        return $this->driver;
    }
}
