<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Processed</title>
</head>
<body style="margin:0;background:#09090b;color:#f4f4f5;font-family:Arial,Helvetica,sans-serif;line-height:1.6;padding:24px;">
    <div style="max-width:640px;margin:0 auto;background:#111827;border:1px solid rgba(255,255,255,0.08);border-radius:16px;padding:32px;">
        <p style="margin:0 0 12px;color:#fbbf24;font-size:12px;letter-spacing:0.28em;text-transform:uppercase;">Black Ember</p>
        <h1 style="margin:0 0 16px;font-size:28px;color:#ffffff;">Your refund has been processed</h1>
        <p style="margin:0 0 20px;color:#d4d4d8;">
            Your refund for booking <strong>{{ $appointment->reference_number }}</strong> has been successfully processed by Stripe.
        </p>

        <div style="background:#0f172a;border:1px solid rgba(255,255,255,0.08);border-radius:12px;padding:20px;margin-bottom:20px;">
            <p style="margin:0 0 8px;color:#a1a1aa;font-size:12px;text-transform:uppercase;letter-spacing:0.2em;">Booking Details</p>
            <p style="margin:0 0 6px;color:#f4f4f5;">Service: {{ optional($appointment->service)->name ?? 'Service' }}</p>
            <p style="margin:0 0 6px;color:#f4f4f5;">Date: {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('F j, Y') }}</p>
            <p style="margin:0;color:#f4f4f5;">Time: {{ $appointment->appointment_time }}</p>
        </div>

        <div style="background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.2);border-radius:12px;padding:20px;">
            <p style="margin:0 0 8px;color:#86efac;font-size:12px;text-transform:uppercase;letter-spacing:0.2em;">Refund Summary</p>
            <p style="margin:0 0 6px;color:#f4f4f5;">Refund amount: ₱{{ number_format(((int) $appointment->refund_amount) / 100, 2) }}</p>
            <p style="margin:0;color:#f4f4f5;">Refund reference: {{ $appointment->refund_reference }}</p>
        </div>

        <p style="margin:20px 0 0;color:#a1a1aa;font-size:13px;">
            If you have any questions, please contact support with your reference number above.
        </p>
    </div>
</body>
</html>