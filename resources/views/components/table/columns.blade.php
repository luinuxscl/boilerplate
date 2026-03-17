@props(['sticky' => false])

<thead {{ $attributes->class($sticky ? 'sticky top-0 z-20' : '') }}>
    <tr>{{ $slot }}</tr>
</thead>
