<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class VonageSmsSender implements SmsSender
{
    public function send(string $phoneNumber, string $message): void
    {
        $apiKey = (string) config('services.sms.vonage_key');
        $apiSecret = (string) config('services.sms.vonage_secret');
        $from = (string) config('services.sms.from', 'BLACKEMBER');
        $verifySsl = filter_var(config('services.sms.vonage_verify_ssl', true), FILTER_VALIDATE_BOOL);

        $normalizedTo = $this->normalizeRecipient($phoneNumber);

        if ($apiKey === '' || $apiSecret === '') {
            throw new RuntimeException('Vonage credentials are not configured.');
        }

        $response = Http::withOptions(['verify' => $verifySsl])->asForm()->post((string) config('services.sms.vonage_url'), [
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,
            'to' => $normalizedTo,
            'from' => $from,
            'text' => $message,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Vonage request failed.');
        }

        $payload = $response->json();

        $status = data_get($payload, 'messages.0.status');

        if ((string) $status !== '0') {
            $errorText = (string) data_get($payload, 'messages.0.error-text', 'Unknown Vonage error.');

            throw new RuntimeException('SMS not sent: '.$errorText);
        }
    }

    private function normalizeRecipient(string $phoneNumber): string
    {
        $digits = preg_replace('/\D+/', '', trim($phoneNumber)) ?? '';

        if ($digits === '') {
            return trim($phoneNumber);
        }

        // PH local format: 09XXXXXXXXX -> 639XXXXXXXXX
        if (str_starts_with($digits, '09') && strlen($digits) === 11) {
            return '63'.substr($digits, 1);
        }

        // PH with country code: +63XXXXXXXXXX or 63XXXXXXXXXX
        if (str_starts_with($digits, '63') && strlen($digits) === 12) {
            return $digits;
        }

        return $digits;
    }
}
