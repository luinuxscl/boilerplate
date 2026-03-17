<div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('AI Prompts') }}</flux:heading>
        @can('ai.manage-prompts')
            @if (! $showForm)
                <flux:button variant="primary" size="sm" icon="plus" wire:click="openCreate">
                    {{ __('New prompt') }}
                </flux:button>
            @endif
        @endcan
    </div>

    {{-- Create / Edit form --}}
    @if ($showForm)
        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
            <flux:heading size="sm" class="mb-4">
                {{ $editingId ? __('Edit prompt') : __('Create prompt') }}
            </flux:heading>

            <form wire:submit="save" class="flex flex-col gap-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>{{ __('Name') }}</flux:label>
                        <flux:input wire:model="name" placeholder="my-prompt-slug" />
                        <flux:description>{{ __('Lowercase letters, numbers, hyphens and underscores.') }}</flux:description>
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Description') }}</flux:label>
                        <flux:input wire:model="description" placeholder="{{ __('Optional description') }}" />
                        <flux:error name="description" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>{{ __('Template') }}</flux:label>
                    <flux:textarea wire:model="template" rows="4" placeholder="Use @{{variable}} for placeholders" />
                    <flux:error name="template" />
                </flux:field>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>{{ __('Model override') }}</flux:label>
                        <flux:input wire:model="model" placeholder="{{ __('e.g. openai/gpt-4o (optional)') }}" />
                        <flux:error name="model" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Active') }}</flux:label>
                        <flux:switch wire:model="isActive" />
                    </flux:field>
                </div>

                <div class="flex gap-2">
                    <flux:button type="submit" variant="primary" size="sm">{{ __('Save') }}</flux:button>
                    <flux:button type="button" variant="ghost" size="sm" wire:click="cancelForm">{{ __('Cancel') }}</flux:button>
                </div>
            </form>
        </div>
    @endif

    {{-- Prompts table --}}
    <x-table>
        <x-table.columns>
            <x-table.column>{{ __('Name') }}</x-table.column>
            <x-table.column>{{ __('Description') }}</x-table.column>
            <x-table.column>{{ __('Model') }}</x-table.column>
            <x-table.column>{{ __('Status') }}</x-table.column>
            <x-table.column />
        </x-table.columns>
        <x-table.rows>
            @forelse ($prompts as $prompt)
                <x-table.row :key="$prompt->id">
                    <x-table.cell variant="strong">
                        <code class="font-mono text-sm">{{ $prompt->name }}</code>
                    </x-table.cell>
                    <x-table.cell>{{ $prompt->description ?? '—' }}</x-table.cell>
                    <x-table.cell>
                        @if ($prompt->model)
                            <flux:badge size="sm" color="blue">{{ $prompt->model }}</flux:badge>
                        @else
                            <span class="text-zinc-400 dark:text-zinc-500">{{ __('default') }}</span>
                        @endif
                    </x-table.cell>
                    <x-table.cell>
                        @if ($prompt->is_active)
                            <flux:badge size="sm" color="green">{{ __('Active') }}</flux:badge>
                        @else
                            <flux:badge size="sm" color="zinc">{{ __('Inactive') }}</flux:badge>
                        @endif
                    </x-table.cell>
                    <x-table.cell>
                        @can('ai.manage-prompts')
                            <div class="flex gap-2">
                                <flux:button size="sm" variant="ghost" wire:click="openEdit('{{ $prompt->id }}')">
                                    {{ __('Edit') }}
                                </flux:button>
                                <flux:button size="sm" variant="danger" wire:click="delete('{{ $prompt->id }}')" wire:confirm="{{ __('Delete this prompt?') }}">
                                    {{ __('Delete') }}
                                </flux:button>
                            </div>
                        @endcan
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.row>
                    <x-table.cell colspan="5" class="text-center">{{ __('No prompts yet.') }}</x-table.cell>
                </x-table.row>
            @endforelse
        </x-table.rows>
    </x-table>
</div>
