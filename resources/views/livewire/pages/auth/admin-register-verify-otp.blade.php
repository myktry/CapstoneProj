<?php

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $otp = '';

    public function mount(): void
    {
        if (! session()->has('pending_admin_registration')) {
            $this->redirect(route('admin.register', absolute: false), navigate: false);

            return;
        }
    }

    public function verify(OtpService $otpService): void
    {
        $pending = session('pending_admin_registration');

        if (! is_array($pending) || empty($pending['email'])) {
            $this->redirect(route('admin.register', absolute: false), navigate: false);

            return;
        }

        $this->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $channel = $pending['otp_channel'] ?? 'email';
        $recipient = $channel === 'sms' ? $pending['phone'] : $pending['email'];

        $isValid = $otpService->verifyCode(
            purpose: 'admin_register',
            channel: $channel,
            recipient: $recipient,
            code: $this->otp,
        );

        if (! $isValid) {
            $this->addError('otp', 'Invalid or expired verification code.');

            return;
        }

        if (User::query()->where('email', $pending['email'])->exists()) {
            session()->forget('pending_admin_registration');
            $this->addError('otp', 'This email address is already registered. Please login instead.');

            return;
        }

        $admin = User::query()->create([
            'name' => $pending['name'],
            'email' => $pending['email'],
            'phone' => $pending['phone'],
            'password' => $pending['password'],
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        event(new Registered($admin));

        Auth::login($admin);
        session()->forget('pending_admin_registration');
        session()->regenerate();

        $this->redirect('/admin', navigate: false);
    }

    public function resend(OtpService $otpService): void
    {
        $pending = session('pending_admin_registration');

        if (! is_array($pending) || empty($pending['email'])) {
            $this->redirect(route('admin.register', absolute: false), navigate: false);

            return;
        }

        try {
            $otpService->issueCode(
                purpose: 'admin_register',
                channel: $pending['otp_channel'] ?? 'email',
                recipient: ($pending['otp_channel'] ?? 'email') === 'sms' ? $pending['phone'] : $pending['email'],
                context: ['stage' => 'admin-registration-resend', 'method' => $pending['otp_channel'] ?? 'email'],
            );
        } catch (\Throwable $throwable) {
            report($throwable);

            $this->addError('otp', 'Unable to resend the verification code right now. Please try again later.');

            return;
        }

        session()->flash('status', 'verification-code-sent');
    }
}; ?>

<div>
    <div class="mb-6">
        <p class="text-xs uppercase tracking-[0.3em] text-amber-300">Admin Verification</p>
        <h1 class="mt-2 text-3xl font-semibold text-white">Confirm Your Email Code</h1>
        <p class="mt-2 text-sm text-zinc-400">We sent a 6-digit OTP by email or SMS to finish admin registration.</p>
    </div>

    @if (session('status') === 'verification-code-sent')
        <div class="mb-4 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            Verification code sent.
        </div>
    @endif

    <form wire:submit="verify" class="space-y-4">
        <div>
            <x-input-label for="otp" :value="__('Verification Code')" class="text-zinc-300" />
            <x-text-input
                wire:model="otp"
                id="otp"
                class="block mt-1 w-full border-white/10 bg-zinc-950 text-center text-2xl tracking-[0.45em] text-white placeholder-zinc-500 focus:border-amber-400 focus:ring-amber-400"
                type="text"
                name="otp"
                inputmode="numeric"
                maxlength="6"
                required
            />
            <x-input-error :messages="$errors->get('otp')" class="mt-2" />
        </div>

        <div class="flex justify-end">
            <x-secondary-button type="button" wire:click="resend" class="!rounded-full !border-white/15 !bg-zinc-800 !text-zinc-200 hover:!bg-zinc-700">
                Resend code
            </x-secondary-button>
        </div>

        <div class="pt-2 flex items-center justify-between gap-3">
            <a class="text-sm text-zinc-400 underline underline-offset-2 hover:text-amber-300 focus:outline-none" href="{{ route('admin.register') }}" wire:navigate>
                Back to registration
            </a>

            <x-primary-button class="!rounded-full !bg-amber-400 !px-6 !py-2 !text-zinc-900 !normal-case !tracking-wide hover:!bg-amber-300 focus:!bg-amber-300 focus:!ring-amber-300">
                Verify and create admin
            </x-primary-button>
        </div>
    </form>
</div>
