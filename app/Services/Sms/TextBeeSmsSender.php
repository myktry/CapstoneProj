<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class TextBeeSmsSender implements SmsSender
{
    public function send(string $phoneNumber, string $message): void
    {
        $apiKey = (string) config('services.sms.textbee_api_key');
        $deviceId = (string) config('services.sms.textbee_device_id');
        $baseUrl = rtrim((string) config('services.sms.textbee_base_url', 'https://api.textbee.dev/api/v1'), '/');
        $verifySsl = filter_var(config('services.sms.textbee_verify_ssl', true), FILTER_VALIDATE_BOOL);

        if ($apiKey === '' || $deviceId === '') {
            throw new RuntimeException('TextBee API key or device ID is not configured.');
        }

        $normalizedTo = $this->normalizeRecipient($phoneNumber);

        $payload = [
            'recipients' => [$normalizedTo],
            'message' => $message,
        ];

        $simSubscriptionId = config('services.sms.textbee_sim_subscription_id');

        if ($simSubscriptionId !== null && $simSubscriptionId !== '') {
            $payload['simSubscriptionId'] = (int) $simSubscriptionId;
        }

        $response = Http::withOptions(['verify' => $verifySsl])
            ->withHeaders([
                'x-api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post($baseUrl.'/gateway/devices/'.$deviceId.'/send-sms', $payload);

        if ($response->failed()) {
            $errorMessage = (string) data_get($response->json(), 'message', $response->body());

            throw new RuntimeException('TextBee request failed: '.$errorMessage);
        }
    }

    private function normalizeRecipient(string $phoneNumber): string
    {
        $cleaned = trim($phoneNumber);

        if (str_starts_with($cleaned, '+')) {
            $digits = preg_replace('/\D+/', '', substr($cleaned, 1)) ?? '';

            return $digits !== '' ? '+'.$digits : $cleaned;
        }

        $digits = preg_replace('/\D+/', '', $cleaned) ?? '';

        if ($digits === '') {
            return $cleaned;
        }

        // PH local format: 09XXXXXXXXX -> +639XXXXXXXXX
        if (str_starts_with($digits, '09') && strlen($digits) === 11) {
            return '+63'.substr($digits, 1);
        }

        // PH with country code: 63XXXXXXXXXX -> +63XXXXXXXXXX
        if (str_starts_with($digits, '63') && strlen($digits) === 12) {
            return '+'.$digits;
        }

        // Fallback: treat raw digits as an E.164-ish input.
        return '+'.$digits;
    }
}
