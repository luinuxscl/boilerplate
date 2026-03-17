<?php

namespace App\Livewire\Ai;

use App\Models\AiPrompt;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('AI Prompts')]
class PromptManager extends Component
{
    use AuthorizesRequests;

    public bool $showForm = false;
    public ?string $editingId = null;

    public string $name        = '';
    public string $description = '';
    public string $template    = '';
    public string $model       = '';
    public bool $isActive      = true;

    public function openCreate(): void
    {
        $this->authorize('ai.manage-prompts');
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(string $promptId): void
    {
        $this->authorize('ai.manage-prompts');

        $prompt = AiPrompt::query()->findOrFail($promptId);

        $this->editingId   = $prompt->id;
        $this->name        = $prompt->name;
        $this->description = $prompt->description ?? '';
        $this->template    = $prompt->template;
        $this->model       = $prompt->model ?? '';
        $this->isActive    = $prompt->is_active;
        $this->showForm    = true;
    }

    public function save(): void
    {
        $this->authorize('ai.manage-prompts');

        $validated = $this->validate([
            'name'        => ['required', 'string', 'max:100', 'regex:/^[a-z0-9\-_]+$/'],
            'description' => ['nullable', 'string', 'max:255'],
            'template'    => ['required', 'string'],
            'model'       => ['nullable', 'string', 'max:100'],
            'isActive'    => ['boolean'],
        ]);

        $data = [
            'user_id'     => auth()->id(),
            'name'        => $validated['name'],
            'description' => $validated['description'] ?: null,
            'template'    => $validated['template'],
            'model'       => $validated['model'] ?: null,
            'is_active'   => $validated['isActive'],
        ];

        if ($this->editingId !== null) {
            AiPrompt::query()->findOrFail($this->editingId)->update($data);
        } else {
            AiPrompt::create($data);
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function delete(string $promptId): void
    {
        $this->authorize('ai.manage-prompts');

        AiPrompt::query()->findOrFail($promptId)->delete();
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->editingId   = null;
        $this->name        = '';
        $this->description = '';
        $this->template    = '';
        $this->model       = '';
        $this->isActive    = true;
        $this->resetValidation();
    }

    public function render(): View
    {
        $prompts = AiPrompt::query()
            ->orderBy('name')
            ->get();

        return view('livewire.ai.prompt-manager', ['prompts' => $prompts]);
    }
}
