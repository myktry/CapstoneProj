<?php

namespace App\Http\Controllers;

use App\Mail\RefundProcessedMail;
use App\Models\Appointment;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $payload = (string) $request->getContent();
        $signature = (string) $request->header('Stripe-Signature', '');
        $webhookSecret = (string) config('services.stripe.webhook_secret', '');

        try {
            $event = $webhookSecret !== ''
                ? Webhook::constructEvent($payload, $signature, $webhookSecret)
                : json_decode($payload, false, 512, JSON_THROW_ON_ERROR);
        } catch (SignatureVerificationException|\JsonException $exception) {
            Log::warning('Stripe webhook signature verification failed', [
                'error' => $exception->getMessage(),
            ]);
            return response('Invalid webhook payload.', 400);
        }

        $type = (string) ($event->type ?? '');

        if (! str_starts_with($type, 'refund.') && $type !== 'charge.refunded') {
            return response('Event ignored.', 200);
        }

        Log::info('Processing Stripe webhook event', ['type' => $type]);

        $refundId = '';
        $paymentIntent = '';
        $refundStatus = '';

        if (str_starts_with($type, 'refund.')) {
            $refundObject = $event->data->object ?? null;

            if (! $refundObject) {
                Log::error('Missing refund object in refund event');
                return response('Missing refund object.', 400);
            }

            $refundId = (string) ($refundObject->id ?? '');
            $paymentIntent = (string) ($refundObject->payment_intent ?? '');
            $refundStatus = (string) ($refundObject->status ?? '');
        }

        if ($type === 'charge.refunded') {
            $chargeObject = $event->data->object ?? null;

            if (! $chargeObject) {
                Log::error('Missing charge object in charge.refunded event');
                return response('Missing charge object.', 400);
            }

            $paymentIntent = (string) ($chargeObject->payment_intent ?? '');
            $refundStatus = ((int) ($chargeObject->amount_refunded ?? 0)) > 0 ? 'succeeded' : 'pending';

            $refundData = $chargeObject->refunds->data[0] ?? null;
            $refundId = (string) ($refundData->id ?? '');
        }

        if ($refundId === '' && $paymentIntent === '') {
            Log::error('Refund identifiers missing in webhook event');
            return response('Refund identifiers missing.', 400);
        }

        $appointment = Appointment::query()
            ->when($refundId !== '', fn ($query) => $query->where('refund_reference', $refundId))
            ->when($refundId === '' && $paymentIntent !== '', fn ($query) => $query->where('stripe_payment_intent_id', $paymentIntent))
            ->latest('id')
            ->first();

        if (! $appointment) {
            Log::warning('No appointment found for refund event', [
                'refund_id' => $refundId,
                'payment_intent' => $paymentIntent,
            ]);
            return response('No appointment found for refund event.', 200);
        }

        $mappedRefundStatus = match ($refundStatus) {
            'succeeded' => 'processed',
            'failed', 'canceled' => 'failed',
            default => 'pending',
        };

        $wasProcessed = $appointment->refund_status === 'processed';

        try {
            $appointment->update([
                'refund_status' => $mappedRefundStatus,
                'refund_reference' => $refundId !== '' ? $refundId : $appointment->refund_reference,
                'refund_processed_at' => $mappedRefundStatus === 'processed' ? now() : $appointment->refund_processed_at,
                'cancellation_note' => $mappedRefundStatus === 'failed'
                    ? 'User cancellation refund failed on Stripe webhook update.'
                    : $appointment->cancellation_note,
            ]);

            Log::info('Appointment refund status updated', [
                'appointment_id' => $appointment->id,
                'refund_status' => $mappedRefundStatus,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Failed to update appointment refund status', [
                'appointment_id' => $appointment->id,
                'error' => $exception->getMessage(),
            ]);
            return response('Failed to update appointment.', 500);
        }

        if ($mappedRefundStatus === 'processed' && ! $wasProcessed && $appointment->customer_email !== '') {
            try {
                Mail::to($appointment->customer_email)->send(new RefundProcessedMail($appointment->fresh(['service'])));
            } catch (\Throwable $exception) {
                Log::error('Failed to send refund processed email', [
                    'appointment_id' => $appointment->id,
                    'email' => $appointment->customer_email,
                    'error' => $exception->getMessage(),
                ]);
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

        return response('Webhook handled.', 200);
    }
}
