<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;

class LogSmsSender implements SmsSender
{
    public function send(string $phoneNumber, string $message): void
    {
        Log::channel(config('logging.default'))->info('SMS OTP sent (log driver)', [
            'phone' => $phoneNumber,
            'message' => $message,
        ]);
    }
}
