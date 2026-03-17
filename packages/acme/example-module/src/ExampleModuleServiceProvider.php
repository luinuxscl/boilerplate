<?php

namespace Acme\ExampleModule;

use App\Contracts\Modules\NavigationRegistryContract;
use App\Services\ApiKeys\ScopeRegistry;
use Illuminate\Support\ServiceProvider;

class ExampleModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerNavigation();
        $this->registerScopes();
    }

    protected function registerNavigation(): void
    {
        /** @var NavigationRegistryContract $registry */
        $registry = $this->app->make(NavigationRegistryContract::class);

        $registry->register([
            'label'      => 'Example',
            'route'      => 'example.index',
            'icon'       => 'puzzle-piece',
            'group'      => 'Example Module',
            'permission' => 'example.view',
        ]);
    }

    protected function registerScopes(): void
    {
        /** @var ScopeRegistry $registry */
        $registry = $this->app->make(ScopeRegistry::class);

        $registry->register([
            'example.read'  => ['description' => 'Read example resources', 'group' => 'example'],
            'example.write' => ['description' => 'Create/update example resources', 'group' => 'example'],
        ]);
    }
}
