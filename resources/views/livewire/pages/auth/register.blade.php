<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Services\OtpService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $name_stego_png_base64 = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(OtpService $otpService): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $pendingRegistration = [
            'name' => trim($validated['name']),
            'name_stego_png_base64' => trim((string) $this->name_stego_png_base64),
            'email' => strtolower(trim($validated['email'])),
            'phone' => trim($validated['phone']),
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
        ];

        session()->put('pending_registration', $pendingRegistration);

        $otpService->issueCode(
            purpose: 'register',
            channel: 'email',
            recipient: $pendingRegistration['email'],
            context: ['stage' => 'registration'],
        );

        session()->flash('status', 'verification-code-sent');

        $this->redirect(route('register.verify-otp', absolute: false), navigate: false);
    }
}; ?>

<div>
    <div class="mb-6">
        <p class="text-xs uppercase tracking-[0.3em] text-amber-300">Create Account</p>
        <h1 class="mt-2 text-3xl font-semibold text-white">Join Black Ember</h1>
        <p class="mt-2 text-sm text-zinc-400">This registration creates a customer account only.</p>
        <p class="mt-2 text-sm text-amber-200">After you submit your details, we will send a 6-digit OTP to your email address to complete registration.</p>
    </div>

    <form wire:submit.prevent="register" method="POST" id="registration-form" class="space-y-4">
        <input type="hidden" id="name-stego-png-base64" wire:model.defer="name_stego_png_base64" />
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" class="text-zinc-300" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full border-white/10 bg-zinc-950 text-white placeholder-zinc-500 focus:border-amber-400 focus:ring-amber-400" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" class="text-zinc-300" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full border-white/10 bg-zinc-950 text-white placeholder-zinc-500 focus:border-amber-400 focus:ring-amber-400" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Phone Number -->
        <div class="mt-4">
            <x-input-label for="phone" :value="__('Phone Number')" class="text-zinc-300" />
            <x-text-input wire:model="phone" id="phone" class="block mt-1 w-full border-white/10 bg-zinc-950 text-white placeholder-zinc-500 focus:border-amber-400 focus:ring-amber-400" type="text" name="phone" required autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="text-zinc-300" />

            <x-text-input wire:model="password" id="password" class="block mt-1 w-full border-white/10 bg-zinc-950 text-white placeholder-zinc-500 focus:border-amber-400 focus:ring-amber-400"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-zinc-300" />

            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full border-white/10 bg-zinc-950 text-white placeholder-zinc-500 focus:border-amber-400 focus:ring-amber-400"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="pt-2 flex items-center justify-between gap-3">
            <a class="text-sm text-zinc-400 underline underline-offset-2 hover:text-amber-300 focus:outline-none" href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>

            <x-input-error :messages="$errors->get('otp')" class="mt-2 text-right" />

            <x-primary-button
                type="button"
                x-data="{ busy: false }"
                x-on:click.prevent="
                    if (busy) return;
                    busy = true;
                    try {
                        const name = (document.getElementById('name')?.value || '').trim();
                        if (name && window.StegoDemo) {
                            try {
                                const cover = window.StegoDemo.createCoverImageLike({ width: 300, height: 300 });
                                const encoded = await window.StegoDemo.hideUserDataInImageLike(cover, { name });
                                const pngBase64 = window.StegoDemo.imageLikeToPngBase64(encoded);
                                const input = document.getElementById('name-stego-png-base64');
                                if (input) {
                                    input.value = pngBase64;
                                    input.dispatchEvent(new Event('input', { bubbles: true }));
                                }
                            } catch (error) {
                                console.warn('Stego payload generation failed; continuing registration without it.', error);
                            }
                        }
                    } finally {
                        document.getElementById('registration-form')?.requestSubmit();
                        busy = false;
                    }
                "
                class="!rounded-full !bg-amber-400 !px-6 !py-2 !text-zinc-900 !normal-case !tracking-wide hover:!bg-amber-300 focus:!bg-amber-300 focus:!ring-amber-300"
            >
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</div>
