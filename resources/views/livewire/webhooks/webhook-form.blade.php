<div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
    <flux:heading size="xl">
        {{ $endpointId ? __('Edit webhook') : __('New webhook') }}
    </flux:heading>

    <div class="max-w-xl rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
        <form wire:submit="save" class="flex flex-col gap-4">
            <flux:field>
                <flux:label>{{ __('URL') }}</flux:label>
                <flux:input wire:model="url" type="url" placeholder="https://example.com/webhook" />
                <flux:error name="url" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Events') }}</flux:label>
                <flux:textarea wire:model="eventsInput" rows="4" placeholder="order.created&#10;order.updated&#10;user.registered" />
                <flux:description>{{ __('One event name per line. Use * to receive all events.') }}</flux:description>
                <flux:error name="eventsInput" />
            </flux:field>

            <div class="flex gap-2">
                <flux:button type="submit" variant="primary" size="sm">{{ __('Save') }}</flux:button>
                <flux:button type="button" variant="ghost" size="sm" wire:navigate href="{{ route('webhooks.index') }}">
                    {{ __('Cancel') }}
                </flux:button>
            </div>
        </form>
    </div>
</div>
