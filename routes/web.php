<?php

use App\Livewire\Ai\PromptManager;
use App\Livewire\Ai\UsageDashboard;
use App\Livewire\ApiKeys\ApiKeyList;
use App\Livewire\Users\UserForm;
use App\Livewire\Users\UserList;
use App\Livewire\Webhooks\WebhookForm;
use App\Livewire\Webhooks\WebhookList;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::post('/locale/{locale}', function (string $locale) {
    $supported = config('app.supported_locales', ['en']);

    if (in_array($locale, $supported)) {
        session(['locale' => $locale]);

        if (auth()->check()) {
            auth()->user()->update(['locale' => $locale]);
        }
    }

    return back();
})->name('locale.switch');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth', 'verified', 'permission:users.view'])->group(function () {
    Route::get('users', UserList::class)->name('users.index');
    Route::get('users/{user}/edit', UserForm::class)->name('users.edit');
});

Route::middleware(['auth', 'verified', 'permission:api-keys.view'])->group(function () {
    Route::get('api-keys', ApiKeyList::class)->name('api-keys.index');
});

Route::middleware(['auth', 'verified', 'permission:ai.manage-prompts'])->group(function () {
    Route::get('ai/prompts', PromptManager::class)->name('ai.prompts');
});

Route::middleware(['auth', 'verified', 'permission:ai.view-usage'])->group(function () {
    Route::get('ai/usage', UsageDashboard::class)->name('ai.usage');
});

Route::middleware(['auth', 'verified', 'permission:webhooks.view'])->group(function () {
    Route::get('webhooks', WebhookList::class)->name('webhooks.index');
    Route::get('webhooks/create', WebhookForm::class)->name('webhooks.create');
    Route::get('webhooks/{endpointId}/edit', WebhookForm::class)->name('webhooks.edit');
});

require __DIR__.'/settings.php';
