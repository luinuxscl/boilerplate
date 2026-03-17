<div class="flex h-full w-full flex-1 flex-col gap-4 p-4 max-w-2xl">
    <div class="flex items-center gap-2">
        <flux:button variant="ghost" size="sm" :href="route('users.index')" wire:navigate icon="arrow-left">
            {{ __('Back') }}
        </flux:button>
        <flux:heading size="xl">{{ __('Edit User') }}</flux:heading>
    </div>

    <form wire:submit="save" class="flex flex-col gap-4">
        <flux:field>
            <flux:label>{{ __('Name') }}</flux:label>
            <flux:input wire:model="name" />
            <flux:error name="name" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Email') }}</flux:label>
            <flux:input wire:model="email" type="email" />
            <flux:error name="email" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Roles') }}</flux:label>
            <div class="flex flex-wrap gap-2">
                @foreach ($roles as $role)
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <flux:checkbox wire:model="selectedRoles" value="{{ $role->name }}" />
                        <span class="text-sm">{{ $role->name }}</span>
                    </label>
                @endforeach
            </div>
            <flux:error name="selectedRoles" />
        </flux:field>

        <div class="flex gap-2">
            <flux:button type="submit" variant="primary">{{ __('Save changes') }}</flux:button>
        </div>
    </form>
</div>
