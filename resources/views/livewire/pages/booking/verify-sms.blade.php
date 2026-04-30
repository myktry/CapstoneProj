<?php

use App\Services\OtpService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $otp = '';
    public string $maskedPhone = '';
    public bool $autoSent = false;
    public string $bookingFingerprint = '';
    public string $backToBookingUrl = '';

    public function mount(OtpService $otpService): void
    {
        $booking = $this->pendingBookingDraft();

        if (! $booking) {
            session()->forget('booking_sms_autosent_key');
            $this->redirect(route('home', absolute: false), navigate: false);

            return;
        }

        $phone = trim((string) ($booking['customer_phone'] ?? ''));
        $this->maskedPhone = $this->maskPhone($phone);
        $this->bookingFingerprint = $this->fingerprint($booking);
        $this->backToBookingUrl = route('book.appointment', [
            'service' => $booking['service_id'] ?? null,
        ]);

        $autoSentKey = (string) session('booking_sms_autosent_key', '');

        if ($phone !== '' && $autoSentKey !== $this->bookingFingerprint) {
            $this->sendCode($otpService, true);
        }
    }

    public function sendCode(OtpService $otpService, bool $isAutoSend = false): void
    {
        $this->resetErrorBag('otp');

        $booking = $this->pendingBookingDraft();

        if (! $booking) {
            session()->forget('booking_sms_autosent_key');
            $this->redirect(route('home', absolute: false), navigate: false);

            return;
        }

        $phone = trim((string) ($booking['customer_phone'] ?? ''));

        if ($phone === '') {
            $this->addError('otp', 'No phone number found for this booking.');

            return;
        }

        try {
            $otpService->issueCode(
                purpose: 'booking',
                channel: 'sms',
                recipient: $phone,
                userId: Auth::id(),
                context: ['stage' => 'booking-confirmation'],
            );
        } catch (ValidationException $exception) {
            $this->addError('otp', (string) ($exception->validator->errors()->first('otp') ?: 'Unable to send verification code right now.'));

            return;
        } catch (\Throwable $exception) {
            $this->addError('otp', $exception->getMessage() !== ''
                ? $exception->getMessage()
                : 'Unable to send verification code right now.');

            return;
        }

        session()->put('booking_sms_autosent_key', $this->fingerprint($booking));
        $this->autoSent = $isAutoSend;
        session()->flash('status', $isAutoSend ? 'booking-code-auto-sent' : 'booking-code-sent');
    }

    public function verify(OtpService $otpService): void
    {
        $this->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $booking = $this->pendingBookingDraft();

        if (! $booking) {
            session()->forget('booking_sms_autosent_key');
            $this->redirect(route('home', absolute: false), navigate: false);

            return;
        }

        $phone = trim((string) ($booking['customer_phone'] ?? ''));

        if ($phone === '') {
            $this->addError('otp', 'No phone number found for this booking.');

            return;
        }

        $isValid = $otpService->verifyCode(
            purpose: 'booking',
            channel: 'sms',
            recipient: $phone,
            code: $this->otp,
            userId: Auth::id(),
        );

        if (! $isValid) {
            $this->addError('otp', 'Invalid or expired verification code.');

            return;
        }

        session()->put('pending_booking', $booking);
        session()->forget(['pending_booking_draft', 'booking_sms_autosent_key']);

        $this->redirect(route('checkout.create', absolute: false), navigate: false);
    }

    private function pendingBookingDraft(): ?array
    {
        $pending = session('pending_booking_draft');

        return is_array($pending) ? $pending : null;
    }

    private function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (strlen($digits) < 4) {
            return $phone !== '' ? $phone : 'your saved phone number';
        }

        return str_repeat('*', max(0, strlen($digits) - 4)).substr($digits, -4);
    }

    private function fingerprint(array $booking): string
    {
        return sha1(json_encode([
            'service_id' => $booking['service_id'] ?? null,
            'appointment_date' => $booking['appointment_date'] ?? null,
            'appointment_time' => $booking['appointment_time'] ?? null,
            'customer_phone' => $booking['customer_phone'] ?? null,
        ]));
    }
}; ?>

<div class="mx-auto w-full max-w-xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="rounded-2xl border border-white/10 bg-zinc-900/80 p-6 shadow-2xl shadow-black/40 backdrop-blur">
        <p class="text-xs uppercase tracking-[0.3em] text-amber-300">Booking Verification</p>
        <h1 class="mt-2 text-2xl font-semibold text-white">Confirm via SMS OTP</h1>
        <p class="mt-2 text-sm text-zinc-400">Before payment, we verify your booking with a one-time code sent to {{ $maskedPhone }}.</p>

        <div class="mt-5 grid gap-2 rounded-xl border border-amber-500/20 bg-amber-500/5 p-4 text-sm text-amber-100">
            <p><strong>Step 1:</strong> We send a 6-digit OTP via SMS.</p>
            <p><strong>Step 2:</strong> Enter the code below to verify your number.</p>
            <p><strong>Step 3:</strong> Continue to secure payment checkout.</p>
        </div>

        @if (session('status') === 'booking-code-auto-sent')
            <div class="mt-4 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                We sent a verification code automatically to {{ $maskedPhone }}.
            </div>
        @elseif (session('status') === 'booking-code-sent')
            <div class="mt-4 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                New SMS verification code sent to {{ $maskedPhone }}.
            </div>
        @endif

        <form wire:submit="verify" class="mt-6 space-y-4">
            <div class="flex justify-end">
                <x-secondary-button type="button" wire:click="sendCode" class="!rounded-full !border-white/15 !bg-zinc-800 !text-zinc-200 hover:!bg-zinc-700">
                    Resend SMS code
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
                <p class="mt-2 text-xs text-zinc-500">Code expires in 5 minutes. If nothing arrives, wait at least 60 seconds then resend.</p>
            </div>

            <div class="flex items-center justify-between gap-3 pt-2">
                <a class="text-sm text-zinc-400 underline underline-offset-2 hover:text-amber-300 focus:outline-none" href="{{ $backToBookingUrl }}">
                    Back to booking
                </a>

                <x-primary-button class="!rounded-full !bg-amber-400 !px-6 !py-2 !text-zinc-900 !normal-case !tracking-wide hover:!bg-amber-300 focus:!bg-amber-300 focus:!ring-amber-300">
                    Verify and proceed to payment
                </x-primary-button>
            </div>
        </form>

        @if (config('services.sms.driver') === 'log')
            <p class="mt-4 text-xs text-zinc-500">SMS driver is set to log in this environment. Use your application logs to read OTP messages during development.</p>
        @elseif (config('services.sms.driver') === 'textbee')
            <p class="mt-4 text-xs text-zinc-500">TextBee sends SMS through your connected device. Check your TextBee dashboard if a message does not arrive.</p>
        @endif
    </div>
</div>
