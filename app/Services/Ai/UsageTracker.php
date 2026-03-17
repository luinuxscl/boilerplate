<?php

namespace App\Services\Ai;

use App\Models\AiPrompt;
use App\Models\AiUsageLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class UsageTracker
{
    public function log(AiResponse $response, ?User $user = null, ?AiPrompt $prompt = null): AiUsageLog
    {
        return AiUsageLog::create([
            'user_id'             => $user?->id,
            'ai_prompt_id'        => $prompt?->id,
            'driver'              => $response->driver,
            'model'               => $response->model,
            'prompt_tokens'       => $response->promptTokens,
            'completion_tokens'   => $response->completionTokens,
            'total_tokens'        => $response->totalTokens,
            'cost_usd'            => $response->costUsd,
            'request_duration_ms' => $response->durationMs,
        ]);
    }

    public function forUser(User $user): Builder
    {
        return AiUsageLog::query()->where('user_id', $user->id);
    }

    /**
     * Return an aggregated summary for a user in the given period.
     *
     * @return array{requests: int, total_tokens: int, cost_usd: float, period: string}
     */
    public function summary(User $user, string $period = 'day'): array
    {
        $since = match ($period) {
            'hour'  => Carbon::now()->subHour(),
            'day'   => Carbon::now()->startOfDay(),
            'week'  => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            default => Carbon::now()->startOfDay(),
        };

        $logs = AiUsageLog::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $since)
            ->selectRaw('COUNT(*) as requests, SUM(total_tokens) as total_tokens, SUM(cost_usd) as cost_usd')
            ->first();

        return [
            'requests'     => (int) ($logs->requests ?? 0),
            'total_tokens' => (int) ($logs->total_tokens ?? 0),
            'cost_usd'     => (float) ($logs->cost_usd ?? 0.0),
            'period'       => $period,
        ];
    }
}
