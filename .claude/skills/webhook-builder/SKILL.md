---
name: webhook-builder
description: Implementa webhooks outbound con HMAC signing, circuit breaker, retry con queues y gestión de endpoints. Úsala al agregar webhooks, implementar notificaciones outbound, o cuando digas "agrega webhooks", "add webhooks", "implementa webhooks para...", "notifica externamente cuando...", "outbound webhook", "webhook con firma", "signed webhook", "circuit breaker webhook".
---

# Webhooks Outbound en el Boilerplate

## Componentes del sistema

```
app/
├── Webhooks/
│   ├── Contracts/
│   │   └── WebhookPayloadContract.php
│   ├── Payloads/
│   │   └── ResourceCreatedPayload.php
│   ├── Jobs/
│   │   └── DispatchWebhookJob.php
│   └── Services/
│       ├── WebhookSignerService.php
│       └── CircuitBreakerService.php
├── Models/
│   ├── WebhookEndpoint.php      # endpoints configurados por el cliente
│   └── WebhookDelivery.php      # log de intentos
└── Http/Controllers/
    └── WebhookEndpointController.php  # CRUD de endpoints
```

## 1. Registrar eventos en WebhookEventRegistry

```php
// En AppServiceProvider o en el módulo correspondiente
use App\Support\WebhookEventRegistry;

WebhookEventRegistry::register('resource.created', ResourceCreatedPayload::class);
WebhookEventRegistry::register('resource.updated', ResourceUpdatedPayload::class);
WebhookEventRegistry::register('resource.deleted', ResourceDeletedPayload::class);
```

## 2. Payload builder

```php
<?php

namespace App\Webhooks\Payloads;

use App\Models\Resource;
use App\Webhooks\Contracts\WebhookPayloadContract;

class ResourceCreatedPayload implements WebhookPayloadContract
{
    public function __construct(
        private readonly Resource $resource,
    ) {}

    public function event(): string
    {
        return 'resource.created';
    }

    public function payload(): array
    {
        return [
            'event'     => $this->event(),
            'timestamp' => now()->toISOString(),
            'data'      => [
                'id'         => $this->resource->id,
                'name'       => $this->resource->name,
                'status'     => $this->resource->status,
                'created_at' => $this->resource->created_at->toISOString(),
            ],
        ];
    }
}
```

## 3. HMAC Signing (SHA-256)

```php
<?php

namespace App\Webhooks\Services;

class WebhookSignerService
{
    /**
     * Genera la firma HMAC-SHA256 del payload.
     * El header enviado es: X-Webhook-Signature: sha256=<hex>
     */
    public function sign(string $payload, string $secret): string
    {
        return 'sha256=' . hash_hmac('sha256', $payload, $secret);
    }

    /**
     * Verifica que una firma recibida es válida.
     * Usa hash_equals para evitar timing attacks.
     */
    public function verify(string $payload, string $secret, string $signature): bool
    {
        return hash_equals($this->sign($payload, $secret), $signature);
    }
}
```

Headers enviados en cada webhook:

```
X-Webhook-Event: resource.created
X-Webhook-Signature: sha256=abc123...
X-Webhook-Delivery: <uuid>
Content-Type: application/json
```

## 4. Circuit Breaker

Estados: `closed` (normal) → `open` (falla, bloqueado) → `half-open` (prueba)

```php
<?php

namespace App\Webhooks\Services;

use Illuminate\Support\Facades\Cache;

class CircuitBreakerService
{
    private const FAILURE_THRESHOLD = 5;
    private const RECOVERY_TIME = 300; // 5 minutos
    private const HALF_OPEN_ATTEMPTS = 1;

    public function isOpen(int $endpointId): bool
    {
        return Cache::get("webhook_circuit:{$endpointId}:state") === 'open';
    }

    public function recordSuccess(int $endpointId): void
    {
        Cache::forget("webhook_circuit:{$endpointId}:failures");
        Cache::put("webhook_circuit:{$endpointId}:state", 'closed');
    }

    public function recordFailure(int $endpointId): void
    {
        $failures = Cache::increment("webhook_circuit:{$endpointId}:failures");

        if ($failures >= self::FAILURE_THRESHOLD) {
            Cache::put(
                "webhook_circuit:{$endpointId}:state",
                'open',
                self::RECOVERY_TIME
            );

            logger()->warning("Webhook circuit opened for endpoint {$endpointId}");
        }
    }

    public function getState(int $endpointId): string
    {
        return Cache::get("webhook_circuit:{$endpointId}:state", 'closed');
    }
}
```

## 5. Dispatch Job con retry y backoff

