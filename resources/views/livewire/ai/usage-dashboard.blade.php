<div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('AI Usage') }}</flux:heading>

        <flux:select wire:model.live="period" size="sm" class="w-32">
            <flux:select.option value="day">{{ __('Today') }}</flux:select.option>
            <flux:select.option value="week">{{ __('This week') }}</flux:select.option>
            <flux:select.option value="month">{{ __('This month') }}</flux:select.option>
        </flux:select>
    </div>

    {{-- Summary cards --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('Requests') }}</flux:text>
            <p class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($summary['requests']) }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('Total tokens') }}</flux:text>
            <p class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($summary['total_tokens']) }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('Est. cost') }}</flux:text>
            <p class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">${{ number_format($summary['cost_usd'], 4) }}</p>
        </div>
    </div>

    {{-- Recent logs table --}}
    <flux:heading size="lg">{{ __('Recent requests') }}</flux:heading>

    <x-table>
        <x-table.columns>
            <x-table.column>{{ __('Model') }}</x-table.column>
            <x-table.column>{{ __('Prompt') }}</x-table.column>
            <x-table.column>{{ __('Tokens') }}</x-table.column>
            <x-table.column>{{ __('Cost') }}</x-table.column>
            <x-table.column>{{ __('Duration') }}</x-table.column>
            <x-table.column>{{ __('When') }}</x-table.column>
        </x-table.columns>
        <x-table.rows>
            @forelse ($recentLogs as $log)
                <x-table.row :key="$log->id">
                    <x-table.cell>
                        <flux:badge size="sm" color="blue">{{ $log->model }}</flux:badge>
                    </x-table.cell>
                    <x-table.cell>
                        @if ($log->prompt)
                            <code class="font-mono text-xs">{{ $log->prompt->name }}</code>
                        @else
                            <span class="text-zinc-400 dark:text-zinc-500">{{ __('raw') }}</span>
                        @endif
                    </x-table.cell>
                    <x-table.cell>{{ number_format($log->total_tokens) }}</x-table.cell>
                    <x-table.cell>${{ number_format($log->cost_usd, 6) }}</x-table.cell>
                    <x-table.cell>{{ $log->request_duration_ms }}ms</x-table.cell>
                    <x-table.cell>{{ $log->created_at->diffForHumans() }}</x-table.cell>
                </x-table.row>
            @empty
                <x-table.row>
                    <x-table.cell colspan="6" class="text-center">{{ __('No usage logs yet.') }}</x-table.cell>
                </x-table.row>
            @endforelse
        </x-table.rows>
    </x-table>
</div>
