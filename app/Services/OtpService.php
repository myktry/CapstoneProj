<?php

namespace App\Services;

use App\Exceptions\OtpException;
use App\Models\OtpChallenge;
use App\Mail\OtpCodeMail;
use App\Services\Sms\SmsSender;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;
use Throwable;

class OtpService
{
    private const OTP_LENGTH = 6;

    private const OTP_EXPIRY_MINUTES = 5;

    private const OTP_COOLDOWN_SECONDS = 60;

    public function __construct()
    {
    }

    /**
     * @return array{challenge_id:int,expires_in:int,cooldown:int}
     */
    public function issueCode(
        string $purpose,
        string $channel,
        string $recipient,
        ?int $userId = null,
        array $context = [],
    ): array {
        $normalizedChannel = strtolower(trim($channel));
        $normalizedPurpose = strtolower(trim($purpose));
        $normalizedRecipient = $this->normalizeRecipient($normalizedChannel, $recipient);

        $latest = OtpChallenge::query()
            ->where('purpose', $normalizedPurpose)
            ->where('channel', $normalizedChannel)
            ->where('recipient', $normalizedRecipient)
            ->latest('id')
            ->first();

        if ($latest && $latest->created_at?->gt(now()->subSeconds(self::OTP_COOLDOWN_SECONDS))) {
            $remaining = now()->diffInSeconds($latest->created_at->addSeconds(self::OTP_COOLDOWN_SECONDS), false);
            $secondsRemaining = max(1, (int) ceil($remaining));
            $secondsLabel = $secondsRemaining === 1 ? 'second' : 'seconds';

            throw OtpException::rateLimited("Please wait {$secondsRemaining} {$secondsLabel} before requesting a new code.");
        }

        try {
            $code = $this->generateCode();

            $challenge = OtpChallenge::query()->create([
                'user_id' => $userId,
                'purpose' => $normalizedPurpose,
                'channel' => $normalizedChannel,
                'recipient' => $normalizedRecipient,
                'code_hash' => Hash::make($code),
                'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
                'context' => $context ?: null,
            ]);

            $this->deliverCode(
                channel: $normalizedChannel,
                recipient: $normalizedRecipient,
                code: $code,
                purpose: $normalizedPurpose,
            );

            return [
                'challenge_id' => (int) $challenge->id,
                'expires_in' => self::OTP_EXPIRY_MINUTES,
                'cooldown' => self::OTP_COOLDOWN_SECONDS,
            ];
        } catch (OtpException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::error('OTP code issuance failed', [
                'purpose' => $purpose,
                'channel' => $channel,
                'error' => $exception->getMessage(),
            ]);

            throw new OtpException(
                'Failed to issue OTP code: ' . $exception->getMessage(),
                'Unable to send verification code. Please try again.',
                context: ['purpose' => $purpose, 'channel' => $channel],
                previous: $exception,
            );
        }
    }

    public function verifyCode(
        string $purpose,
        string $channel,
        string $recipient,
        string $code,
        ?int $userId = null,
    ): bool {
        $normalizedChannel = strtolower(trim($channel));
        $normalizedPurpose = strtolower(trim($purpose));
        $normalizedRecipient = $this->normalizeRecipient($normalizedChannel, $recipient);

        $challenge = OtpChallenge::query()
            ->where('purpose', $normalizedPurpose)
            ->where('channel', $normalizedChannel)
            ->where('recipient', $normalizedRecipient)
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->active()
            ->latest('id')
            ->first();

        if (! $challenge) {
            return false;
        }

        if (! Hash::check(trim($code), $challenge->code_hash)) {
            $newAttempts = $challenge->attempts + 1;
            $payload = ['attempts' => $newAttempts];

            if ($newAttempts >= $challenge->max_attempts) {
                $payload['consumed_at'] = now();
            }

            $challenge->update($payload);

            return false;
        }

        $challenge->update([
            'consumed_at' => now(),
        ]);

        return true;
    }

    private function deliverCode(string $channel, string $recipient, string $code, string $purpose): void
    {
        if (! in_array($channel, ['email', 'sms'], true)) {
            throw new OtpException(
                'Unsupported OTP channel selected',
                'The selected delivery method is not supported.',
                context: ['channel' => $channel],
            );
        }

        if ($channel === 'email') {
            try {
                Mail::mailer((string) config('mail.default', 'smtp'))->to($recipient)->send(
                    new OtpCodeMail(
                        code: $code,
                        purpose: $purpose,
                        expiresInMinutes: self::OTP_EXPIRY_MINUTES,
                    )
                );
            } catch (Throwable $exception) {
                Log::error('OTP email delivery failed', [
                    'purpose' => $purpose,
                    'recipient' => $recipient,
                    'mailer' => config('mail.default', 'smtp'),
                    'smtp_host' => config('mail.mailers.smtp.host'),
                    'error' => $exception->getMessage(),
                ]);

                throw new OtpException(
                    'Email delivery failed: ' . $exception->getMessage(),
                    'We could not send the verification email right now. Please try again in a moment.',
                    context: ['purpose' => $purpose, 'recipient' => $recipient],
                    previous: $exception,
                );
            }

            return;
        }

        $message = "Your Black Ember verification code is {$code}. It expires in " . self::OTP_EXPIRY_MINUTES . ' minutes.';

        try {
            app(SmsSender::class)->send($recipient, $message);
        } catch (Throwable $exception) {
            Log::error('OTP SMS delivery failed', [
                'purpose' => $purpose,
                'recipient' => $recipient,
                'error' => $exception->getMessage(),
            ]);

            throw new OtpException(
                'SMS delivery failed: ' . $exception->getMessage(),
                'We could not send the verification SMS right now. Please try again in a moment.',
                context: ['purpose' => $purpose, 'recipient' => $recipient],
                previous: $exception,
            );
        }
    }

    private function normalizeRecipient(string $channel, string $recipient): string
    {
        $value = trim($recipient);

        if ($channel === 'email') {
            return strtolower($value);
        }

        if ($channel === 'sms') {
            return preg_replace('/\s+/', '', $value) ?? $value;
        }

        return $value;
    }

    private function generateCode(): string
    {
        $script = base_path('scripts/otp/generate.mjs');

        if (is_file($script)) {
            $process = new Process(['node', $script, '--length', (string) self::OTP_LENGTH]);
            $process->setTimeout(10);
            $process->run();

            if ($process->isSuccessful()) {
                $value = trim($process->getOutput());

                if (preg_match('/^\d{'.self::OTP_LENGTH.'}$/', $value) === 1) {
                    return $value;
                }
            }
        }

        return str_pad((string) random_int(0, (10 ** self::OTP_LENGTH) - 1), self::OTP_LENGTH, '0', STR_PAD_LEFT);
    }
}
