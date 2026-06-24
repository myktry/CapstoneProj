<?php

namespace App\Http\Controllers;

use App\Exceptions\AuthorizationException;
use App\Exceptions\RefundException;
use App\Jobs\SyncRefundStatus;
use App\Models\Appointment;
use App\Services\RefundStatusSyncService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Refund;
use Stripe\Stripe;

class BookingRefundController extends Controller
{
    public function show(Request $request, Appointment $appointment, RefundStatusSyncService $refundStatusSyncService)
    {
        if ((int) $appointment->user_id !== (int) $request->user()?->id) {
            throw new AuthorizationException(
                'User does not own this appointment',
                'You do not have permission to view this appointment.',
                context: ['appointment_id' => $appointment->id],
            );
        }

        if ($appointment->seen_at === null) {
            $appointment->update(['seen_at' => now()]);
        }

        try {
            $refundStatusSyncService->sync($appointment);
            $appointment->refresh();
        } catch (\Throwable $exception) {
            Log::error('Failed to sync refund status', [
                'appointment_id' => $appointment->id,
                'error' => $exception->getMessage(),
            ]);
            // Continue with cached data
        }

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
        if ((int) $appointment->user_id !== (int) $request->user()?->id) {
            throw new AuthorizationException(
                'User does not own this appointment',
                'You do not have permission to cancel this appointment.',
                context: ['appointment_id' => $appointment->id],
            );
        }

        if ($appointment->status !== 'paid') {
            throw new RefundException(
                'Appointment status is not paid',
                'Only paid bookings can be cancelled with refund.',
                context: ['status' => $appointment->status],
            );
        }

        if ($appointment->refund_status === 'pending') {
            throw new RefundException(
                'Refund already pending',
                'A refund request is already pending confirmation.',
                context: ['refund_status' => $appointment->refund_status],
            );
        }

        if ($appointment->refund_status === 'processed') {
            throw new RefundException(
                'Booking already refunded',
                'This booking has already been refunded.',
                context: ['refund_status' => $appointment->refund_status],
            );
        }

        if (! $this->canRequestRefund($appointment)) {
            throw new RefundException(
                'Refund cutoff time passed',
                'Refund is no longer allowed within 10 minutes before your appointment.',
            );
        }

        $deductionPercent = max(0, min(100, (int) config('refund.deduction_percent', 25)));
        $amountPaid = (int) $appointment->amount_paid;

        if ($amountPaid <= 0) {
            throw new RefundException(
                'No paid amount found',
                'No paid amount found for this booking.',
                context: ['amount_paid' => $amountPaid],
            );
        }

        $deductionAmount = (int) round(($amountPaid * $deductionPercent) / 100);
        $refundAmount = max(0, $amountPaid - $deductionAmount);

        if ($refundAmount <= 0) {
            throw new RefundException(
                'Refund amount is zero',
                'Refund amount is zero based on the current policy.',
                context: ['refund_amount' => $refundAmount, 'deduction_percent' => $deductionPercent],
            );
        }

        Stripe::setApiKey((string) config('services.stripe.secret'));

        $paymentIntentId = (string) ($appointment->stripe_payment_intent_id ?: '');

        if ($paymentIntentId === '' && $appointment->stripe_session_id) {
            try {
                $session = StripeSession::retrieve((string) $appointment->stripe_session_id);
                $paymentIntentId = (string) ($session->payment_intent ?? '');
            } catch (\Throwable $exception) {
                Log::error('Failed to retrieve payment intent from Stripe session', [
                    'appointment_id' => $appointment->id,
                    'session_id' => $appointment->stripe_session_id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        if ($paymentIntentId === '') {
            throw new RefundException(
                'Unable to locate payment reference',
                'Unable to locate the original payment reference for refund.',
                context: ['appointment_id' => $appointment->id],
            );
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
            Log::error('Refund creation failed with Stripe', [
                'appointment_id' => $appointment->id,
                'payment_intent_id' => $paymentIntentId,
                'error' => $exception->getMessage(),
            ]);

            throw new RefundException(
                'Refund request failed: ' . $exception->getMessage(),
                'Refund request failed. Please try again or contact support.',
                context: ['appointment_id' => $appointment->id],
                previous: $exception,
            );
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

        SyncRefundStatus::dispatch($appointment->id)->delay(now()->addMinute());

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

}
