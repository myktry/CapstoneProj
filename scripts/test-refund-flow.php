<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Appointment;
use App\Http\Controllers\BookingRefundController;
use Carbon\Carbon;

echo "\n=== REFUND BUTTON DIAGNOSTIC ===\n";

// Find a paid appointment
$appointment = Appointment::where('status', 'paid')
    ->where('appointment_date', '>=', now()->toDateString())
    ->first();

if (!$appointment) {
    echo "❌ No paid appointments found (need future appointment for refund test)\n";
    exit(1);
}

echo "✓ Found paid appointment: #{$appointment->id}\n";
echo "  - Amount: PHP " . number_format($appointment->amount_paid / 100, 2) . "\n";
echo "  - Status: {$appointment->status}\n";
echo "  - Refund Status: {$appointment->refund_status}\n";
echo "  - Appointment Date: {$appointment->appointment_date}\n";
echo "  - Appointment Time: {$appointment->appointment_time}\n";

// Test validation
$controller = new BookingRefundController();
$reflection = new ReflectionClass($controller);

// Check canRequestRefund
$method = $reflection->getMethod('canRequestRefund');
$method->setAccessible(true);
$canRequest = $method->invoke($controller, $appointment);

echo "\nValidation Checks:\n";
echo "  - canRequestRefund: " . ($canRequest ? '✓ YES' : '❌ NO') . "\n";

if (!$canRequest) {
    // Check why
    $appointmentTime = $reflection->getMethod('appointmentDateTime');
    $appointmentTime->setAccessible(true);
    $apptDateTime = $appointmentTime->invoke($controller, $appointment);
    if (!$apptDateTime) {
        echo "    ❌ Failed to parse appointment datetime\n";
    } else {
        $cutoff = $apptDateTime->copy()->subMinutes(10);
        echo "    - Appointment: {$apptDateTime}\n";
        echo "    - Refund cutoff: {$cutoff}\n";
        echo "    - Now: " . now() . "\n";
    }
}

echo "  - status === 'paid': " . ($appointment->status === 'paid' ? '✓ YES' : '❌ NO') . "\n";
echo "  - refund_status !== 'pending': " . ($appointment->refund_status !== 'pending' ? '✓ YES' : '❌ NO') . "\n";
echo "  - refund_status !== 'processed': " . ($appointment->refund_status !== 'processed' ? '✓ YES' : '❌ NO') . "\n";

// Check Stripe keys
echo "\nStripe Configuration:\n";
echo "  - Secret key set: " . (config('services.stripe.secret') ? '✓ YES' : '❌ NO') . "\n";

// Check payment references
echo "\nPayment References:\n";
echo "  - Payment Intent ID: " . ($appointment->stripe_payment_intent_id ?: '(empty)') . "\n";
echo "  - Session ID: " . ($appointment->stripe_session_id ?: '(empty)') . "\n";

if (!$appointment->stripe_payment_intent_id && !$appointment->stripe_session_id) {
    echo "    ❌ WARNING: No payment references stored - refund will fail!\n";
}

echo "\n=== END DIAGNOSTIC ===\n\n";
