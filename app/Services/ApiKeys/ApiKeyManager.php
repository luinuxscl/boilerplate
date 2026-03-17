<?php

namespace App\Services\ApiKeys;

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Support\Str;

class ApiKeyManager
{
    /**
     * Generate a new API key for a user and return the plain-text key (shown once).
     *
     * @param array<string> $scopes
     */
    public function create(
        User $user,
        string $name,
        array $scopes = ['*'],
        int $rateLimitPerMinute = 60,
        ?\DateTimeInterface $expiresAt = null,
    ): array {
        $plain = 'sk_' . Str::random(40);

        $apiKey = ApiKey::create([
            'user_id'              => $user->id,
            'name'                 => $name,
            'key_hash'             => hash('sha256', $plain),
            'key_prefix'           => Str::substr($plain, 0, 8),
            'scopes'               => $scopes,
            'rate_limit_per_minute' => $rateLimitPerMinute,
            'expires_at'           => $expiresAt,
            'is_active'            => true,
        ]);

        return ['api_key' => $apiKey, 'plain' => $plain];
    }

    public function findByPlain(string $plain): ?ApiKey
    {
        $hash = hash('sha256', $plain);

        return ApiKey::query()
            ->where('key_hash', $hash)
            ->where('is_active', true)
            ->first();
    }

    public function revoke(ApiKey $apiKey): void
    {
        $apiKey->update(['is_active' => false]);
    }

    public function touchLastUsed(ApiKey $apiKey): void
    {
        $apiKey->updateQuietly(['last_used_at' => now()]);
    }
}
