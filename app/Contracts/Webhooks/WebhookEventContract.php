<?php

namespace App\Contracts\Webhooks;

interface WebhookEventContract
{
    /** The event name, e.g. "order.created". */
    public function eventName(): string;

    /**
     * The payload that will be serialised and sent.
     *
     * @return array<string, mixed>
     */
    public function payload(): array;
}
