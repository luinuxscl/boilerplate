<?php

namespace App\Livewire\Users;

use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Title('Edit User')]
class UserForm extends Component
{
    use ProfileValidationRules;

    public User $user;

    public string $name = '';

    public string $email = '';

    /** @var list<string> */
    public array $selectedRoles = [];

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
    }

    public function save(): void
    {
        $this->authorize('users.edit');

        $validated = $this->validate($this->profileRules($this->user->id));

        $this->user->update($validated);
        $this->user->syncRoles($this->selectedRoles);

        $this->dispatch('user-updated');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.users.user-form', [
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }
}