```php
<?php

namespace App\Webhooks\Jobs;

use App\Models\WebhookEndpoint;
use App\Models\WebhookDelivery;
use App\Webhooks\Contracts\WebhookPayloadContract;
use App\Webhooks\Services\CircuitBreakerService;
use App\Webhooks\Services\WebhookSignerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class DispatchWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $maxExceptions = 3;

    public function __construct(
        private readonly WebhookEndpoint $endpoint,
        private readonly WebhookPayloadContract $payload,
        private readonly string $deliveryId,
    ) {}

    /**
     * Backoff exponencial: 1min, 5min, 25min
     */
    public function backoff(): array
    {
        return [60, 300, 1500];
    }

    public function handle(
        CircuitBreakerService $circuitBreaker,
        WebhookSignerService $signer,
    ): void {
        if ($circuitBreaker->isOpen($this->endpoint->id)) {
            logger()->info("Webhook skipped — circuit open for endpoint {$this->endpoint->id}");
            return;
        }

        $body = json_encode($this->payload->payload());
        $signature = $signer->sign($body, $this->endpoint->secret);

        $delivery = WebhookDelivery::create([
            'endpoint_id' => $this->endpoint->id,
            'delivery_id' => $this->deliveryId,
            'event'       => $this->payload->event(),
            'payload'     => $body,
            'status'      => 'pending',
        ]);

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-Webhook-Event'     => $this->payload->event(),
                    'X-Webhook-Signature' => $signature,
                    'X-Webhook-Delivery'  => $this->deliveryId,
                ])
                ->post($this->endpoint->url, $this->payload->payload());

            if ($response->successful()) {
                $circuitBreaker->recordSuccess($this->endpoint->id);
                $delivery->update(['status' => 'delivered', 'response_code' => $response->status()]);
            } else {
                $circuitBreaker->recordFailure($this->endpoint->id);
                $delivery->update(['status' => 'failed', 'response_code' => $response->status()]);
                $this->fail("Webhook returned HTTP {$response->status()}");
            }
        } catch (\Exception $e) {
            $circuitBreaker->recordFailure($this->endpoint->id);
            $delivery->update(['status' => 'failed', 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
```

## 6. Disparar un webhook

```php
// En el event listener o en el modelo (Observer)
use App\Webhooks\Jobs\DispatchWebhookJob;
use App\Webhooks\Payloads\ResourceCreatedPayload;
use Illuminate\Support\Str;

// Buscar todos los endpoints suscritos al evento
$endpoints = WebhookEndpoint::where('is_active', true)
    ->whereJsonContains('events', 'resource.created')
    ->get();

foreach ($endpoints as $endpoint) {
    DispatchWebhookJob::dispatch(
        $endpoint,
        new ResourceCreatedPayload($resource),
        Str::uuid()->toString(),
    );
}
```

## 7. Modelos necesarios

```bash
php artisan make:model WebhookEndpoint --migration --no-interaction
php artisan make:model WebhookDelivery --migration --no-interaction
```

Campos clave del migration de `webhook_endpoints`:

```php
$table->id();
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
$table->string('url');
$table->string('secret', 64);        // para HMAC signing
$table->json('events');              // array de eventos suscritos
$table->boolean('is_active')->default(true);
$table->timestamps();
```

Campos clave de `webhook_deliveries`:

```php
$table->id();
$table->foreignId('endpoint_id')->constrained('webhook_endpoints')->cascadeOnDelete();
$table->uuid('delivery_id')->unique();
$table->string('event');
$table->json('payload');
$table->string('status');           // pending, delivered, failed
$table->integer('response_code')->nullable();
$table->text('error')->nullable();
$table->timestamps();
```

## 8. Tests Pest

```php
describe('WebhookDispatch', function () {

    it('dispatches webhook job when resource is created', function () {
        Queue::fake();
        $resource = Resource::factory()->create();

        Queue::assertPushed(DispatchWebhookJob::class);
    });

    it('signs payload with HMAC SHA-256', function () {
        $signer = app(WebhookSignerService::class);
        $payload = '{"event":"test"}';
        $secret = 'my-secret';

        $signature = $signer->sign($payload, $secret);

        expect($signature)->toStartWith('sha256=');
        expect($signer->verify($payload, $secret, $signature))->toBeTrue();
    });

    it('opens circuit after 5 consecutive failures', function () {
        $circuitBreaker = app(CircuitBreakerService::class);

        foreach (range(1, 5) as $i) {
            $circuitBreaker->recordFailure(endpointId: 1);
        }

        expect($circuitBreaker->isOpen(1))->toBeTrue();
    });

    it('skips delivery when circuit is open', function () {
        Http::fake();
        $circuitBreaker = app(CircuitBreakerService::class);
        Cache::put('webhook_circuit:1:state', 'open');

        $endpoint = WebhookEndpoint::factory()->create(['id' => 1]);
        $job = new DispatchWebhookJob($endpoint, new ResourceCreatedPayload(Resource::factory()->make()), 'uuid');
        $job->handle($circuitBreaker, app(WebhookSignerService::class));

        Http::assertNothingSent();
    });
});
```
