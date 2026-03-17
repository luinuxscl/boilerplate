<?php

namespace App\Services\Ai;

use App\Models\AiPrompt;
use Illuminate\Support\Collection;

class PromptRegistry
{
    /**
     * In-memory registered prompts (name => template/meta).
     *
     * @var array<string, array{template: string, description: string, model: string|null}>
     */
    private array $prompts = [];

    /**
     * Register a prompt definition in memory (for code-driven prompts).
     *
     * @param  array{template: string, description?: string, model?: string|null}  $definition
     */
    public function register(string $name, array $definition): void
    {
        $this->prompts[$name] = [
            'template'    => $definition['template'],
            'description' => $definition['description'] ?? '',
            'model'       => $definition['model'] ?? null,
        ];
    }

    /**
     * Render a prompt by name, merging in-memory definitions with DB records.
     * DB records take precedence over in-memory definitions.
     *
     * @param  array<string, mixed>  $data
     */
    public function render(string $name, array $data = []): string
    {
        $dbPrompt = AiPrompt::query()
            ->where('name', $name)
            ->where('is_active', true)
            ->first();

        if ($dbPrompt instanceof AiPrompt) {
            return $dbPrompt->render($data);
        }

        if (isset($this->prompts[$name])) {
            $template = $this->prompts[$name]['template'];

            return preg_replace_callback('/\{\{(\w+)\}\}/', function (array $matches) use ($data): string {
                return (string) ($data[$matches[1]] ?? $matches[0]);
            }, $template);
        }

        throw new \RuntimeException("AI prompt [{$name}] is not registered.");
    }

    /**
     * Find the DB AiPrompt model by name (if it exists).
     */
    public function findModel(string $name): ?AiPrompt
    {
        return AiPrompt::query()
            ->where('name', $name)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get all in-memory registered prompt names.
     *
     * @return array<string, array{template: string, description: string, model: string|null}>
     */
    public function all(): array
    {
        return $this->prompts;
    }

    /**
     * Get all active prompts from the database.
     *
     * @return Collection<int, AiPrompt>
     */
    public function allFromDatabase(): Collection
    {
        return AiPrompt::query()->where('is_active', true)->orderBy('name')->get();
    }
}
