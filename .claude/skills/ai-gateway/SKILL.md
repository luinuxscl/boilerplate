---
name: ai-gateway
description: Implementa el AI Gateway del boilerplate con driver pattern, OpenRouter como default, cost tracking en integer cents y rate limiting por usuario. Úsala al trabajar con el AI Gateway, añadir un nuevo provider, implementar cost tracking, o cuando digas "usa el AI gateway", "llama al LLM", "implementa AI", "add AI provider", "cost tracking AI", "openrouter", "AI driver", "AiProviderContract", "ai gateway".
---

# AI Gateway con Driver Pattern

## Arquitectura del sistema

```
app/
├── AI/
│   ├── Contracts/
│   │   └── AiProviderContract.php
│   ├── Drivers/
│   │   ├── OpenRouterDriver.php      # default
│   │   ├── OpenAiDriver.php
│   │   └── AnthropicDriver.php
│   ├── AiGateway.php                 # fachada principal
│   └── Exceptions/
│       └── AiProviderException.php
├── Models/
│   └── AiUsageLog.php
config/
└── ai.php
```

## 1. Contrato del provider

```php
<?php

namespace App\AI\Contracts;

interface AiProviderContract
{
    /**
     * Completa un prompt y retorna respuesta con cost tracking.
     *
     * @return array{content: string, usage: array{prompt_tokens: int, completion_tokens: int}, cost_cents: int}
     */
    public function complete(string $prompt, array $options = []): array;

    /**
     * Retorna el nombre del driver para logging.
     */
    public function driverName(): string;
}
```

**Regla crítica:** `cost_cents` siempre en **integer cents** — nunca floats. Ejemplo: $0.003 → 0 cents (redondear a int), $0.03 → 3 cents. Usa `intval(round($cost * 100))`.

## 2. Driver OpenRouter (default)

```php
<?php

namespace App\AI\Drivers;

use App\AI\Contracts\AiProviderContract;
use App\AI\Exceptions\AiProviderException;
use Illuminate\Support\Facades\Http;

class OpenRouterDriver implements AiProviderContract
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $defaultModel = 'anthropic/claude-3-haiku',
    ) {}

    public function complete(string $prompt, array $options = []): array
    {
        $response = Http::withToken($this->apiKey)
            ->post('https://openrouter.ai/api/v1/chat/completions', [
                'model'    => $options['model'] ?? $this->defaultModel,
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'max_tokens' => $options['max_tokens'] ?? 1000,
            ]);

        if ($response->failed()) {
            throw new AiProviderException(
                "OpenRouter error: {$response->status()} - {$response->body()}"
            );
        }

        $data = $response->json();

        return [
            'content'    => $data['choices'][0]['message']['content'],
            'usage'      => [
                'prompt_tokens'     => $data['usage']['prompt_tokens'] ?? 0,
                'completion_tokens' => $data['usage']['completion_tokens'] ?? 0,
            ],
            'cost_cents' => $this->calculateCostCents($data),
        ];
    }

    public function driverName(): string
    {
        return 'openrouter';
    }

    /**
     * Calcula el costo en integer cents desde los tokens usados.
     * Precios aproximados — ajustar según el modelo.
     */
    private function calculateCostCents(array $data): int
    {
        // OpenRouter retorna el costo en la respuesta cuando está disponible
        if (isset($data['usage']['cost'])) {
            return intval(round($data['usage']['cost'] * 100));
        }

        return 0; // fallback si el provider no retorna costo
    }
}
```

## 3. AiGateway — fachada principal

```php
<?php

namespace App\AI;

use App\AI\Contracts\AiProviderContract;
use App\Models\AiUsageLog;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

class AiGateway
{
    public function __construct(
        private readonly AiProviderContract $provider,
    ) {}

    /**
     * Completa un prompt con logging de uso y cost tracking.
     */
    public function complete(
        string $prompt,
        array $options = [],
        ?User $user = null,
    ): string {
        if ($user) {
            $this->enforceRateLimit($user);
        }

        $result = $this->provider->complete($prompt, $options);

        AiUsageLog::create([
            'user_id'           => $user?->id,
            'driver'            => $this->provider->driverName(),
            'prompt_tokens'     => $result['usage']['prompt_tokens'],
            'completion_tokens' => $result['usage']['completion_tokens'],
            'cost_cents'        => $result['cost_cents'], // integer cents
            'model'             => $options['model'] ?? config('ai.default_model'),
        ]);

        return $result['content'];
    }

    private function enforceRateLimit(User $user): void
    {
        $key = "ai-gateway:{$user->id}";
        $limit = config('ai.rate_limit_per_minute', 10);

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            $seconds = RateLimiter::availableIn($key);
            throw new \RuntimeException("AI rate limit exceeded. Try again in {$seconds} seconds.");
        }

        RateLimiter::hit($key, 60);
    }
}
```

## 4. Configuración `config/ai.php`

