<?php

use App\Livewire\Ai\UsageDashboard;
use App\Models\AiUsageLog;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

it('renders the usage dashboard for authorized users', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(UsageDashboard::class)
        ->assertOk()
        ->assertSee('AI Usage');
});

it('shows summary cards with token counts', function (): void {
    $admin = User::factory()->admin()->create();

    AiUsageLog::factory()->count(5)->create([
        'user_id'      => $admin->id,
        'total_tokens' => 100,
    ]);

    Livewire::actingAs($admin)
        ->test(UsageDashboard::class)
        ->assertSee('500');
});

it('shows recent logs in the table', function (): void {
    $admin = User::factory()->admin()->create();

    AiUsageLog::factory()->create([
        'user_id' => $admin->id,
        'model'   => 'openai/gpt-4o-mini',
    ]);

    Livewire::actingAs($admin)
        ->test(UsageDashboard::class)
        ->assertSee('openai/gpt-4o-mini');
});

it('shows empty state when no logs', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(UsageDashboard::class)
        ->assertSee('No usage logs yet.');
});

it('filters summary by period', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(UsageDashboard::class)
        ->assertSet('period', 'day')
        ->set('period', 'week')
        ->assertSet('period', 'week');
});

it('only shows logs belonging to the authenticated user', function (): void {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->create();

    AiUsageLog::factory()->create([
        'user_id' => $other->id,
        'model'   => 'anthropic/claude-3-haiku',
    ]);

    Livewire::actingAs($admin)
        ->test(UsageDashboard::class)
        ->assertDontSee('anthropic/claude-3-haiku');
});
