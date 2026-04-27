<?php

namespace App\Services;

use App\Jobs\DeliverOtpCode;
use App\Models\OtpChallenge;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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

            throw ValidationException::withMessages([
                'otp' => "Please wait {$secondsRemaining} {$secondsLabel} before requesting a new code.",
            ]);
        }

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
            throw ValidationException::withMessages([
                'otp' => 'Unsupported OTP channel selected.',
            ]);
        }

        DeliverOtpCode::dispatch(
            channel: $channel,
            recipient: $recipient,
            code: $code,
            purpose: $purpose,
            expiresInMinutes: self::OTP_EXPIRY_MINUTES,
        );
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
        return str_pad((string) random_int(0, (10 ** self::OTP_LENGTH) - 1), self::OTP_LENGTH, '0', STR_PAD_LEFT);
    }
}
