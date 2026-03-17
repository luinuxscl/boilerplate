<?php

namespace App\Providers;

use App\Contracts\Modules\NavigationRegistryContract;
use App\Services\Modules\NavigationRegistry;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NavigationRegistryContract::class, NavigationRegistry::class);
    }

    public function boot(): void
    {
        $this->registerCoreNavigation();
    }

    protected function registerCoreNavigation(): void
    {
        $registry = $this->app->make(NavigationRegistryContract::class);

        $registry->register([
            'label'      => 'Dashboard',
            'route'      => 'dashboard',
            'icon'       => 'home',
            'group'      => 'General',
        ]);

        $registry->register([
            'label'      => 'Users',
            'route'      => 'users.index',
            'icon'       => 'users',
            'group'      => 'Administration',
            'permission' => 'users.view',
        ]);

        $registry->register([
            'label'      => 'AI Prompts',
            'route'      => 'ai.prompts',
            'icon'       => 'cpu-chip',
            'group'      => 'AI',
            'permission' => 'ai.manage-prompts',
        ]);

        $registry->register([
            'label'      => 'AI Usage',
            'route'      => 'ai.usage',
            'icon'       => 'chart-bar',
            'group'      => 'AI',
            'permission' => 'ai.view-usage',
        ]);

        $registry->register([
            'label'      => 'Webhooks',
            'route'      => 'webhooks.index',
            'icon'       => 'arrow-path',
            'group'      => 'Developer',
            'permission' => 'webhooks.view',
        ]);
    }
}
