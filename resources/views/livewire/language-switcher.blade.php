<flux:dropdown position="bottom" align="end">
    <flux:button
        variant="subtle"
        square
        class="!h-10 [&>div>svg]:size-5"
        :aria-label="__('Language')"
    >
        <flux:icon name="globe" variant="mini" class="text-zinc-500 dark:text-zinc-400" />
    </flux:button>

    <flux:menu>
        @foreach ($locales as $code => $name)
            <flux:menu.item
                wire:click="switchLocale('{{ $code }}')"
                :icon="$currentLocale === $code ? 'check' : null"
                wire:loading.attr="disabled"
                wire:target="switchLocale('{{ $code }}')"
            >
                {{ $name }}
            </flux:menu.item>
        @endforeach
    </flux:menu>
</flux:dropdown>
