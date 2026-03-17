<?php

namespace App\Providers;

use App\Models\User;
use App\Services\ApiKeys\ScopeRegistry;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ScopeRegistry::class, function (): ScopeRegistry {
            $registry = new ScopeRegistry();
            $registry->register([
                '*'              => ['description' => 'Full access', 'group' => 'global'],
                'profile.read'   => ['description' => 'Read user profile', 'group' => 'profile'],
                'api-keys.read'  => ['description' => 'List API keys', 'group' => 'api-keys'],
                'api-keys.write' => ['description' => 'Create/revoke API keys', 'group' => 'api-keys'],
            ]);

            return $registry;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureGate();
    }

    /**
     * Grant super-admin unrestricted access to all gates.
     */
    protected function configureGate(): void
    {
        Gate::before(function (User $user, string $ability): ?bool {
            if ($user->hasRole('super-admin')) {
                return true;
            }

            return null;
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
