<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Livewire\Attributes\Computed;
use Livewire\Component;

class AdminBootstrap extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        if ($this->adminCount > 0) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $user = auth()->user();

        if ($user) {
            $this->name = (string) ($user->name ?? '');
            $this->email = (string) ($user->email ?? '');
        }
    }

    #[Computed]
    public function adminCount(): int
    {
        return User::query()
            ->where('role', 'admin')
            ->count();
    }

    #[Computed]
    public function canBootstrap(): bool
    {
        return $this->adminCount === 0;
    }

    public function createAdmin(): void
    {
        if (! $this->canBootstrap) {
            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
        ]);

        session()->flash('admin-bootstrap-success', 'Admin account created successfully.');

        $this->reset(['name', 'email', 'password', 'password_confirmation']);
    }

    public function render()
    {
        return view('livewire.admin.admin-bootstrap');
    }
}