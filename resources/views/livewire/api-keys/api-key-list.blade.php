<div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('API Keys') }}</flux:heading>
    </div>

    {{-- Created key alert (shown once) --}}
    @if ($showCreatedKey)
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
            <flux:heading size="sm" class="text-green-800 dark:text-green-300">{{ __('API Key Created') }}</flux:heading>
            <flux:text class="mt-1 text-green-700 dark:text-green-400">
                {{ __('Copy this key now. It will not be shown again.') }}
            </flux:text>
            <div class="mt-2 flex items-center gap-2">
                <code class="flex-1 rounded bg-green-100 px-3 py-2 font-mono text-sm text-green-900 dark:bg-green-900/40 dark:text-green-200">{{ $createdPlain }}</code>
                <flux:button size="sm" x-data x-on:click="navigator.clipboard.writeText('{{ $createdPlain }}')">
                    {{ __('Copy') }}
                </flux:button>
            </div>
            <flux:button size="sm" variant="ghost" wire:click="dismissCreatedKey" class="mt-2">
                {{ __('I have copied it') }}
            </flux:button>
        </div>
    @endif

    {{-- Create new key form --}}
    @can('api-keys.create')
        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
            <flux:heading size="sm" class="mb-3">{{ __('Create new API key') }}</flux:heading>
            <form wire:submit="createKey" class="flex flex-col gap-3">
                <div class="grid gap-3 sm:grid-cols-3">
                    <flux:field>
                        <flux:label>{{ __('Name') }}</flux:label>
                        <flux:input wire:model="newKeyName" placeholder="{{ __('My integration') }}" />
                        <flux:error name="newKeyName" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Scopes') }}</flux:label>
                        <flux:input wire:model="newKeyScopes" placeholder="* or profile.read,api-keys.read" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Rate limit / min') }}</flux:label>
                        <flux:input wire:model="newKeyRateLimit" type="number" min="1" max="3600" />
                        <flux:error name="newKeyRateLimit" />
                    </flux:field>
                </div>
                <div>
                    <flux:button type="submit" variant="primary" size="sm">{{ __('Create key') }}</flux:button>
                </div>
            </form>
        </div>
    @endcan

    {{-- Keys table --}}
    <x-table>
        <x-table.columns>
            <x-table.column>{{ __('Name') }}</x-table.column>
            <x-table.column>{{ __('Prefix') }}</x-table.column>
            <x-table.column>{{ __('Scopes') }}</x-table.column>
            <x-table.column>{{ __('Rate limit') }}</x-table.column>
            <x-table.column>{{ __('Last used') }}</x-table.column>
            <x-table.column>{{ __('Status') }}</x-table.column>
            <x-table.column />
        </x-table.columns>
        <x-table.rows>
            @forelse ($apiKeys as $key)
                <x-table.row :key="$key->id">
                    <x-table.cell variant="strong">{{ $key->name }}</x-table.cell>
                    <x-table.cell>
                        <code class="font-mono text-xs">{{ $key->key_prefix }}...</code>
                    </x-table.cell>
                    <x-table.cell>
                        <div class="flex flex-wrap gap-1">
                            @foreach ($key->scopes as $scope)
                                <flux:badge size="sm" color="blue">{{ $scope }}</flux:badge>
                            @endforeach
                        </div>
                    </x-table.cell>
                    <x-table.cell>{{ $key->rate_limit_per_minute }}/min</x-table.cell>
                    <x-table.cell>{{ $key->last_used_at?->diffForHumans() ?? __('Never') }}</x-table.cell>
                    <x-table.cell>
                        @if ($key->is_active && ! $key->isExpired())
                            <flux:badge size="sm" color="green">{{ __('Active') }}</flux:badge>
                        @elseif ($key->isExpired())
                            <flux:badge size="sm" color="red">{{ __('Expired') }}</flux:badge>
                        @else
                            <flux:badge size="sm" color="zinc">{{ __('Revoked') }}</flux:badge>
                        @endif
                    </x-table.cell>
                    <x-table.cell>
                        @can('api-keys.revoke')
                            @if ($key->is_active)
                                <flux:button size="sm" variant="danger" wire:click="revokeKey('{{ $key->id }}')" wire:confirm="{{ __('Revoke this API key? This cannot be undone.') }}">
                                    {{ __('Revoke') }}
                                </flux:button>
                            @endif
                        @endcan
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.row>
                    <x-table.cell colspan="7" class="text-center">{{ __('No API keys yet.') }}</x-table.cell>
                </x-table.row>
            @endforelse
        </x-table.rows>
    </x-table>
</div>
