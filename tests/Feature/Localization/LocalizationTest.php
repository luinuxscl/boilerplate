<?php

use App\Livewire\LanguageSwitcher;
use App\Models\User;
use Livewire\Livewire;

describe('SetLocale middleware', function () {
    test('uses locale from authenticated user model', function () {
        $user = User::factory()->create(['locale' => 'es']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();

        expect(app()->getLocale())->toBe('es');
    });

    test('uses locale from session for guests', function () {
        $this->withSession(['locale' => 'es'])
            ->get('/')
            ->assertOk();

        expect(app()->getLocale())->toBe('es');
    });

    test('falls back to default app locale when no preference is set', function () {
        $this->get('/')->assertOk();

        expect(app()->getLocale())->toBe(config('app.locale'));
    });

    test('user model locale takes priority over session', function () {
        $user = User::factory()->create(['locale' => 'es']);

        $this->actingAs($user)
            ->withSession(['locale' => 'en'])
            ->get(route('dashboard'))
            ->assertOk();

        expect(app()->getLocale())->toBe('es');
    });

    test('ignores unsupported locale in session', function () {
        $this->withSession(['locale' => 'fr'])
            ->get('/')
            ->assertOk();

        expect(app()->getLocale())->toBe(config('app.locale'));
    });
});

describe('LanguageSwitcher component', function () {
    test('mounts with current locale and supported locales', function () {
        Livewire::test(LanguageSwitcher::class)
            ->assertSet('currentLocale', config('app.locale'))
            ->assertSet('locales', config('app.locale_names'));
    });

    test('guest can switch locale via session', function () {
        Livewire::test(LanguageSwitcher::class)
            ->call('switchLocale', 'es');

        expect(session('locale'))->toBe('es');
    });

    test('authenticated user locale is persisted to database', function () {
        $user = User::factory()->create(['locale' => 'en']);

        Livewire::actingAs($user)
            ->test(LanguageSwitcher::class)
            ->call('switchLocale', 'es');

        expect($user->fresh()->locale)->toBe('es');
        expect(session('locale'))->toBe('es');
    });

    test('unsupported locale is rejected', function () {
        $user = User::factory()->create(['locale' => 'en']);

        Livewire::actingAs($user)
            ->test(LanguageSwitcher::class)
            ->call('switchLocale', 'fr');

        expect($user->fresh()->locale)->toBe('en');
        expect(session('locale'))->toBeNull();
    });
});

describe('User model', function () {
    test('locale is fillable', function () {
        $user = User::factory()->create(['locale' => 'es']);

        expect($user->locale)->toBe('es');
    });

    test('locale defaults to en', function () {
        $user = User::factory()->create();

        expect($user->locale)->toBe('en');
    });
});
