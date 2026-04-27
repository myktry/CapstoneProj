<?php

namespace App\Jobs;

use App\Mail\OtpCodeMail;
use App\Services\Sms\SmsSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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
            Mail::to($this->recipient)->queue(new OtpCodeMail(
                code: $this->code,
                purpose: $this->purpose,
                expiresInMinutes: $this->expiresInMinutes,
            ));

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
