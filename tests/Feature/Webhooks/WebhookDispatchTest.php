<?php

use App\Contracts\Webhooks\WebhookEventContract;
use App\Jobs\DispatchWebhook;
use App\Models\WebhookEndpoint;
use App\Models\User;
use App\Services\Webhooks\WebhookDispatcher;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

// Minimal in-memory event for tests
function makeEvent(string $name = 'order.created', array $payload = []): WebhookEventContract
{
    return new class($name, $payload) implements WebhookEventContract {
        public function __construct(
            private string $name,
            private array $data,
        ) {}

        public function eventName(): string { return $this->name; }

        public function payload(): array { return $this->data; }
    };
}

it('dispatches a job for each active endpoint listening to the event', function (): void {
    Queue::fake();

    $user = User::factory()->create();

    WebhookEndpoint::factory()->for($user)->withEvents(['order.created'])->create();
    WebhookEndpoint::factory()->for($user)->withEvents(['order.updated'])->create();

    app(WebhookDispatcher::class)->dispatch(makeEvent('order.created'));

    Queue::assertPushed(DispatchWebhook::class, 1);
});

it('does not dispatch to inactive endpoints', function (): void {
    Queue::fake();

    $user = User::factory()->create();

    WebhookEndpoint::factory()->for($user)->inactive()->withEvents(['order.created'])->create();

    app(WebhookDispatcher::class)->dispatch(makeEvent('order.created'));

    Queue::assertNothingPushed();
});

it('dispatches to endpoints subscribed with wildcard *', function (): void {
    Queue::fake();

    $user = User::factory()->create();

    WebhookEndpoint::factory()->for($user)->withEvents(['*'])->create();

    app(WebhookDispatcher::class)->dispatch(makeEvent('anything.happened'));

    Queue::assertPushed(DispatchWebhook::class, 1);
});

it('sends signed http request to the endpoint url', function (): void {
    Http::fake(['*' => Http::response('', 200)]);

    $user     = User::factory()->create();
    $endpoint = WebhookEndpoint::factory()->for($user)->withEvents(['order.created'])->create([
        'url'    => 'https://example.com/hook',
        'secret' => 'test-secret',
    ]);

    $job = new DispatchWebhook($endpoint, makeEvent('order.created', ['id' => 1]));
    $job->handle(app(\App\Services\Webhooks\WebhookSigner::class));

    Http::assertSent(function (\Illuminate\Http\Client\Request $request): bool {
        return $request->url() === 'https://example.com/hook'
            && str_starts_with($request->header('X-Webhook-Signature')[0], 'sha256=')
            && $request->header('X-Webhook-Event')[0] === 'order.created';
    });
});

it('increments failure count when job fails', function (): void {
    $user     = User::factory()->create();
    $endpoint = WebhookEndpoint::factory()->for($user)->withEvents(['order.created'])->create();

    $job = new DispatchWebhook($endpoint, makeEvent('order.created'));
    $job->failed(new \RuntimeException('Connection refused'));

    expect($endpoint->fresh()->failure_count)->toBe(1);
});

it('deactivates endpoint after 10 failures', function (): void {
    $user     = User::factory()->create();
    $endpoint = WebhookEndpoint::factory()->for($user)->withEvents(['ping'])->withFailures(9)->create();

    $job = new DispatchWebhook($endpoint, makeEvent('ping'));
    $job->failed(new \RuntimeException('Connection refused'));

    expect($endpoint->fresh()->is_active)->toBeFalse();
});