```php
<?php

return [
    'default_driver' => env('AI_DEFAULT_DRIVER', 'openrouter'),

    'default_model' => env('AI_DEFAULT_MODEL', 'anthropic/claude-3-haiku'),

    'rate_limit_per_minute' => env('AI_RATE_LIMIT', 10),

    'drivers' => [
        'openrouter' => [
            'api_key' => env('OPENROUTER_API_KEY'),
            'default_model' => env('OPENROUTER_DEFAULT_MODEL', 'anthropic/claude-3-haiku'),
        ],
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'default_model' => env('OPENAI_DEFAULT_MODEL', 'gpt-4o-mini'),
        ],
        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'default_model' => env('ANTHROPIC_DEFAULT_MODEL', 'claude-3-haiku-20240307'),
        ],
    ],
];
```

## 5. Binding en Service Provider

```php
// En AppServiceProvider o AiServiceProvider
$this->app->bind(AiProviderContract::class, function ($app) {
    $driver = config('ai.default_driver');
    $config = config("ai.drivers.{$driver}");

    return match ($driver) {
        'openrouter' => new OpenRouterDriver(
            apiKey: $config['api_key'],
            defaultModel: $config['default_model'],
        ),
        'openai'     => new OpenAiDriver($config['api_key'], $config['default_model']),
        'anthropic'  => new AnthropicDriver($config['api_key'], $config['default_model']),
        default      => throw new \InvalidArgumentException("Unknown AI driver: {$driver}"),
    };
});

$this->app->singleton(AiGateway::class);
```

## 6. Modelo y migration AiUsageLog

```bash
php artisan make:model AiUsageLog --migration --no-interaction
```

```php
// Migration
$table->id();
$table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
$table->string('driver');
$table->string('model');
$table->unsignedInteger('prompt_tokens')->default(0);
$table->unsignedInteger('completion_tokens')->default(0);
$table->unsignedInteger('cost_cents')->default(0);  // SIEMPRE integer cents
$table->timestamps();

// Índice para reportes de costo por usuario
$table->index(['user_id', 'created_at']);
```

## 7. Cambiar de provider en runtime

```php
// Forzar un driver específico para una operación
$driver = new OpenAiDriver(
    apiKey: config('ai.drivers.openai.api_key'),
    defaultModel: 'gpt-4o',
);

$gateway = new AiGateway($driver);
$result = $gateway->complete('Summarize this document...', user: $user);
```

## 8. Tests Pest

```php
use App\AI\AiGateway;
use App\AI\Contracts\AiProviderContract;
use App\Models\AiUsageLog;
use App\Models\User;

beforeEach(function () {
    $this->mockProvider = Mockery::mock(AiProviderContract::class);
    $this->app->bind(AiProviderContract::class, fn () => $this->mockProvider);
});

it('returns content from provider', function () {
    $this->mockProvider->shouldReceive('complete')
        ->once()
        ->andReturn([
            'content'    => 'Hello world',
            'usage'      => ['prompt_tokens' => 10, 'completion_tokens' => 5],
            'cost_cents' => 0,
        ]);

    $this->mockProvider->shouldReceive('driverName')->andReturn('mock');

    $gateway = app(AiGateway::class);
    $result = $gateway->complete('Say hello');

    expect($result)->toBe('Hello world');
});

it('logs usage with integer cents after completion', function () {
    $user = User::factory()->create();

    $this->mockProvider->shouldReceive('complete')->andReturn([
        'content'    => 'Response',
        'usage'      => ['prompt_tokens' => 100, 'completion_tokens' => 50],
        'cost_cents' => 3,
    ]);
    $this->mockProvider->shouldReceive('driverName')->andReturn('mock');

    app(AiGateway::class)->complete('prompt', user: $user);

    $log = AiUsageLog::where('user_id', $user->id)->first();

    expect($log)
        ->not->toBeNull()
        ->cost_cents->toBe(3)
        ->prompt_tokens->toBe(100);
});

it('enforces rate limit per user', function () {
    $user = User::factory()->create();

    $this->mockProvider->shouldReceive('complete')->andReturn([
        'content' => 'ok', 'usage' => [], 'cost_cents' => 0,
    ]);
    $this->mockProvider->shouldReceive('driverName')->andReturn('mock');

    $gateway = app(AiGateway::class);
    $limit = config('ai.rate_limit_per_minute', 10);

    // Agotar el rate limit
    foreach (range(1, $limit) as $i) {
        $gateway->complete('prompt', user: $user);
    }

    expect(fn () => $gateway->complete('one more', user: $user))
        ->toThrow(\RuntimeException::class, 'AI rate limit exceeded');
});

it('switches driver when bound explicitly', function () {
    $altDriver = Mockery::mock(AiProviderContract::class);
    $altDriver->shouldReceive('complete')->andReturn([
        'content' => 'from alt', 'usage' => [], 'cost_cents' => 0,
    ]);
    $altDriver->shouldReceive('driverName')->andReturn('alt');

    $gateway = new AiGateway($altDriver);
    $result = $gateway->complete('test');

    expect($result)->toBe('from alt');
});
```

## Variables de entorno necesarias

```env
AI_DEFAULT_DRIVER=openrouter
AI_DEFAULT_MODEL=anthropic/claude-3-haiku
AI_RATE_LIMIT=10

OPENROUTER_API_KEY=sk-or-...
OPENROUTER_DEFAULT_MODEL=anthropic/claude-3-haiku
```
