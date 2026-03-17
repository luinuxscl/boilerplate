<div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('Webhooks') }}</flux:heading>
        @can('webhooks.create')
            <flux:button variant="primary" size="sm" icon="plus" wire:navigate href="{{ route('webhooks.create') }}">
                {{ __('New webhook') }}
            </flux:button>
        @endcan
    </div>

    <x-table>
        <x-table.columns>
            <x-table.column>{{ __('URL') }}</x-table.column>
            <x-table.column>{{ __('Events') }}</x-table.column>
            <x-table.column>{{ __('Status') }}</x-table.column>
            <x-table.column>{{ __('Failures') }}</x-table.column>
            <x-table.column />
        </x-table.columns>
        <x-table.rows>
            @forelse ($endpoints as $endpoint)
                <x-table.row :key="$endpoint->id">
                    <x-table.cell variant="strong">
                        <span class="max-w-xs truncate font-mono text-sm">{{ $endpoint->url }}</span>
                    </x-table.cell>
                    <x-table.cell>
                        <div class="flex flex-wrap gap-1">
                            @foreach ($endpoint->events as $event)
                                <flux:badge size="sm" color="blue">{{ $event }}</flux:badge>
                            @endforeach
                        </div>
                    </x-table.cell>
                    <x-table.cell>
                        @if ($endpoint->is_active)
                            <flux:badge size="sm" color="green">{{ __('Active') }}</flux:badge>
                        @else
                            <flux:badge size="sm" color="zinc">{{ __('Inactive') }}</flux:badge>
                        @endif
                    </x-table.cell>
                    <x-table.cell>
                        @if ($endpoint->failure_count > 0)
                            <flux:badge size="sm" color="red">{{ $endpoint->failure_count }}</flux:badge>
                        @else
                            <span class="text-zinc-400 dark:text-zinc-500">0</span>
                        @endif
                    </x-table.cell>
                    <x-table.cell>
                        @canany(['webhooks.edit', 'webhooks.delete'])
                            <div class="flex gap-2">
                                @can('webhooks.edit')
                                    <flux:button size="sm" variant="ghost" wire:click="toggleActive('{{ $endpoint->id }}')">
                                        {{ $endpoint->is_active ? __('Disable') : __('Enable') }}
                                    </flux:button>
                                    <flux:button size="sm" variant="ghost" wire:navigate href="{{ route('webhooks.edit', $endpoint->id) }}">
                                        {{ __('Edit') }}
                                    </flux:button>
                                @endcan
                                @can('webhooks.delete')
                                    <flux:button size="sm" variant="danger" wire:click="delete('{{ $endpoint->id }}')" wire:confirm="{{ __('Delete this webhook endpoint?') }}">
                                        {{ __('Delete') }}
                                    </flux:button>
                                @endcan
                            </div>
                        @endcanany
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.row>
                    <x-table.cell colspan="5" class="text-center">{{ __('No webhook endpoints yet.') }}</x-table.cell>
                </x-table.row>
            @endforelse
        </x-table.rows>
    </x-table>
</div>
