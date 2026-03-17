<?php

use App\Models\AiPrompt;
use App\Models\AiUsageLog;
use App\Models\User;
use App\Services\Ai\AiGateway;
use App\Services\Ai\AiResponse;
use App\Services\Ai\Drivers\NullDriver;
use App\Services\Ai\PromptRegistry;
use App\Services\Ai\UsageTracker;

beforeEach(function (): void {
    $this->driver   = new NullDriver();
    $this->tracker  = new UsageTracker();
    $this->registry = new PromptRegistry();
    $this->gateway  = new AiGateway($this->driver, $this->tracker, $this->registry);
});

it('returns an AiResponse from complete()', function (): void {
    $response = $this->gateway->complete('Hello world');

    expect($response)->toBeInstanceOf(AiResponse::class)
        ->and($response->content)->toBe('This is a null driver response.')
        ->and($response->driver)->toBe('null');
});

it('does not log usage when tracking is disabled', function (): void {
    config(['ai.track_usage' => false]);

    $user = User::factory()->create();
    $this->gateway->forUser($user)->complete('Hello');

    expect(AiUsageLog::count())->toBe(0);
});

it('logs usage when tracking is enabled', function (): void {
    config(['ai.track_usage' => true]);

    $user = User::factory()->create();
    $this->gateway->forUser($user)->complete('Hello');

    expect(AiUsageLog::count())->toBe(1);

    $log = AiUsageLog::first();
    expect($log->user_id)->toBe($user->id)
        ->and($log->driver)->toBe('null');
});

it('logs usage without user when no user is set', function (): void {
    config(['ai.track_usage' => true]);

    $this->gateway->complete('Hello');

    $log = AiUsageLog::first();
    expect($log->user_id)->toBeNull();
});

it('renders a registered prompt and completes it', function (): void {
    config(['ai.track_usage' => false]);

    $this->registry->register('greet', [
        'template' => 'Say hello to {{name}}.',
    ]);

    $response = $this->gateway->prompt('greet', ['name' => 'Alice']);

    expect($response)->toBeInstanceOf(AiResponse::class);
});

it('uses model from db prompt when set', function (): void {
    config(['ai.track_usage' => false]);

    AiPrompt::create([
        'name'      => 'db-prompt',
        'template'  => 'Answer: {{question}}',
        'model'     => 'custom/model',
        'is_active' => true,
    ]);

    $customResponse = new AiResponse(
        content: 'answer',
        model: 'custom/model',
        driver: 'null',
        promptTokens: 5,
        completionTokens: 5,
        totalTokens: 10,
        costUsd: 0.0,
        durationMs: 1,
    );
    $this->driver->setNextResponse($customResponse);

    $response = $this->gateway->prompt('db-prompt', ['question' => 'test?']);

    expect($response->model)->toBe('custom/model');
});

it('throws exception for unknown prompt name', function (): void {
    config(['ai.track_usage' => false]);

    expect(fn () => $this->gateway->prompt('nonexistent'))
        ->toThrow(\RuntimeException::class, 'AI prompt [nonexistent] is not registered.');
});

it('forUser() returns a new instance without mutating the original', function (): void {
    config(['ai.track_usage' => true]);

    $user   = User::factory()->create();
    $scoped = $this->gateway->forUser($user);

    expect($scoped)->not->toBe($this->gateway);

    $scoped->complete('Hello');
    expect(AiUsageLog::first()->user_id)->toBe($user->id);
});
