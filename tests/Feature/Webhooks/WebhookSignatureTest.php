<?php

use App\Services\Webhooks\WebhookSigner;

beforeEach(function (): void {
    $this->signer = new WebhookSigner();
});

it('signs a payload and returns sha256 prefix', function (): void {
    $signature = $this->signer->sign('hello', 'secret');

    expect($signature)->toStartWith('sha256=');
});

it('produces a consistent signature for the same inputs', function (): void {
    $sig1 = $this->signer->sign('payload', 'mysecret');
    $sig2 = $this->signer->sign('payload', 'mysecret');

    expect($sig1)->toBe($sig2);
});

it('produces different signatures for different payloads', function (): void {
    $sig1 = $this->signer->sign('payload-a', 'secret');
    $sig2 = $this->signer->sign('payload-b', 'secret');

    expect($sig1)->not->toBe($sig2);
});

it('produces different signatures for different secrets', function (): void {
    $sig1 = $this->signer->sign('payload', 'secret-a');
    $sig2 = $this->signer->sign('payload', 'secret-b');

    expect($sig1)->not->toBe($sig2);
});

it('verifies a valid signature', function (): void {
    $payload   = '{"event":"order.created"}';
    $secret    = 'supersecret';
    $signature = $this->signer->sign($payload, $secret);

    expect($this->signer->verify($payload, $secret, $signature))->toBeTrue();
});

it('rejects a tampered payload', function (): void {
    $secret    = 'supersecret';
    $signature = $this->signer->sign('original', $secret);

    expect($this->signer->verify('tampered', $secret, $signature))->toBeFalse();
});

it('rejects a wrong secret on verify', function (): void {
    $payload   = 'data';
    $signature = $this->signer->sign($payload, 'correct-secret');

    expect($this->signer->verify($payload, 'wrong-secret', $signature))->toBeFalse();
});
