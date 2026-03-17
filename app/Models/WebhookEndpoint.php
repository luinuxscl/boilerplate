<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookEndpoint extends Model
{
    /** @use HasFactory<\Database\Factories\WebhookEndpointFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'url',
        'events',
        'secret',
        'is_active',
        'failure_count',
        'last_triggered_at',
    ];

    public function casts(): array
    {
        return [
            'events'            => 'array',
            'is_active'         => 'boolean',
            'failure_count'     => 'integer',
            'last_triggered_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function listensTo(string $event): bool
    {
        return in_array($event, $this->events ?? [], true)
            || in_array('*', $this->events ?? [], true);
    }
}
