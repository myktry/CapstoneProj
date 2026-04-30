<?php

namespace App\Services;

use App\Mail\RefundProcessedMail;
use App\Models\Appointment;
use App\Models\UserNotification;
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

        $appointment->update([
            'refund_status' => $mappedStatus,
            'refund_processed_at' => $mappedStatus === 'processed' ? now() : null,
            'cancellation_note' => $mappedStatus === 'failed'
                ? 'User cancellation refund failed on Stripe sync check.'
                : $appointment->cancellation_note,
        ]);

        if ($mappedStatus === 'processed' && ! $wasProcessed) {
            $this->dispatchRefundProcessedNotification($appointment->fresh(['service']));
        }

        return $mappedStatus;
    }

    public function dispatchRefundProcessedNotification(Appointment $appointment): void
    {
        if ($appointment->customer_email !== '') {
            Mail::to($appointment->customer_email)->send(new RefundProcessedMail($appointment));
        }

        if ($appointment->user_id) {
            UserNotification::create([
                'user_id' => $appointment->user_id,
                'type' => 'refund_processed',
                'title' => 'Refund Processed',
                'message' => 'Your refund of '.number_format(((int) $appointment->refund_amount) / 100, 2).' has been successfully processed.',
                'related_model' => 'Appointment',
                'related_id' => $appointment->id,
            ]);
        }
    }
}
