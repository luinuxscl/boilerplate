@props(['key' => null])

<tr @if ($key) wire:key="table-{{ $key }}" @endif {{ $attributes->class('hover:bg-zinc-50 dark:hover:bg-zinc-700/30') }}>
    {{ $slot }}
</tr>
