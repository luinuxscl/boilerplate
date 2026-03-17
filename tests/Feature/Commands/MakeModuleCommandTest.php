<?php

use Illuminate\Support\Facades\File;

afterEach(function (): void {
    // Clean up any modules created during tests
    File::deleteDirectory(base_path('packages/test-vendor'));
});

it('scaffolds a new module package', function (): void {
    $this->artisan('make:module test-vendor/my-module')
        ->assertSuccessful();

    $baseDir = base_path('packages/test-vendor/my-module');

    expect(is_dir($baseDir))->toBeTrue()
        ->and(file_exists("{$baseDir}/composer.json"))->toBeTrue()
        ->and(file_exists("{$baseDir}/src/MyModuleServiceProvider.php"))->toBeTrue();
});

it('composer.json contains the correct package name', function (): void {
    $this->artisan('make:module test-vendor/my-module')->assertSuccessful();

    $composerJson = json_decode(
        file_get_contents(base_path('packages/test-vendor/my-module/composer.json')),
        true,
    );

    expect($composerJson['name'])->toBe('test-vendor/my-module');
});

it('service provider has the correct namespace', function (): void {
    $this->artisan('make:module test-vendor/my-module')->assertSuccessful();

    $providerContent = file_get_contents(
        base_path('packages/test-vendor/my-module/src/MyModuleServiceProvider.php')
    );

    expect($providerContent)
        ->toContain('namespace TestVendor\MyModule')
        ->toContain('class MyModuleServiceProvider');
});

it('fails with invalid name format', function (): void {
    $this->artisan('make:module InvalidName')
        ->assertFailed();
});

it('fails if package already exists', function (): void {
    $this->artisan('make:module test-vendor/my-module')->assertSuccessful();
    $this->artisan('make:module test-vendor/my-module')->assertFailed();
});

it('module:list shows discovered packages', function (): void {
    $this->artisan('module:list')
        ->assertSuccessful()
        ->expectsOutputToContain('acme/example-module');
});
