<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('User Management')]
class UserList extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function deleteUser(int $userId): void
    {
        $this->authorize('users.delete');

        User::query()->findOrFail($userId)->delete();
    }

    public function render(): \Illuminate\View\View
    {
        $users = User::query()
            ->with('roles')
            ->when(
                $this->search,
                fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%"),
            )
            ->latest()
            ->paginate(15);

        return view('livewire.users.user-list', ['users' => $users]);
    }
}
