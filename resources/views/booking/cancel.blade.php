<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled — Black Ember</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-950 text-zinc-100 flex items-center justify-center px-4">

    <div class="max-w-lg w-full text-center space-y-8">

        {{-- X icon --}}
        <div class="flex justify-center">
            <div class="w-20 h-20 rounded-full bg-red-500/10 border border-red-500/30 flex items-center justify-center">
                <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
        </div>

        {{-- Title --}}
        <div>
            <p class="text-red-400 text-sm font-semibold tracking-widest uppercase mb-2">Payment Cancelled</p>
            <h1 class="text-3xl font-bold text-white">Booking Not Completed</h1>
            <p class="mt-3 text-zinc-400 text-sm">
                Your payment was cancelled. No charge was made to your card.
                You can go back and try booking again.
            </p>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('home') }}"
               class="px-6 py-3 rounded-full bg-amber-500 hover:bg-amber-400 text-black font-semibold text-sm transition">
                Back to Home
            </a>
        </div>

    </div>

</body>
</html>
