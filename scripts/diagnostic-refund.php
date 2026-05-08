<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Appointment;

echo "\n=== REFUND FLOW DIAGNOSTICS ===\n\n";

// Check paid appointments
$paidAppointments = Appointment::where('status', 'paid')->count();
echo "Total paid appointments: {$paidAppointments}\n";

if ($paidAppointments > 0) {
    $appt = Appointment::where('status', 'paid')->first();
    echo "\nSample appointment ID #{$appt->id}:\n";
    echo "  Amount: PHP " . number_format($appt->amount_paid / 100, 2) . "\n";
    echo "  Refund Status: {$appt->refund_status}\n";
    echo "  Payment Intent: " . ($appt->stripe_payment_intent_id ?: 'NOT SET') . "\n";
    echo "  Session ID: " . ($appt->stripe_session_id ?: 'NOT SET') . "\n";
    echo "  Appointment Date: {$appt->appointment_date}\n";
    echo "  Appointment Time: {$appt->appointment_time}\n";
}

// Check Stripe config
echo "\nStripe Configuration:\n";
echo "  - STRIPE_SECRET set: " . (config('services.stripe.secret') ? 'YES' : 'NO') . "\n";
echo "  - STRIPE_KEY set: " . (config('services.stripe.key') ? 'YES' : 'NO') . "\n";

// Check refund config
echo "\nRefund Configuration:\n";
echo "  - Deduction percent: " . config('refund.deduction_percent', 'NOT SET') . "%\n";

echo "\n=== END DIAGNOSTICS ===\n\n";
