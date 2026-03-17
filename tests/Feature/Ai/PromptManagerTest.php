<?php

use App\Livewire\Ai\PromptManager;
use App\Models\AiPrompt;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

it('renders the prompt manager for authorized users', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(PromptManager::class)
        ->assertOk();
});

it('lists existing prompts', function (): void {
    $admin = User::factory()->admin()->create();

    AiPrompt::create(['name' => 'prompt-a', 'template' => 'Hello {{name}}', 'is_active' => true]);
    AiPrompt::create(['name' => 'prompt-b', 'template' => 'Goodbye', 'is_active' => false]);

    Livewire::actingAs($admin)
        ->test(PromptManager::class)
        ->assertSee('prompt-a')
        ->assertSee('prompt-b');
});

it('creates a new prompt', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(PromptManager::class)
        ->call('openCreate')
        ->assertSet('showForm', true)
        ->set('name', 'new-prompt')
        ->set('description', 'A test prompt')
        ->set('template', 'Say {{greeting}} to {{name}}.')
        ->call('save')
        ->assertSet('showForm', false);

    expect(AiPrompt::where('name', 'new-prompt')->exists())->toBeTrue();
});

it('validates required fields when creating a prompt', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(PromptManager::class)
        ->call('openCreate')
        ->set('name', '')
        ->set('template', '')
        ->call('save')
        ->assertHasErrors(['name', 'template']);
});

it('validates name format', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(PromptManager::class)
        ->call('openCreate')
        ->set('name', 'Invalid Name!')
        ->set('template', 'test')
        ->call('save')
        ->assertHasErrors(['name']);
});

it('edits an existing prompt', function (): void {
    $admin = User::factory()->admin()->create();

    $prompt = AiPrompt::create([
        'name'      => 'edit-me',
        'template'  => 'Original template',
        'is_active' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(PromptManager::class)
        ->call('openEdit', $prompt->id)
        ->assertSet('name', 'edit-me')
        ->assertSet('template', 'Original template')
        ->set('template', 'Updated template')
        ->call('save');

    expect(AiPrompt::find($prompt->id)->template)->toBe('Updated template');
});

it('deletes a prompt', function (): void {
    $admin = User::factory()->admin()->create();

    $prompt = AiPrompt::create([
        'name'      => 'delete-me',
        'template'  => 'Goodbye',
        'is_active' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(PromptManager::class)
        ->call('delete', $prompt->id);

    expect(AiPrompt::find($prompt->id))->toBeNull();
});

it('denies access to users without permission', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(PromptManager::class)
        ->call('openCreate')
        ->assertForbidden();
});
