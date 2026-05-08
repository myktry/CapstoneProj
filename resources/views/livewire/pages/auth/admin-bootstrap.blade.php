<?php

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
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
        abort_if(User::query()->where('role', 'admin')->exists(), 404);
    }

    public function createAdmin(OtpService $otpService): void
    {
        abort_if(User::query()->where('role', 'admin')->exists(), 404);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $pendingAdmin = [
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'phone' => trim($validated['phone']),
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
        ];

        session()->put('pending_admin_registration', $pendingAdmin);

        try {
            $otpService->issueCode(
                purpose: 'admin_register',
                channel: 'email',
                recipient: $pendingAdmin['email'],
                context: ['stage' => 'admin-bootstrap'],
            );
        } catch (ValidationException $exception) {
            session()->flash('status', 'verification-code-sent');
            $this->redirect(route('admin.register.verify-otp', absolute: false), navigate: false);

            return;
        } catch (\Throwable $throwable) {
            report($throwable);
            session()->forget('pending_admin_registration');

            $this->addError('email', 'Unable to send the verification code right now. Please try again.');

            return;
        }

        session()->flash('status', 'verification-code-sent');

        $this->redirect(route('admin.register.verify-otp', absolute: false), navigate: false);
    }
}; ?>

<div>
    <div class="mb-6">
        <p class="text-xs uppercase tracking-[0.3em] text-amber-300">Create Admin</p>
        <h1 class="mt-2 text-3xl font-semibold text-white">Bootstrap First Admin</h1>
        <p class="mt-2 text-sm text-zinc-400">This form is only available until the first admin account is created.</p>
        <p class="mt-2 text-sm text-amber-200">After you submit your details, we will send a 6-digit OTP to your email address to finish setup.</p>
    </div>

    <form wire:submit.prevent="createAdmin" class="space-y-4">
        <div>
            <x-input-label for="name" :value="__('Name')" class="text-zinc-300" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full border-white/10 bg-zinc-950 text-white placeholder-zinc-500 focus:border-amber-400 focus:ring-amber-400" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" class="text-zinc-300" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full border-white/10 bg-zinc-950 text-white placeholder-zinc-500 focus:border-amber-400 focus:ring-amber-400" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="phone" :value="__('Phone Number')" class="text-zinc-300" />
            <x-text-input wire:model="phone" id="phone" class="block mt-1 w-full border-white/10 bg-zinc-950 text-white placeholder-zinc-500 focus:border-amber-400 focus:ring-amber-400" type="text" name="phone" required autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="text-zinc-300" />
            <x-text-input wire:model="password" id="password" class="block mt-1 w-full border-white/10 bg-zinc-950 text-white placeholder-zinc-500 focus:border-amber-400 focus:ring-amber-400" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-zinc-300" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full border-white/10 bg-zinc-950 text-white placeholder-zinc-500 focus:border-amber-400 focus:ring-amber-400" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="pt-2 flex items-center justify-between gap-3">
            <a class="text-sm text-zinc-400 underline underline-offset-2 hover:text-amber-300 focus:outline-none" href="{{ route('filament.admin.auth.login') }}" wire:navigate>
                {{ __('Back to admin login') }}
            </a>

            <x-primary-button class="!rounded-full !bg-amber-400 !px-6 !py-2 !text-zinc-900 !normal-case !tracking-wide hover:!bg-amber-300 focus:!bg-amber-300 focus:!ring-amber-300">
                {{ __('Create Admin') }}
            </x-primary-button>
        </div>
    </form>
</div>