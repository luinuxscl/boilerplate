@props(['paginate' => null])

<div class="flex flex-col">
    <div class="overflow-auto">
        <table {{ $attributes->class('min-w-full table-fixed border-separate border-spacing-0 text-sm') }}>
            {{ $slot }}
        </table>
    </div>

    @if ($paginate)
        <div class="mt-4">
            {{ $paginate->links() }}
        </div>
    @endif
</div>
