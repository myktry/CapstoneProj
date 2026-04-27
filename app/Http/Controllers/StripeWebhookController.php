<?php

namespace App\Http\Controllers;

use App\Mail\RefundProcessedMail;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
            return response('Invalid webhook payload.', 400);
        }

        $type = (string) ($event->type ?? '');

        if (! str_starts_with($type, 'refund.') && $type !== 'charge.refunded') {
            return response('Event ignored.', 200);
        }

        $refundId = '';
        $paymentIntent = '';
        $refundStatus = '';

        if (str_starts_with($type, 'refund.')) {
            $refundObject = $event->data->object ?? null;

            if (! $refundObject) {
                return response('Missing refund object.', 400);
            }

            $refundId = (string) ($refundObject->id ?? '');
            $paymentIntent = (string) ($refundObject->payment_intent ?? '');
            $refundStatus = (string) ($refundObject->status ?? '');
        }

        if ($type === 'charge.refunded') {
            $chargeObject = $event->data->object ?? null;

            if (! $chargeObject) {
                return response('Missing charge object.', 400);
            }

            $paymentIntent = (string) ($chargeObject->payment_intent ?? '');
            $refundStatus = ((int) ($chargeObject->amount_refunded ?? 0)) > 0 ? 'succeeded' : 'pending';

            $refundData = $chargeObject->refunds->data[0] ?? null;
            $refundId = (string) ($refundData->id ?? '');
        }

        if ($refundId === '' && $paymentIntent === '') {
            return response('Refund identifiers missing.', 400);
        }

        $appointment = Appointment::query()
            ->when($refundId !== '', fn ($query) => $query->where('refund_reference', $refundId))
            ->when($refundId === '' && $paymentIntent !== '', fn ($query) => $query->where('stripe_payment_intent_id', $paymentIntent))
            ->latest('id')
            ->first();

        if (! $appointment) {
            return response('No appointment found for refund event.', 200);
        }

        $mappedRefundStatus = match ($refundStatus) {
            'succeeded' => 'processed',
            'failed', 'canceled' => 'failed',
            default => 'pending',
        };

        $wasProcessed = $appointment->refund_status === 'processed';

        $appointment->update([
            'refund_status' => $mappedRefundStatus,
            'refund_reference' => $refundId !== '' ? $refundId : $appointment->refund_reference,
            'refund_processed_at' => $mappedRefundStatus === 'processed' ? now() : $appointment->refund_processed_at,
            'cancellation_note' => $mappedRefundStatus === 'failed'
                ? 'User cancellation refund failed on Stripe webhook update.'
                : $appointment->cancellation_note,
        ]);

        if ($mappedRefundStatus === 'processed' && ! $wasProcessed && $appointment->customer_email !== '') {
            Mail::to($appointment->customer_email)->queue(new RefundProcessedMail($appointment->fresh(['service'])));
        }

        return response('Webhook handled.', 200);
    }
}
