@props(['align' => 'start'])

@php
$alignClass = match ($align) {
    'center' => 'text-center',
    'end' => 'text-end',
    default => 'text-start',
};
@endphp

<th {{ $attributes->class("py-3 px-3 first:ps-0 last:pe-0 text-sm font-medium text-zinc-800 dark:text-white border-b border-zinc-800/10 dark:border-white/20 {$alignClass}") }}>
    {{ $slot }}
</th>
