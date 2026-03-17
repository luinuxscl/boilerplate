<?php

namespace App\Services\Webhooks;

class WebhookSigner
{
    /**
     * Sign a payload string using HMAC-SHA256.
     * Returns the signature in the format "sha256=<hex>".
     */
    public function sign(string $payload, string $secret): string
    {
        return 'sha256='.hash_hmac('sha256', $payload, $secret);
    }

    /**
     * Verify an incoming signature against a payload and secret.
     */
    public function verify(string $payload, string $secret, string $signature): bool
    {
        return hash_equals($this->sign($payload, $secret), $signature);
    }
}
