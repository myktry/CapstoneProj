<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        // Check if an admin already exists
        abort_if(User::query()->where('role', 'admin')->exists(), 403, 'An admin account already exists.');
    }

    public function registerAdmin(): void
    {
        // Double-check that no admin exists
        abort_if(User::query()->where('role', 'admin')->exists(), 403, 'An admin account already exists.');

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $admin = User::query()->create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'phone' => trim($validated['phone']),
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        Auth::login($admin);
        session()->regenerate();

        $this->redirect('/admin', navigate: false);
    }
}; ?>

<div>
    <div class="mb-6">
        <p class="text-xs uppercase tracking-[0.3em] text-amber-300">Create Admin</p>
        <h1 class="mt-2 text-3xl font-semibold text-white">Admin Registration</h1>
        <p class="mt-2 text-sm text-zinc-400">Create the administrator account for Black Ember.</p>
        <p class="mt-2 text-sm text-amber-200">Only one admin account can exist in the system. Once created, this form will no longer be accessible.</p>
    </div>

    <form wire:submit.prevent="registerAdmin" class="space-y-4">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Full Name')" class="text-zinc-300" />
            <x-text-input
                wire:model="name"
                id="name"
                class="block mt-1 w-full border-white/10 bg-zinc-950 text-white placeholder-zinc-500 focus:border-amber-400 focus:ring-amber-400"
                type="text"
                name="name"
                required
                autofocus
                autocomplete="name"
                placeholder="Enter your full name"
            />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email Address')" class="text-zinc-300" />
            <x-text-input
                wire:model="email"
                id="email"
                class="block mt-1 w-full border-white/10 bg-zinc-950 text-white placeholder-zinc-500 focus:border-amber-400 focus:ring-amber-400"
                type="email"
                name="email"
                required
                autocomplete="username"
                placeholder="admin@example.com"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Phone Number -->
        <div class="mt-4">
            <x-input-label for="phone" :value="__('Phone Number')" class="text-zinc-300" />
            <x-text-input
                wire:model="phone"
                id="phone"
                class="block mt-1 w-full border-white/10 bg-zinc-950 text-white placeholder-zinc-500 focus:border-amber-400 focus:ring-amber-400"
                type="text"
                name="phone"
                required
                autocomplete="tel"
                placeholder="+63 900 000 0000"
            />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="text-zinc-300" />
            <x-text-input
                wire:model="password"
                id="password"
                class="block mt-1 w-full border-white/10 bg-zinc-950 text-white placeholder-zinc-500 focus:border-amber-400 focus:ring-amber-400"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="Enter a strong password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-zinc-300" />
            <x-text-input
                wire:model="password_confirmation"
                id="password_confirmation"
                class="block mt-1 w-full border-white/10 bg-zinc-950 text-white placeholder-zinc-500 focus:border-amber-400 focus:ring-amber-400"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Re-enter your password"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="pt-4 flex items-center justify-between gap-3">
            <a class="text-sm text-zinc-400 underline underline-offset-2 hover:text-amber-300 focus:outline-none" href="{{ route('admin.login') }}" wire:navigate>
                {{ __('Back to Admin Login') }}
            </a>

            <x-primary-button class="!rounded-full !bg-amber-400 !px-6 !py-2 !text-zinc-900 !normal-case !tracking-wide hover:!bg-amber-300 focus:!bg-amber-300 focus:!ring-amber-300">
                {{ __('Create Admin Account') }}
            </x-primary-button>
        </div>
    </form>
</div>
