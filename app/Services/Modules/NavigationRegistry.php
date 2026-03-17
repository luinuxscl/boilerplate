<?php

namespace App\Services\Modules;

use App\Contracts\Modules\NavigationRegistryContract;

class NavigationRegistry implements NavigationRegistryContract
{
    /** @var array<int, array<string, mixed>> */
    private array $items = [];

    /**
     * @param  array{label: string, route: string, icon: string, group: string, permission?: string}  $item
     */
    public function register(array $item): void
    {
        $this->items[] = $item;
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function grouped(): array
    {
        $grouped = [];

        foreach ($this->items as $item) {
            $group = $item['group'] ?? 'General';
            $grouped[$group][] = $item;
        }

        return $grouped;
    }
}
