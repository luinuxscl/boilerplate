<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiPrompt extends Model
{
    /** @use HasFactory<\Database\Factories\AiPromptFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'template',
        'model',
        'is_active',
    ];

    public function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(AiUsageLog::class);
    }

    /**
     * Render the template with the given data, replacing {{key}} placeholders.
     *
     * @param  array<string, mixed>  $data
     */
    public function render(array $data = []): string
    {
        return preg_replace_callback('/\{\{(\w+)\}\}/', function (array $matches) use ($data): string {
            return (string) ($data[$matches[1]] ?? $matches[0]);
        }, $this->template);
    }
}
