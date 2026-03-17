<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {name : Vendor and package name, e.g. acme/my-module}';

    protected $description = 'Scaffold a new boilerplate module package under packages/';

    public function handle(): int
    {
        $name = $this->argument('name');

        if (! preg_match('/^[a-z0-9\-]+\/[a-z0-9\-]+$/', $name)) {
            $this->error('Name must be in vendor/package format using lowercase letters, numbers and hyphens.');

            return self::FAILURE;
        }

        [$vendor, $package] = explode('/', $name);

        $baseDir   = base_path("packages/{$vendor}/{$package}");
        $srcDir    = "{$baseDir}/src";
        $namespace = Str::studly($vendor).'\\'.Str::studly(Str::replace('-', ' ', $package));
        $providerClass = Str::studly(Str::replace('-', ' ', $package)).'ServiceProvider';

        if (is_dir($baseDir)) {
            $this->error("Package directory already exists: packages/{$vendor}/{$package}");

            return self::FAILURE;
        }

        mkdir($srcDir, 0755, true);

        $this->writeComposerJson($baseDir, $name, $namespace, $providerClass);
        $this->writeServiceProvider($srcDir, $namespace, $providerClass);

        $this->info("Module <comment>{$name}</comment> scaffolded at packages/{$vendor}/{$package}/");
        $this->line('');
        $this->line('Next steps:');
        $this->line("  1. Add <comment>\"repositories\"</comment> entry in composer.json if not already present.");
        $this->line("  2. Run <comment>composer require {$name}:*@dev</comment>");
        $this->line("  3. Implement your logic in <comment>packages/{$vendor}/{$package}/src/</comment>");

        return self::SUCCESS;
    }

    private function writeComposerJson(string $baseDir, string $name, string $namespace, string $providerClass): void
    {
        $escapedNamespace = str_replace('\\', '\\\\', $namespace);

        $contents = <<<JSON
        {
            "name": "{$name}",
            "description": "A boilerplate module.",
            "type": "library",
            "license": "MIT",
            "autoload": {
                "psr-4": {
                    "{$escapedNamespace}\\\\": "src/"
                }
            },
            "extra": {
                "laravel": {
                    "providers": [
                        "{$escapedNamespace}\\\\{$providerClass}"
                    ]
                }
            },
            "require": {
                "php": "^8.2"
            }
        }
        JSON;

        file_put_contents("{$baseDir}/composer.json", ltrim($contents));
    }

    private function writeServiceProvider(string $srcDir, string $namespace, string $providerClass): void
    {
        $contents = <<<PHP
        <?php

        namespace {$namespace};

        use App\\Contracts\\Modules\\NavigationRegistryContract;
        use Illuminate\\Support\\ServiceProvider;

        class {$providerClass} extends ServiceProvider
        {
            public function boot(): void
            {
                \$this->registerNavigation();
            }

            protected function registerNavigation(): void
            {
                /** @var NavigationRegistryContract \$registry */
                \$registry = \$this->app->make(NavigationRegistryContract::class);

                // \$registry->register([
                //     'label'      => 'My Page',
                //     'route'      => 'my-module.index',
                //     'icon'       => 'puzzle-piece',
                //     'group'      => 'My Module',
                //     'permission' => 'my-module.view',
                // ]);
            }
        }
        PHP;

        file_put_contents("{$srcDir}/{$providerClass}.php", ltrim($contents));
    }
}
