<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLog extends Model
{
    /** @use HasFactory<\Database\Factories\AiUsageLogFactory> */
    use HasFactory, HasUlids;

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'ai_prompt_id',
        'driver',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'cost_usd',
        'request_duration_ms',
        'metadata',
    ];

    public function casts(): array
    {
        return [
            'cost_usd' => 'decimal:8',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prompt(): BelongsTo
    {
        return $this->belongsTo(AiPrompt::class, 'ai_prompt_id');
    }
}
