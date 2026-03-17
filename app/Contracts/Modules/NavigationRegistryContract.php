<?php

namespace App\Contracts\Modules;

interface NavigationRegistryContract
{
    /**
     * Register a navigation item.
     *
     * @param  array{label: string, route: string, icon: string, group: string, permission?: string}  $item
     */
    public function register(array $item): void;

    /**
     * All registered items, grouped by group key.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function grouped(): array;
}
