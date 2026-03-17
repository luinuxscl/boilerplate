<?php

use App\Models\AiPrompt;
use App\Models\AiUsageLog;
use App\Models\User;
use App\Services\Ai\AiResponse;
use App\Services\Ai\UsageTracker;

beforeEach(function (): void {
    $this->tracker = new UsageTracker();
});

it('creates a usage log record', function (): void {
    $user = User::factory()->create();

    $response = new AiResponse(
        content: 'test',
        model: 'openai/gpt-4o-mini',
        driver: 'openrouter',
        promptTokens: 50,
        completionTokens: 25,
        totalTokens: 75,
        costUsd: 0.00005,
        durationMs: 300,
    );

    $log = $this->tracker->log($response, $user);

    expect($log)->toBeInstanceOf(AiUsageLog::class)
        ->and($log->user_id)->toBe($user->id)
        ->and($log->model)->toBe('openai/gpt-4o-mini')
        ->and($log->driver)->toBe('openrouter')
        ->and($log->prompt_tokens)->toBe(50)
        ->and($log->completion_tokens)->toBe(25)
        ->and($log->total_tokens)->toBe(75)
        ->and($log->request_duration_ms)->toBe(300);
});

it('creates a log without a user', function (): void {
    $response = new AiResponse(
        content: 'test',
        model: 'null',
        driver: 'null',
        promptTokens: 10,
        completionTokens: 10,
        totalTokens: 20,
        costUsd: 0.0,
        durationMs: 1,
    );

    $log = $this->tracker->log($response);

    expect($log->user_id)->toBeNull();
});

it('associates the log with a prompt model', function (): void {
    $user   = User::factory()->create();
    $prompt = AiPrompt::create([
        'name'      => 'test-prompt',
        'template'  => 'Hello',
        'is_active' => true,
    ]);

    $response = new AiResponse(
        content: 'ok',
        model: 'null',
        driver: 'null',
        promptTokens: 5,
        completionTokens: 5,
        totalTokens: 10,
        costUsd: 0.0,
        durationMs: 1,
    );

    $log = $this->tracker->log($response, $user, $prompt);

    expect($log->ai_prompt_id)->toBe($prompt->id);
});

it('summary returns correct aggregates', function (): void {
    $user = User::factory()->create();

    AiUsageLog::factory()->count(3)->create([
        'user_id'          => $user->id,
        'prompt_tokens'    => 100,
        'completion_tokens' => 50,
        'total_tokens'     => 150,
        'cost_usd'         => 0.0001,
    ]);

    $summary = $this->tracker->summary($user, 'day');

    expect($summary['requests'])->toBe(3)
        ->and($summary['total_tokens'])->toBe(450)
        ->and($summary['period'])->toBe('day');
});

it('summary returns zeros when no logs exist', function (): void {
    $user    = User::factory()->create();
    $summary = $this->tracker->summary($user, 'day');

    expect($summary['requests'])->toBe(0)
        ->and($summary['total_tokens'])->toBe(0)
        ->and($summary['cost_usd'])->toBe(0.0);
});
