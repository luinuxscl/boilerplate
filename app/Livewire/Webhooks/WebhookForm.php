<?php

namespace App\Livewire\Webhooks;

use App\Models\WebhookEndpoint;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Webhook')]
class WebhookForm extends Component
{
    use AuthorizesRequests;

    public ?string $endpointId = null;
    public string $url         = '';
    public string $eventsInput = '';

    public function mount(?string $endpointId = null): void
    {
        if ($endpointId !== null) {
            $endpoint = WebhookEndpoint::query()
                ->where('user_id', auth()->id())
                ->findOrFail($endpointId);

            $this->endpointId  = $endpoint->id;
            $this->url         = $endpoint->url;
            $this->eventsInput = implode("\n", $endpoint->events);
        }
    }

    public function save(): void
    {
        if ($this->endpointId !== null) {
            $this->authorize('webhooks.edit');
        } else {
            $this->authorize('webhooks.create');
        }

        $validated = $this->validate([
            'url'         => ['required', 'url', 'max:500'],
            'eventsInput' => ['required', 'string'],
        ]);

        $events = array_values(array_filter(
            array_map('trim', explode("\n", $validated['eventsInput']))
        ));

        if ($this->endpointId !== null) {
            WebhookEndpoint::query()
                ->where('user_id', auth()->id())
                ->findOrFail($this->endpointId)
                ->update(['url' => $validated['url'], 'events' => $events]);
        } else {
            WebhookEndpoint::create([
                'user_id' => auth()->id(),
                'url'     => $validated['url'],
                'events'  => $events,
                'secret'  => Str::random(64),
            ]);
        }

        $this->redirect(route('webhooks.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.webhooks.webhook-form');
    }
}
