<?php

namespace App\Jobs;

use App\Services\Sms\SmsSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class DeliverOtpCode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $channel,
        public readonly string $recipient,
        public readonly string $code,
        public readonly string $purpose,
        public readonly int $expiresInMinutes,
    ) {
    }

    public function handle(SmsSender $smsSender): void
    {
        $message = "Your Black Ember verification code is {$this->code}. It expires in {$this->expiresInMinutes} minutes.";

        if ($this->channel === 'email') {
            $script = base_path('scripts/otp/send-email.mjs');

            if (! is_file($script)) {
                throw new \RuntimeException('OTP email sender script is missing: scripts/otp/send-email.mjs');
            }

            $process = new Process(
                [
                    'node',
                    $script,
                    '--to',
                    $this->recipient,
                    '--code',
                    $this->code,
                    '--purpose',
                    $this->purpose,
                    '--expires',
                    (string) $this->expiresInMinutes,
                ],
                base_path(),
                [
                    // Laravel loads .env into PHP, but child processes won't see it unless we pass it through.
                    'EMAIL_USER' => (string) env('EMAIL_USER', ''),
                    'EMAIL_PASS' => (string) env('EMAIL_PASS', ''),
                ] + ($_ENV ?? [])
            );
            $process->setTimeout(20);
            $process->run();

            if (! $process->isSuccessful()) {
                Log::error('Failed to send OTP email via nodemailer.', [
                    'recipient' => $this->recipient,
                    'stdout' => trim($process->getOutput()),
                    'stderr' => trim($process->getErrorOutput()),
                ]);

                throw new \RuntimeException('Failed to send OTP email via nodemailer.');
            }

            return;
        }

        if ($this->channel === 'sms') {
            $smsSender->send($this->recipient, $message);

            return;
        }

        Log::warning('Unsupported OTP channel encountered in queued delivery.', [
            'channel' => $this->channel,
            'recipient' => $this->recipient,
        ]);
    }
}
