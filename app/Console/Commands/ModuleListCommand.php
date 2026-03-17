<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ModuleListCommand extends Command
{
    protected $signature = 'module:list';

    protected $description = 'List all module packages under the packages/ directory';

    public function handle(): int
    {
        $packagesDir = base_path('packages');

        if (! is_dir($packagesDir)) {
            $this->line('No packages directory found.');

            return self::SUCCESS;
        }

        $modules = $this->discoverModules($packagesDir);

        if (empty($modules)) {
            $this->line('No modules found.');

            return self::SUCCESS;
        }

        $this->table(
            ['Package', 'Description', 'Path'],
            $modules,
        );

        return self::SUCCESS;
    }

    /** @return array<int, array{string, string, string}> */
    private function discoverModules(string $packagesDir): array
    {
        $modules = [];

        foreach (glob("{$packagesDir}/*/*/composer.json") as $composerFile) {
            $data        = json_decode(file_get_contents($composerFile), true);
            $name        = $data['name'] ?? basename(dirname($composerFile));
            $description = $data['description'] ?? '—';
            $path        = str_replace(base_path().'/', '', dirname($composerFile));

            $modules[] = [$name, $description, $path];
        }

        return $modules;
    }
}
