<?php

namespace App\Http\Controllers;

use App\Mail\RefundProcessedMail;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Refund;
use Stripe\Stripe;

class BookingRefundController extends Controller
{
    public function show(Request $request, Appointment $appointment)
    {
        abort_unless((int) $appointment->user_id === (int) $request->user()?->id, 403);

        if ($appointment->seen_at === null) {
            $appointment->update(['seen_at' => now()]);
        }

        $this->syncRefundStatusFromStripe($appointment);
        $appointment->refresh();

        $deductionPercent = max(0, min(100, (int) config('refund.deduction_percent', 25)));
        $canRequestRefund = $this->canRequestRefund($appointment);
        $appointmentAt = $this->appointmentDateTime($appointment);
        $refundCutoffAt = $appointmentAt?->copy()->subMinutes(10);

        return view('booking.details', [
            'appointment' => $appointment->loadMissing('service'),
            'deductionPercent' => $deductionPercent,
            'canRequestRefund' => $canRequestRefund,
            'appointmentAt' => $appointmentAt,
            'refundCutoffAt' => $refundCutoffAt,
        ]);
    }

    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless((int) $appointment->user_id === (int) $request->user()?->id, 403);

        if ($appointment->status !== 'paid') {
            return back()->with('error', 'Only paid bookings can be cancelled with refund.');
        }

        if ($appointment->refund_status === 'pending') {
            return back()->with('error', 'A refund request is already pending confirmation.');
        }

        if ($appointment->refund_status === 'processed') {
            return back()->with('error', 'This booking has already been refunded.');
        }

        if (! $this->canRequestRefund($appointment)) {
            return back()->with('error', 'Refund is no longer allowed within 10 minutes before your appointment.');
        }

        $deductionPercent = max(0, min(100, (int) config('refund.deduction_percent', 25)));
        $amountPaid = (int) $appointment->amount_paid;

        if ($amountPaid <= 0) {
            return back()->with('error', 'No paid amount found for this booking.');
        }

        $deductionAmount = (int) round(($amountPaid * $deductionPercent) / 100);
        $refundAmount = max(0, $amountPaid - $deductionAmount);

        if ($refundAmount <= 0) {
            return back()->with('error', 'Refund amount is zero based on the current policy.');
        }

        Stripe::setApiKey((string) config('services.stripe.secret'));

        $paymentIntentId = (string) ($appointment->stripe_payment_intent_id ?: '');

        if ($paymentIntentId === '' && $appointment->stripe_session_id) {
            $session = StripeSession::retrieve((string) $appointment->stripe_session_id);
            $paymentIntentId = (string) ($session->payment_intent ?? '');
        }

        if ($paymentIntentId === '') {
            return back()->with('error', 'Unable to locate the original payment reference for refund.');
        }

        try {
            $refund = Refund::create([
                'payment_intent' => $paymentIntentId,
                'amount' => $refundAmount,
                'reason' => 'requested_by_customer',
                'metadata' => [
                    'appointment_id' => (string) $appointment->id,
                    'cancelled_by' => 'user',
                ],
            ]);
        } catch (\Throwable $exception) {
            return back()->with('error', 'Refund request failed: '.$exception->getMessage());
        }

        $appointment->update([
            'status' => 'cancelled',
            'stripe_payment_intent_id' => $paymentIntentId,
            'refund_status' => 'pending',
            'refund_amount' => $refundAmount,
            'refund_deduction_amount' => $deductionAmount,
            'refund_reference' => (string) $refund->id,
            'refund_processed_at' => null,
            'cancelled_by' => 'user',
            'cancelled_at' => now(),
            'cancellation_note' => 'User cancelled booking from account notification/details page. Refund pending Stripe confirmation.',
        ]);

        return back()->with('status', 'Booking cancelled and refund requested. We are waiting for Stripe confirmation. Reference: '.$refund->id);
    }

    private function canRequestRefund(Appointment $appointment): bool
    {
        $appointmentAt = $this->appointmentDateTime($appointment);

        if (! $appointmentAt) {
            return false;
        }

        return now()->lt($appointmentAt->copy()->subMinutes(10));
    }

    private function appointmentDateTime(Appointment $appointment): ?Carbon
    {
        $date = trim((string) $appointment->appointment_date);
        $time = trim((string) $appointment->appointment_time);

        if ($date === '' || $time === '') {
            return null;
        }

        try {
            return Carbon::parse($date.' '.$time);
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function syncRefundStatusFromStripe(Appointment $appointment): void
    {
        if ($appointment->refund_status !== 'pending' || ! $appointment->refund_reference) {
            return;
        }

        try {
            Stripe::setApiKey((string) config('services.stripe.secret'));
            $refund = Refund::retrieve((string) $appointment->refund_reference);
            $stripeStatus = (string) ($refund->status ?? '');
        } catch (\Throwable $exception) {
            return;
        }

        $mappedStatus = match ($stripeStatus) {
            'succeeded' => 'processed',
            'failed', 'canceled' => 'failed',
            default => 'pending',
        };

        if ($mappedStatus === 'pending') {
            return;
        }

        $wasProcessed = $appointment->refund_status === 'processed';

        $appointment->update([
            'refund_status' => $mappedStatus,
            'refund_processed_at' => $mappedStatus === 'processed' ? now() : null,
            'cancellation_note' => $mappedStatus === 'failed'
                ? 'User cancellation refund failed on Stripe sync check.'
                : $appointment->cancellation_note,
        ]);

        if ($mappedStatus === 'processed' && ! $wasProcessed && $appointment->customer_email !== '') {
            Mail::to($appointment->customer_email)->send(new RefundProcessedMail($appointment->fresh(['service'])));
        }
    }
}
