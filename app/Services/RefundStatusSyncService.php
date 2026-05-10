<?php

namespace App\Services;

use App\Mail\RefundProcessedMail;
use App\Models\Appointment;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Refund;
use Stripe\Stripe;

class RefundStatusSyncService
{
    public function sync(Appointment $appointment): string
    {
        if ($appointment->refund_status !== 'pending' || ! $appointment->refund_reference) {
            return (string) $appointment->refund_status;
        }

        try {
            Stripe::setApiKey((string) config('services.stripe.secret'));
            $refund = Refund::retrieve((string) $appointment->refund_reference);
            $stripeStatus = (string) ($refund->status ?? '');
        } catch (\Throwable $exception) {
            Log::error('Failed to retrieve refund status from Stripe', [
                'appointment_id' => $appointment->id,
                'refund_reference' => $appointment->refund_reference,
                'error' => $exception->getMessage(),
            ]);

            return (string) $appointment->refund_status;
        }

        $mappedStatus = match ($stripeStatus) {
            'succeeded' => 'processed',
            'failed', 'canceled' => 'failed',
            default => 'pending',
        };

        if ($mappedStatus === 'pending') {
            return $mappedStatus;
        }

        $wasProcessed = $appointment->refund_status === 'processed';

        try {
            $appointment->update([
                'refund_status' => $mappedStatus,
                'refund_processed_at' => $mappedStatus === 'processed' ? now() : null,
                'cancellation_note' => $mappedStatus === 'failed'
                    ? 'User cancellation refund failed on Stripe sync check.'
                    : $appointment->cancellation_note,
            ]);

            Log::info('Appointment refund status synced with Stripe', [
                'appointment_id' => $appointment->id,
                'refund_status' => $mappedStatus,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Failed to update appointment refund status', [
                'appointment_id' => $appointment->id,
                'error' => $exception->getMessage(),
            ]);

            return (string) $appointment->refund_status;
        }

        if ($mappedStatus === 'processed' && ! $wasProcessed) {
            $this->dispatchRefundProcessedNotification($appointment->fresh(['service']));
        }

        return $mappedStatus;
    }

    public function dispatchRefundProcessedNotification(Appointment $appointment): void
    {
        if ($appointment->customer_email !== '') {
            try {
                Mail::to($appointment->customer_email)->send(new RefundProcessedMail($appointment));
            } catch (\Throwable $exception) {
                Log::error('Failed to send refund processed email', [
                    'appointment_id' => $appointment->id,
                    'email' => $appointment->customer_email,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        if ($appointment->user_id) {
            try {
                UserNotification::create([
                    'user_id' => $appointment->user_id,
                    'type' => 'refund_processed',
                    'title' => 'Refund Processed',
                    'message' => 'Your refund of '.number_format(((int) $appointment->refund_amount) / 100, 2).' has been successfully processed.',
                    'related_model' => 'Appointment',
                    'related_id' => $appointment->id,
                ]);
            } catch (\Throwable $exception) {
                Log::error('Failed to create refund processed notification', [
                    'appointment_id' => $appointment->id,
                    'user_id' => $appointment->user_id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }
}
