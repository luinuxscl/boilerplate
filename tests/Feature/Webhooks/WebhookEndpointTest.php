<?php

use App\Livewire\Webhooks\WebhookList;
use App\Models\User;
use App\Models\WebhookEndpoint;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

it('renders the webhook list for authorized users', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(WebhookList::class)
        ->assertOk()
        ->assertSee('Webhooks');
});

it('only shows the authenticated user\'s endpoints', function (): void {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->create();

    WebhookEndpoint::factory()->for($other)->withEvents(['order.created'])->create([
        'url' => 'https://other-user.example.com/hook',
    ]);

    WebhookEndpoint::factory()->for($admin)->withEvents(['order.created'])->create([
        'url' => 'https://my.example.com/hook',
    ]);

    Livewire::actingAs($admin)
        ->test(WebhookList::class)
        ->assertSee('https://my.example.com/hook')
        ->assertDontSee('https://other-user.example.com/hook');
});

it('shows empty state when no endpoints', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(WebhookList::class)
        ->assertSee('No webhook endpoints yet.');
});

it('can delete an endpoint', function (): void {
    $admin    = User::factory()->admin()->create();
    $endpoint = WebhookEndpoint::factory()->for($admin)->create();

    Livewire::actingAs($admin)
        ->test(WebhookList::class)
        ->call('delete', $endpoint->id);

    expect(WebhookEndpoint::find($endpoint->id))->toBeNull();
});

it('can toggle an endpoint active state', function (): void {
    $admin    = User::factory()->admin()->create();
    $endpoint = WebhookEndpoint::factory()->for($admin)->create(['is_active' => true]);

    Livewire::actingAs($admin)
        ->test(WebhookList::class)
        ->call('toggleActive', $endpoint->id);

    expect($endpoint->fresh()->is_active)->toBeFalse();
});

it('denies delete to users without permission', function (): void {
    $user     = User::factory()->create();
    $endpoint = WebhookEndpoint::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(WebhookList::class)
        ->call('delete', $endpoint->id)
        ->assertForbidden();
});
