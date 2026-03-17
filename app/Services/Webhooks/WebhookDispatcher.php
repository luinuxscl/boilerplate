<?php

namespace App\Services\Webhooks;

use App\Contracts\Webhooks\WebhookEventContract;
use App\Jobs\DispatchWebhook;
use App\Models\WebhookEndpoint;

class WebhookDispatcher
{
    public function dispatch(WebhookEventContract $event): void
    {
        WebhookEndpoint::query()
            ->where('is_active', true)
            ->get()
            ->filter(fn (WebhookEndpoint $endpoint) => $endpoint->listensTo($event->eventName()))
            ->each(fn (WebhookEndpoint $endpoint) => DispatchWebhook::dispatch($endpoint, $event));
    }
}
