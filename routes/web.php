<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth', 'verified', 'permission:users.view'])->group(function () {
    Route::get('users', \App\Livewire\Users\UserList::class)->name('users.index');
    Route::get('users/{user}/edit', \App\Livewire\Users\UserForm::class)->name('users.edit');
});

Route::middleware(['auth', 'verified', 'permission:api-keys.view'])->group(function () {
    Route::get('api-keys', \App\Livewire\ApiKeys\ApiKeyList::class)->name('api-keys.index');
});

Route::middleware(['auth', 'verified', 'permission:ai.manage-prompts'])->group(function () {
    Route::get('ai/prompts', \App\Livewire\Ai\PromptManager::class)->name('ai.prompts');
});

Route::middleware(['auth', 'verified', 'permission:ai.view-usage'])->group(function () {
    Route::get('ai/usage', \App\Livewire\Ai\UsageDashboard::class)->name('ai.usage');
});

Route::middleware(['auth', 'verified', 'permission:webhooks.view'])->group(function () {
    Route::get('webhooks', \App\Livewire\Webhooks\WebhookList::class)->name('webhooks.index');
    Route::get('webhooks/create', \App\Livewire\Webhooks\WebhookForm::class)->name('webhooks.create');
    Route::get('webhooks/{endpointId}/edit', \App\Livewire\Webhooks\WebhookForm::class)->name('webhooks.edit');
});

require __DIR__.'/settings.php';
