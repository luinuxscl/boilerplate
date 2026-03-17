<?php

namespace App\Providers;

use App\Contracts\AiDriverContract;
use App\Services\Ai\AiGateway;
use App\Services\Ai\Drivers\NullDriver;
use App\Services\Ai\Drivers\OpenRouterDriver;
use App\Services\Ai\PromptRegistry;
use App\Services\Ai\UsageTracker;
use Illuminate\Support\ServiceProvider;
use OpenAI\Laravel\Facades\OpenAI;

class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/ai.php', 'ai');

        $this->app->singleton(PromptRegistry::class);
        $this->app->singleton(UsageTracker::class);

        $this->app->singleton(AiDriverContract::class, function (): AiDriverContract {
            $driver = config('ai.default_driver', 'openrouter');

            return match ($driver) {
                'null'        => new NullDriver(),
                'openrouter'  => $this->makeOpenRouterDriver(),
                default       => throw new \RuntimeException("Unknown AI driver [{$driver}]."),
            };
        });

        $this->app->singleton(AiGateway::class, function (): AiGateway {
            return new AiGateway(
                $this->app->make(AiDriverContract::class),
                $this->app->make(UsageTracker::class),
                $this->app->make(PromptRegistry::class),
            );
        });
    }

    public function boot(): void
    {
        //
    }

    private function makeOpenRouterDriver(): OpenRouterDriver
    {
        $config = config('ai.drivers.openrouter');

        $client = OpenAI::factory()
            ->withApiKey($config['api_key'] ?? '')
            ->withBaseUri($config['base_url'])
            ->make();

        return new OpenRouterDriver($client, config('ai.default_model'));
    }
}
