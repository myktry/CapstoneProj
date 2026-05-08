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
    public string $otpChannel = 'email';

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
            'otpChannel' => ['required', 'in:email,sms'],
        ]);

        $pendingRegistration = [
            'name' => trim($validated['name']),
            'name_stego_png_base64' => trim((string) $this->name_stego_png_base64),
            'email' => strtolower(trim($validated['email'])),
            'phone' => trim($validated['phone']),
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
            'otp_channel' => $validated['otpChannel'],
        ];

        session()->put('pending_registration', $pendingRegistration);

        $recipient = $validated['otpChannel'] === 'sms'
            ? trim($validated['phone'])
            : $pendingRegistration['email'];

        try {
            $otpService->issueCode(
                purpose: 'register',
                channel: $validated['otpChannel'],
                recipient: $recipient,
                context: ['stage' => 'registration'],
            );
        } catch (\Throwable $throwable) {
            report($throwable);
            $this->addError('otpChannel', $validated['otpChannel'] === 'email'
                ? 'Email verification is unavailable. Please use SMS instead.'
                : 'SMS verification is unavailable. Please use email instead.');
            return;
        }

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

        <!-- OTP Channel Selection -->
        <div class="mt-6 p-4 rounded-lg border border-amber-500/20 bg-amber-500/10">
            <x-input-label :value="__('How would you like to verify your account?')" class="text-amber-200 font-semibold mb-3 block" />
            <div class="space-y-3">
                <label class="flex items-center cursor-pointer group">
                    <input 
                        wire:model="otpChannel" 
                        type="radio" 
                        value="email" 
                        class="rounded border-white/20 bg-zinc-900 text-amber-400 shadow-sm focus:ring-amber-400"
                    />
                    <span class="ms-3 text-sm text-zinc-300 group-hover:text-amber-300">
                        <span class="font-semibold">Email</span> - Get a code sent to your email
                    </span>
                </label>
                <label class="flex items-center cursor-pointer group">
                    <input 
                        wire:model="otpChannel" 
                        type="radio" 
                        value="sms" 
                        class="rounded border-white/20 bg-zinc-900 text-amber-400 shadow-sm focus:ring-amber-400"
                    />
                    <span class="ms-3 text-sm text-zinc-300 group-hover:text-amber-300">
                        <span class="font-semibold">SMS</span> - Get a code sent to your phone
                    </span>
                </label>
            </div>
            <x-input-error :messages="$errors->get('otpChannel')" class="mt-3" />
        </div>

        <div class="pt-2 flex items-center justify-between gap-3">
            <a class="text-sm text-zinc-400 underline underline-offset-2 hover:text-amber-300 focus:outline-none" href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>

            <x-input-error :messages="$errors->get('otp')" class="mt-2 text-right" />

            <x-primary-button
                type="submit"
                class="!rounded-full !bg-amber-400 !px-6 !py-2 !text-zinc-900 !normal-case !tracking-wide hover:!bg-amber-300 focus:!bg-amber-300 focus:!ring-amber-300"
            >
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</div>
