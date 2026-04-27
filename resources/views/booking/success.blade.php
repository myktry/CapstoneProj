<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed — Black Ember</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-950 text-zinc-100 flex items-center justify-center px-4">

    {{-- Toast Notification --}}
    <div id="booking-toast"
         class="fixed top-6 right-6 z-50 flex items-start gap-3 bg-zinc-900 border border-amber-500/40 rounded-2xl shadow-2xl px-5 py-4 max-w-sm w-full
                translate-x-[120%] opacity-0 transition-all duration-500 ease-out">
        <div class="flex-shrink-0 w-9 h-9 rounded-full bg-amber-500/15 border border-amber-500/30 flex items-center justify-center mt-0.5">
            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-white">Booking Confirmed!</p>
            <p class="text-xs text-zinc-400 mt-0.5 leading-relaxed">
                @if($appointment)
                    Your appointment on
                    <span class="text-amber-400 font-medium">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M j, Y') }}</span>
                    at <span class="text-amber-400 font-medium">{{ $appointment->appointment_time }}</span> is confirmed.
                @else
                    Your payment was successful and your booking is confirmed.
                @endif
            </p>
        </div>
        <button onclick="dismissToast()" class="flex-shrink-0 text-zinc-600 hover:text-zinc-300 transition mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        {{-- Progress bar --}}
        <div id="toast-progress" class="absolute bottom-0 left-0 h-0.5 bg-amber-500 rounded-b-2xl w-full origin-left"></div>
    </div>

    <div class="max-w-lg w-full text-center space-y-8">

        {{-- Checkmark --}}
        <div class="flex justify-center">
            <div class="w-20 h-20 rounded-full bg-amber-500/10 border border-amber-500/30 flex items-center justify-center">
                <svg class="w-10 h-10 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </div>

        {{-- Title --}}
        <div>
            <p class="text-amber-400 text-sm font-semibold tracking-widest uppercase mb-2">Payment Successful</p>
            <h1 class="text-3xl font-bold text-white">Booking Confirmed!</h1>
            <p class="mt-3 text-zinc-400 text-sm">
                Thank you for booking with Black Ember. We look forward to serving you.
            </p>
        </div>

        {{-- Booking Details --}}
        @if($appointment)
        <div class="bg-zinc-900 border border-white/10 rounded-2xl p-6 text-left space-y-3">
            <h2 class="text-xs font-semibold tracking-widest text-zinc-500 uppercase mb-4">Booking Details</h2>

            <div class="flex justify-between text-sm">
                <span class="text-zinc-400">Name</span>
                <span class="text-white font-medium">{{ $appointment->customer_name }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-zinc-400">Email</span>
                <span class="text-white font-medium">{{ $appointment->customer_email }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-zinc-400">Service</span>
                <span class="text-white font-medium">
                    {{ optional(\App\Models\Service::find($appointment->service_id))->name ?? 'N/A' }}
                </span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-zinc-400">Date</span>
                <span class="text-white font-medium">
                    {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('F j, Y') }}
                </span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-zinc-400">Time</span>
                <span class="text-white font-medium">{{ $appointment->appointment_time }}</span>
            </div>
            <div class="border-t border-white/10 pt-3 flex justify-between text-sm">
                <span class="text-zinc-400">Reference Number</span>
                <span class="text-white font-medium font-mono text-xs break-all text-right">{{ $appointment->reference_number }}</span>
            </div>
            <div class="border-t border-white/10 pt-3 flex justify-between text-sm">
                <span class="text-zinc-400">Amount Paid</span>
                <span class="text-amber-400 font-bold text-base">
                    ₱{{ number_format($appointment->amount_paid / 100, 2) }}
                </span>
            </div>
        </div>
        @endif

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('home') }}"
               class="px-6 py-3 rounded-full bg-amber-500 hover:bg-amber-400 text-black font-semibold text-sm transition">
                Back to Home
            </a>
        </div>

    </div>

    <script>
        const toast = document.getElementById('booking-toast');
        const progress = document.getElementById('toast-progress');
        const DURATION = 6000; // ms before auto-dismiss

        function dismissToast() {
            toast.classList.remove('translate-x-0', 'opacity-100');
            toast.classList.add('translate-x-[120%]', 'opacity-0');
            progress.style.transition = 'none';
        }

        // Slide in after a short delay
        setTimeout(() => {
            toast.classList.remove('translate-x-[120%]', 'opacity-0');
            toast.classList.add('translate-x-0', 'opacity-100');

            // Animate the progress bar shrinking
            progress.style.transition = `width ${DURATION}ms linear`;
            progress.style.width = '0%';
        }, 300);

        // Auto-dismiss
        setTimeout(dismissToast, DURATION + 300);
    </script>

</body>
</html>
