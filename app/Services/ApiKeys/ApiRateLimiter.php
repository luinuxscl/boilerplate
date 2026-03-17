<?php

namespace App\Services\ApiKeys;

use App\Models\ApiKey;
use Illuminate\Support\Facades\Cache;

class ApiRateLimiter
{
    public function attempt(ApiKey $apiKey): bool
    {
        $key = "api_rate_limit:{$apiKey->id}";
        $limit = $apiKey->rate_limit_per_minute;

        $hits = (int) Cache::get($key, 0);

        if ($hits >= $limit) {
            return false;
        }

        if ($hits === 0) {
            Cache::put($key, 1, 60);
        } else {
            Cache::increment($key);
        }

        return true;
    }

    public function remaining(ApiKey $apiKey): int
    {
        $key = "api_rate_limit:{$apiKey->id}";
        $hits = (int) Cache::get($key, 0);

        return max(0, $apiKey->rate_limit_per_minute - $hits);
    }

    public function resetFor(ApiKey $apiKey): void
    {
        Cache::forget("api_rate_limit:{$apiKey->id}");
    }
}
