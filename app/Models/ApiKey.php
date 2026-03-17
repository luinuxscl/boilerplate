<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiKey extends Model
{
    /** @use HasFactory<\Database\Factories\ApiKeyFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'name',
        'key_hash',
        'key_prefix',
        'scopes',
        'rate_limit_per_minute',
        'expires_at',
        'last_used_at',
        'is_active',
    ];

    public function casts(): array
    {
        return [
            'scopes'       => 'array',
            'expires_at'   => 'datetime',
            'last_used_at' => 'datetime',
            'is_active'    => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function hasScope(string $scope): bool
    {
        return in_array('*', $this->scopes, true) || in_array($scope, $this->scopes, true);
    }

    public function hasScopes(array $scopes): bool
    {
        return collect($scopes)->every(fn (string $scope) => $this->hasScope($scope));
    }
}
