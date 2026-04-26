<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Booking Details - Black Ember</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-zinc-950 text-zinc-100 antialiased">
    <main class="mx-auto w-full max-w-3xl px-6 py-12">
        <a href="{{ route('home') }}" class="text-sm text-zinc-400 underline underline-offset-2 hover:text-amber-300">Back to home</a>

        <section class="mt-6 rounded-2xl border border-white/10 bg-zinc-900/80 p-6 shadow-2xl shadow-black/40">
            <p class="text-xs uppercase tracking-[0.3em] text-amber-300">Payment Notification</p>
            <h1 class="mt-2 text-2xl font-semibold text-white">Booking Reference #{{ $appointment->id }}</h1>
            <p class="mt-2 text-sm text-zinc-400">Review your paid appointment details and cancellation/refund policy.</p>

            @if (session('status'))
                <div class="mt-4 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mt-4 rounded-lg border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mt-6 grid gap-4 rounded-xl border border-white/10 bg-zinc-950/70 p-4 text-sm">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-zinc-400">Service</span>
                    <span class="font-medium text-zinc-100">{{ optional($appointment->service)->name ?? 'Service' }}</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-zinc-400">Date & Time</span>
                    <span class="font-medium text-zinc-100">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('F j, Y') }} at {{ \Carbon\Carbon::createFromTimeString($appointment->appointment_time)->format('g:i A') }}</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-zinc-400">Stripe Session</span>
                    <span class="font-medium text-zinc-100">{{ $appointment->stripe_session_id ?? 'N/A' }}</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-zinc-400">Amount Paid</span>
                    <span class="font-semibold text-amber-300">PHP {{ number_format($appointment->amount_paid / 100, 2) }}</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-zinc-400">Status</span>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $appointment->status === 'paid' ? 'bg-emerald-500/10 text-emerald-300' : 'bg-red-500/10 text-red-300' }}">{{ strtoupper($appointment->status) }}</span>
                </div>
            </div>

            <div class="mt-6 rounded-xl border border-amber-500/20 bg-amber-500/5 p-4">
                <p class="text-xs uppercase tracking-[0.25em] text-amber-300">Refund Policy Note</p>
                <p class="mt-2 text-sm text-zinc-300">
                    If you cancel this paid booking, {{ $deductionPercent }}% will be deducted as non-refundable deposit. The remaining amount will be refunded to your original payment method via Stripe.
                </p>
                <p class="mt-2 text-xs text-zinc-400">
                    Refund cancellation closes 10 minutes before appointment time.
                    @if ($refundCutoffAt)
                        Cutoff: {{ $refundCutoffAt->format('F j, Y g:i A') }}.
                    @endif
                </p>
            </div>

            @if ($canRequestRefund && $appointment->status === 'paid' && ! in_array($appointment->refund_status, ['pending', 'processed'], true))
                <form method="POST" action="{{ route('bookings.cancel', $appointment) }}" class="mt-6">
                    @csrf
                    <button
                        type="submit"
                        onclick="return confirm('Are you sure you want to cancel this booking and request a refund?')"
                        class="rounded-full bg-red-500 px-6 py-3 text-sm font-semibold uppercase tracking-widest text-white transition hover:bg-red-400"
                    >
                        Cancel Booking and Request Refund
                    </button>
                </form>
            @else
                <div class="mt-6 rounded-lg border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">
                    @if ($appointment->refund_status === 'pending')
                        Refund request submitted. Waiting for Stripe confirmation. Reference: <span class="font-semibold text-zinc-100">{{ $appointment->refund_reference }}</span>
                    @elseif ($appointment->refund_status === 'processed')
                        Refund processed. Reference: <span class="font-semibold text-zinc-100">{{ $appointment->refund_reference }}</span>
                    @elseif ($appointment->refund_status === 'failed')
                        Refund failed on payment provider update. Please contact support with reference: <span class="font-semibold text-zinc-100">{{ $appointment->refund_reference ?? 'N/A' }}</span>
                    @elseif (! $canRequestRefund && $appointment->status === 'paid')
                        Refund is no longer available because the appointment is within 10 minutes.
                    @else
                        This booking can no longer be cancelled from this page.
                    @endif
                </div>
            @endif
        </section>
    </main>
</body>
</html>
