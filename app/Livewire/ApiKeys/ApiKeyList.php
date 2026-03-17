<?php

namespace App\Livewire\ApiKeys;

use App\Models\ApiKey;
use App\Services\ApiKeys\ApiKeyManager;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('API Keys')]
class ApiKeyList extends Component
{
    use AuthorizesRequests;

    public bool $showCreatedKey = false;
    public string $createdPlain = '';

    public string $newKeyName = '';
    public string $newKeyScopes = '*';
    public int $newKeyRateLimit = 60;

    public function createKey(ApiKeyManager $manager): void
    {
        $this->authorize('api-keys.create');

        $this->validate([
            'newKeyName'      => ['required', 'string', 'min:2', 'max:100'],
            'newKeyRateLimit' => ['required', 'integer', 'min:1', 'max:3600'],
        ]);

        $scopes = array_filter(array_map('trim', explode(',', $this->newKeyScopes)));

        $result = $manager->create(
            user: auth()->user(),
            name: $this->newKeyName,
            scopes: $scopes ?: ['*'],
            rateLimitPerMinute: $this->newKeyRateLimit,
        );

        $this->createdPlain = $result['plain'];
        $this->showCreatedKey = true;
        $this->reset('newKeyName', 'newKeyScopes', 'newKeyRateLimit');
    }

    public function revokeKey(string $keyId, ApiKeyManager $manager): void
    {
        $this->authorize('api-keys.revoke');

        $apiKey = ApiKey::query()->findOrFail($keyId);
        $manager->revoke($apiKey);
    }

    public function dismissCreatedKey(): void
    {
        $this->showCreatedKey = false;
        $this->createdPlain = '';
    }

    public function render(): View
    {
        $apiKeys = ApiKey::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('livewire.api-keys.api-key-list', ['apiKeys' => $apiKeys]);
    }
}
