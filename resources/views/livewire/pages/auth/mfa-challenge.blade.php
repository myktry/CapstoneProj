<?php

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $method = 'email';
    public string $otp = '';

    public function mount(OtpService $otpService): void
    {
        if (! $this->pendingLoginData()) {
            $this->redirect(route('login', absolute: false), navigate: false);

            return;
        }

        $this->sendCode($otpService);
    }

    public function sendCode(OtpService $otpService): void
    {
        $this->validate([
            'method' => ['required', 'in:email,sms'],
        ]);

        $pending = $this->pendingLoginData();

        if (! $pending) {
            $this->redirect(route('login', absolute: false), navigate: false);

            return;
        }

        $user = User::query()->find($pending['user_id']);

        if (! $user) {
            session()->forget('pending_login_mfa');
            $this->redirect(route('login', absolute: false), navigate: false);

            return;
        }

        $recipient = $this->method === 'sms'
            ? trim((string) $user->phone)
            : strtolower(trim((string) $user->email));

        if ($recipient === '') {
            $this->addError('method', $this->method === 'sms'
                ? 'No phone number is available for SMS verification.'
                : 'No email address is available for email verification.');

            return;
        }

        try {
            $otpService->issueCode(
                purpose: 'login',
                channel: $this->method,
                recipient: $recipient,
                userId: (int) $user->id,
                context: ['stage' => 'login-mfa'],
            );
        } catch (\Throwable $throwable) {
            report($throwable);

            $this->addError(
                'method',
                $this->method === 'email'
                    ? 'Email verification is unavailable right now. Please use SMS verification instead.'
                    : 'SMS verification is unavailable right now. Please try again later.'
            );

            return;
        }

        session()->flash('status', 'mfa-code-sent');
    }

    public function verify(OtpService $otpService): void
    {
        $this->validate([
            'method' => ['required', 'in:email,sms'],
            'otp' => ['required', 'digits:6'],
        ]);

        $pending = $this->pendingLoginData();

        if (! $pending) {
            $this->redirect(route('login', absolute: false), navigate: false);

            return;
        }

        $user = User::query()->find($pending['user_id']);

        if (! $user) {
            session()->forget('pending_login_mfa');
            $this->redirect(route('login', absolute: false), navigate: false);

            return;
        }

        $recipient = $this->method === 'sms'
            ? trim((string) $user->phone)
            : strtolower(trim((string) $user->email));

        if ($recipient === '') {
            $this->addError('method', 'The selected verification method is not available for this account.');

            return;
        }

        $isValid = $otpService->verifyCode(
            purpose: 'login',
            channel: $this->method,
            recipient: $recipient,
            code: $this->otp,
            userId: (int) $user->id,
        );

        if (! $isValid) {
            $this->addError('otp', 'Invalid or expired verification code.');

            return;
        }

        Auth::loginUsingId((int) $user->id, (bool) ($pending['remember'] ?? false));
        session()->forget('pending_login_mfa');
        session()->regenerate();

        $defaultRedirect = $user->role === 'admin'
            ? '/admin'
            : route('home', absolute: false);

        $this->redirectIntended(default: $defaultRedirect, navigate: false);
    }

    private function pendingLoginData(): ?array
    {
        $pending = session('pending_login_mfa');

        return is_array($pending) ? $pending : null;
    }
}; ?>

<div>
    <div class="mb-6">
        <p class="text-xs uppercase tracking-[0.3em] text-amber-300">Multi-factor Authentication</p>
        <h1 class="mt-2 text-3xl font-semibold text-white">Verify Login</h1>
        <p class="mt-2 text-sm text-zinc-400">Choose a verification method and enter the 6-digit code.</p>
    </div>

    @if (session('status') === 'mfa-code-sent')
        <div class="mb-4 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            Verification code sent.
        </div>
    @endif

    <form wire:submit="verify" class="space-y-4">
        <div>
            <p class="mb-2 text-sm font-medium text-zinc-300">Verification Method</p>
            <div class="grid grid-cols-2 gap-3">
                <button
                    type="button"
                    wire:click="$set('method', 'email')"
                    class="rounded-lg border px-4 py-3 text-sm font-medium transition {{ $method === 'email' ? 'border-amber-400 bg-amber-500/20 text-amber-300' : 'border-white/10 bg-zinc-950 text-zinc-300 hover:border-amber-500/30' }}"
                >
                    Email OTP
                </button>
                <button
                    type="button"
                    wire:click="$set('method', 'sms')"
                    class="rounded-lg border px-4 py-3 text-sm font-medium transition {{ $method === 'sms' ? 'border-amber-400 bg-amber-500/20 text-amber-300' : 'border-white/10 bg-zinc-950 text-zinc-300 hover:border-amber-500/30' }}"
                >
                    SMS OTP
                </button>
            </div>
            <x-input-error :messages="$errors->get('method')" class="mt-2" />
        </div>

        <div class="flex justify-end">
            <x-secondary-button type="button" wire:click="sendCode" class="!rounded-full !border-white/15 !bg-zinc-800 !text-zinc-200 hover:!bg-zinc-700">
                Send code
            </x-secondary-button>
        </div>

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

        <div class="pt-2 flex items-center justify-between gap-3">
            <a class="text-sm text-zinc-400 underline underline-offset-2 hover:text-amber-300 focus:outline-none" href="{{ route('login') }}" wire:navigate>
                Back to login
            </a>

            <x-primary-button class="!rounded-full !bg-amber-400 !px-6 !py-2 !text-zinc-900 !normal-case !tracking-wide hover:!bg-amber-300 focus:!bg-amber-300 focus:!ring-amber-300">
                Verify and continue
            </x-primary-button>
        </div>
    </form>
</div>
