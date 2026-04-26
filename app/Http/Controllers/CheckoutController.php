<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class CheckoutController extends Controller
{
    /**
     * Create a Stripe Checkout Session and redirect the user to Stripe's hosted page.
     */
    public function create(Request $request)
    {
        $data = $request->session()->get('pending_booking');

        if (! $data) {
            return redirect()->route('home')->with('error', 'No booking data found. Please try again.');
        }

        $service = Service::findOrFail($data['service_id']);

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'mode'                 => 'payment',
            'line_items'           => [
                [
                    'price_data' => [
                        'currency'     => 'php',
                        'unit_amount'  => (int) ($service->price * 100), // convert to centavos
                        'product_data' => [
                            'name'        => $service->name,
                            'description' => 'Appointment on ' . $data['appointment_date'] . ' at ' . $data['appointment_time'],
                        ],
                    ],
                    'quantity' => 1,
                ],
            ],
            'customer_email' => $data['customer_email'],
            'metadata'       => [
                'user_id'          => $data['user_id'] ?? '',
                'service_id'       => $data['service_id'],
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time'],
                'customer_name'    => $data['customer_name'],
                'customer_email'   => $data['customer_email'],
                'customer_phone'   => $data['customer_phone'],
            ],
            'success_url' => route('booking.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('booking.cancel'),
        ]);

        // Store session ID in Laravel session for later verification
        $request->session()->put('stripe_session_id', $session->id);

        return redirect($session->url);
    }

    /**
     * Handle successful payment — save the appointment to the database.
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (! $sessionId) {
            return redirect()->route('home');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $stripeSession = StripeSession::retrieve($sessionId);

        if ($stripeSession->payment_status !== 'paid') {
            return redirect()->route('home')->with('error', 'Payment was not completed.');
        }

        // Prevent duplicate appointment creation on refresh
        $existing = Appointment::where('stripe_session_id', $sessionId)->first();

        if (! $existing) {
            $meta = $stripeSession->metadata;

            Appointment::create([
                'user_id'          => $meta->user_id ?: null,
                'service_id'       => $meta->service_id,
                'appointment_date' => $meta->appointment_date,
                'appointment_time' => $meta->appointment_time,
                'customer_name'    => $meta->customer_name,
                'customer_email'   => $meta->customer_email,
                'customer_phone'   => $meta->customer_phone,
                'status'           => 'paid',
                'stripe_session_id'=> $sessionId,
                'stripe_payment_intent_id' => (string) ($stripeSession->payment_intent ?? ''),
                'amount_paid'      => $stripeSession->amount_total,
            ]);
        }

        // Clear pending booking from session
        $request->session()->forget(['pending_booking', 'pending_booking_draft', 'stripe_session_id']);

        $appointment = $existing ?? Appointment::where('stripe_session_id', $sessionId)->first();

        return view('booking.success', compact('appointment'));
    }

    /**
     * Handle cancelled payment.
     */
    public function cancel(Request $request)
    {
        $request->session()->forget(['pending_booking', 'pending_booking_draft', 'stripe_session_id']);

        return view('booking.cancel');
    }
}
