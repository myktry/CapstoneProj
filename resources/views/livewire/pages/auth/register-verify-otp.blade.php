<?php

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Str;

new #[Layout('layouts.guest')] class extends Component
{
    public string $otp = '';

    public function mount(): void
    {
        if (! session()->has('pending_registration')) {
            $this->redirect(route('register', absolute: false), navigate: false);

            return;
        }
    }

    public function verify(OtpService $otpService): void
    {
        $pending = session('pending_registration');

        if (! is_array($pending) || empty($pending['email'])) {
            $this->redirect(route('register', absolute: false), navigate: false);

            return;
        }

        $this->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $isValid = $otpService->verifyCode(
            purpose: 'register',
            channel: 'email',
            recipient: $pending['email'],
            code: $this->otp,
        );

        if (! $isValid) {
            $this->addError('otp', 'Invalid or expired verification code.');

            return;
        }

        if (User::query()->where('email', $pending['email'])->exists()) {
            session()->forget('pending_registration');
            $this->addError('otp', 'This email address is already registered. Please login instead.');

            return;
        }

        $user = User::query()->create([
            'name' => 'HIDDEN',
            'name_stego_png_base64' => (string) ($pending['name_stego_png_base64'] ?? ''),
            'email' => $pending['email'],
            'phone' => $pending['phone'],
            'password' => $pending['password'],
            'role' => $pending['role'] ?? 'customer',
            'email_verified_at' => now(),
        ]);

        event(new Registered($user));

        Auth::login($user);
        session()->forget('pending_registration');
        session()->regenerate();

        $this->redirect(route('dashboard', absolute: false), navigate: false);
    }

    public function resend(OtpService $otpService): void
    {
        $pending = session('pending_registration');

        if (! is_array($pending) || empty($pending['email'])) {
            $this->redirect(route('register', absolute: false), navigate: false);

            return;
        }

        $this->sendVerificationCode($otpService, ['stage' => 'registration-resend']);
    }

    private function sendVerificationCode(OtpService $otpService, array $context = ['stage' => 'registration']): void
    {
        $pending = session('pending_registration');

        if (! is_array($pending) || empty($pending['email'])) {
            return;
        }

        try {
            $otpService->issueCode(
                purpose: 'register',
                channel: 'email',
                recipient: $pending['email'],
                context: $context,
            );

            session()->flash('status', 'verification-code-sent');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            if ($exception->errors() !== [] && array_key_exists('otp', $exception->errors())) {
                $this->addError('otp', $exception->errors()['otp'][0]);

                return;
            }

            throw $exception;
        }
    }
}; ?>

<div>
    <div class="mb-6">
        <p class="text-xs uppercase tracking-[0.3em] text-amber-300">Verify Email</p>
        <h1 class="mt-2 text-3xl font-semibold text-white">Enter OTP Code</h1>
        <p class="mt-2 text-sm text-zinc-400">We sent a 6-digit OTP to your email address to finish account creation.</p>
        @if (is_array(session('pending_registration')) && ! empty(session('pending_registration')['email'] ?? null))
            <p class="mt-2 text-sm text-amber-200">Check the inbox for <span class="font-medium">{{ session('pending_registration')['email'] }}</span>.</p>
        @endif
    </div>

    @if (session('status') === 'verification-code-sent')
        <div class="mb-4 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            A new verification code has been sent.
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
                autofocus
            />
            <x-input-error :messages="$errors->get('otp')" class="mt-2" />
        </div>

        <div class="pt-2 flex items-center justify-between gap-3">
            <button
                type="button"
                wire:click="resend"
                class="text-sm text-zinc-400 underline underline-offset-2 hover:text-amber-300 focus:outline-none"
            >
                Resend code
            </button>

            <x-primary-button class="!rounded-full !bg-amber-400 !px-6 !py-2 !text-zinc-900 !normal-case !tracking-wide hover:!bg-amber-300 focus:!bg-amber-300 focus:!ring-amber-300">
                Complete Registration
            </x-primary-button>
        </div>
    </form>
</div>
