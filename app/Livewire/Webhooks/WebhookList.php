<?php

namespace App\Livewire\Webhooks;

use App\Models\WebhookEndpoint;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Webhooks')]
class WebhookList extends Component
{
    use AuthorizesRequests;

    public function delete(string $endpointId): void
    {
        $this->authorize('webhooks.delete');

        WebhookEndpoint::query()->findOrFail($endpointId)->delete();
    }

    public function toggleActive(string $endpointId): void
    {
        $this->authorize('webhooks.edit');

        $endpoint = WebhookEndpoint::query()->findOrFail($endpointId);
        $endpoint->update(['is_active' => ! $endpoint->is_active]);
    }

    public function render(): View
    {
        $endpoints = WebhookEndpoint::query()
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.webhooks.webhook-list', ['endpoints' => $endpoints]);
    }
}
