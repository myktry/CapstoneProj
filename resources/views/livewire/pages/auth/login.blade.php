<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $user = $this->form->validateCredentials();

        if ($user->isAdmin()) {
            $this->addError('form.email', 'Admin accounts must sign in at /admin/login.');

            return;
        }

        // Set pending MFA session to trigger challenge before actual login
        session()->put('pending_login_mfa', [
            'user_id' => $user->id,
            'remember' => (bool) $this->form->remember,
        ]);

        $this->redirect(route('login.mfa-challenge', absolute: false), navigate: false);
    }
}; ?>

<div>
    <div class="mb-6">
        <p class="text-xs uppercase tracking-[0.3em] text-amber-300">Welcome Back</p>
        <h1 class="mt-2 text-3xl font-semibold text-white">Sign in</h1>
    </div>

    @if (session('status') === 'mfa-required')
        <div class="mb-4 rounded-lg border border-amber-500/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-200">
            Please complete MFA verification to continue.
        </div>
    @endif

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-4">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="text-zinc-300" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full border-white/10 bg-zinc-950 text-white placeholder-zinc-500 focus:border-amber-400 focus:ring-amber-400" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="text-zinc-300" />

            <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password"
                            class="block mt-1 w-full border-white/10 bg-zinc-950 text-white placeholder-zinc-500 focus:border-amber-400 focus:ring-amber-400" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-white/20 bg-zinc-900 text-amber-400 shadow-sm focus:ring-amber-400" name="remember">
                <span class="ms-2 text-sm text-zinc-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="pt-2 flex flex-wrap items-center justify-between gap-3">
            @if (Route::has('register'))
                <a class="text-sm text-zinc-400 underline underline-offset-2 hover:text-amber-300 focus:outline-none" href="{{ route('register') }}" wire:navigate>
                    {{ __('Create account') }}
                </a>
            @endif

            @if (Route::has('password.request'))
                <a class="text-sm text-zinc-400 underline underline-offset-2 hover:text-amber-300 focus:outline-none" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-auto !rounded-full !bg-amber-400 !px-6 !py-2 !text-zinc-900 !normal-case !tracking-wide hover:!bg-amber-300 focus:!bg-amber-300 focus:!ring-amber-300">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</div>
