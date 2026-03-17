<div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('Users') }}</flux:heading>
    </div>

    <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search users...') }}" icon="magnifying-glass" />

    <x-table>
        <x-table.columns>
            <x-table.column>{{ __('Name') }}</x-table.column>
            <x-table.column>{{ __('Email') }}</x-table.column>
            <x-table.column>{{ __('Roles') }}</x-table.column>
            <x-table.column>{{ __('Joined') }}</x-table.column>
            <x-table.column />
        </x-table.columns>
        <x-table.rows>
            @foreach ($users as $user)
                <x-table.row :key="$user->id">
                    <x-table.cell variant="strong">{{ $user->name }}</x-table.cell>
                    <x-table.cell>{{ $user->email }}</x-table.cell>
                    <x-table.cell>
                        <div class="flex flex-wrap gap-1">
                            @foreach ($user->roles as $role)
                                <flux:badge size="sm" color="zinc">{{ $role->name }}</flux:badge>
                            @endforeach
                        </div>
                    </x-table.cell>
                    <x-table.cell>{{ $user->created_at->diffForHumans() }}</x-table.cell>
                    <x-table.cell>
                        <div class="flex gap-2">
                            @can('users.edit')
                                <flux:button size="sm" :href="route('users.edit', $user)" wire:navigate>
                                    {{ __('Edit') }}
                                </flux:button>
                            @endcan
                            @can('users.delete')
                                <flux:button size="sm" variant="danger" wire:click="deleteUser({{ $user->id }})" wire:confirm="{{ __('Are you sure you want to delete this user?') }}">
                                    {{ __('Delete') }}
                                </flux:button>
                            @endcan
                        </div>
                    </x-table.cell>
                </x-table.row>
            @endforeach
        </x-table.rows>
    </x-table>

    <div>{{ $users->links() }}</div>
</div>
