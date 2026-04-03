<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Gallery - Black Ember Barbershop</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-[#0a0a0a] text-zinc-100 antialiased">
        @php
            $galleryItems = \App\Models\GalleryItem::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('created_at')
                ->get()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'description' => $item->description ?: 'Premium grooming style.',
                    'image' => $item->image
                        ? (str_starts_with($item->image, 'http') ? $item->image : \Illuminate\Support\Facades\Storage::disk('public')->url($item->image))
                        : 'https://images.unsplash.com/photo-1503951458645-643d53bfd90f?q=80&w=1200&auto=format&fit=crop',
                ]);
        @endphp

        <livewire:booking-panel />

        <div class="min-h-screen">
            {{-- Header --}}
            <header class="sticky top-0 z-40 border-b border-white/10 bg-[#0a0a0a]/80 backdrop-blur">
                <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-4">
                    <a href="{{ route('home') }}" class="hover:opacity-80 transition">
                        <p class="text-sm uppercase tracking-[0.3em] text-amber-500">Barbershop</p>
                        <h1 class="text-lg font-semibold">Black Ember</h1>
                    </a>
                    <nav class="flex items-center gap-8 text-sm uppercase tracking-widest text-zinc-400">
                        <a href="{{ route('home') }}" class="transition hover:text-white">Home</a>
                        <a href="{{ route('gallery') }}" class="transition hover:text-amber-500">Gallery</a>
                        <a href="{{ route('home') }}#book" class="transition hover:text-white">Book Now</a>
                        <a href="{{ route('home') }}#contact" class="transition hover:text-white">Contact</a>

                        @auth
                            @php
                                $recentBookings = auth()->user()->appointments()
                                    ->with('service')
                                    ->latest()
                                    ->take(5)
                                    ->get();
                                $newCount = auth()->user()->appointments()
                                    ->where('created_at', '>=', now()->subDay())
                                    ->count();
                            @endphp

                            {{-- Notification Bell --}}
                            <details class="relative" id="notif-details">
                                <summary class="list-none cursor-pointer relative flex items-center justify-center w-9 h-9 rounded-full border border-white/10 bg-zinc-900 text-zinc-400 hover:border-amber-500/40 hover:text-amber-500 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                    @if($newCount > 0)
                                        <span class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-amber-500 text-[9px] font-bold text-black leading-none">
                                            {{ $newCount > 9 ? '9+' : $newCount }}
                                        </span>
                                    @endif
                                </summary>

                                <div class="absolute right-0 mt-2 w-72 rounded-xl border border-white/10 bg-zinc-900 shadow-2xl shadow-black/50 overflow-hidden">
                                    <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
                                        <span class="text-xs font-semibold text-zinc-300 uppercase tracking-widest">My Bookings</span>
                                        @if($newCount > 0)
                                            <span class="text-[10px] bg-amber-500/15 text-amber-400 border border-amber-500/30 rounded-full px-2 py-0.5 font-medium">
                                                {{ $newCount }} new
                                            </span>
                                        @endif
                                    </div>

                                    @if($recentBookings->isEmpty())
                                        <div class="px-4 py-6 text-center">
                                            <svg class="w-8 h-8 text-zinc-700 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5"/>
                                            </svg>
                                            <p class="text-xs text-zinc-500 normal-case tracking-normal">No bookings yet</p>
                                        </div>
                                    @else
                                        <ul class="divide-y divide-white/5 max-h-72 overflow-y-auto">
                                            @foreach($recentBookings as $booking)
                                                @php
                                                    $isNew = $booking->created_at >= now()->subDay();
                                                @endphp
                                                <li class="px-4 py-3 flex items-start gap-3 {{ $isNew ? 'bg-amber-500/5' : '' }}">
                                                    <div class="flex-shrink-0 mt-0.5 w-7 h-7 rounded-full flex items-center justify-center
                                                        {{ $booking->status === 'paid' ? 'bg-emerald-500/10 text-emerald-400' : ($booking->status === 'cancelled' ? 'bg-red-500/10 text-red-400' : 'bg-zinc-800 text-zinc-400') }}">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            @if($booking->status === 'paid')
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                            @elseif($booking->status === 'cancelled')
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                            @else
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3"/>
                                                            @endif
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-xs font-medium text-zinc-200 normal-case tracking-normal truncate">
                                                            {{ optional($booking->service)->name ?? 'Service' }}
                                                        </p>
                                                        <p class="text-[11px] text-zinc-500 normal-case tracking-normal mt-0.5">
                                                            {{ \Carbon\Carbon::parse($booking->appointment_date)->format('M j, Y') }}
                                                            &middot; {{ \Carbon\Carbon::createFromTimeString($booking->appointment_time)->format('g:i A') }}
                                                        </p>
                                                    </div>
                                                    <span class="flex-shrink-0 text-[10px] font-medium rounded-full px-2 py-0.5 normal-case tracking-normal
                                                        {{ $booking->status === 'paid' ? 'bg-emerald-500/10 text-emerald-400' : ($booking->status === 'cancelled' ? 'bg-red-500/10 text-red-400' : 'bg-zinc-700 text-zinc-400') }}">
                                                        {{ ucfirst($booking->status) }}
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </details>

                            <details class="relative">
                                <summary class="list-none cursor-pointer rounded-full border border-amber-500/40 bg-zinc-900 px-3 py-2 text-xs text-amber-500 hover:border-amber-300/70 hover:text-amber-200">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </summary>

                                <div class="absolute right-0 mt-2 w-52 rounded-xl border border-white/10 bg-zinc-900 p-2 shadow-2xl shadow-black/40">
                                    <div class="px-3 py-2 text-xs normal-case tracking-normal text-zinc-500">
                                        {{ auth()->user()->email }}
                                    </div>

                                    <a href="{{ route('profile') }}" class="block rounded-lg px-3 py-2 text-xs normal-case tracking-normal text-zinc-200 hover:bg-zinc-800">
                                        View / Edit Profile
                                    </a>

                                    <form method="POST" action="{{ route('logout') }}" class="mt-1">
                                        @csrf
                                        <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-xs normal-case tracking-normal text-zinc-200 hover:bg-zinc-800">
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </details>
                        @else
                            <a href="{{ route('login') }}" class="transition hover:text-white">Login</a>
                        @endauth
                    </nav>
                </div>
            </header>

            {{-- Gallery Hero Section --}}
            <section class="bg-[#0a0a0a] pt-20 pb-10">
                <div class="mx-auto w-full max-w-6xl px-6">
                    <div class="space-y-3 mb-4">
                        <p class="text-xs uppercase tracking-[0.4em] text-amber-500">Lookbook</p>
                        <h2 class="text-4xl font-semibold text-white sm:text-5xl">Our Gallery</h2>
                    </div>
                    <p class="max-w-2xl text-lg text-zinc-400">
                        Browse our curated collection of signature cuts, fades, and beard styles. Each design represents our commitment to precision and modern barbering.
                    </p>
                </div>
            </section>

            {{-- Gallery Grid Section --}}
            <section class="bg-[#0a0a0a] py-20">
                <div class="mx-auto w-full max-w-6xl px-6">
                    @if($galleryItems->isEmpty())
                        {{-- Empty State --}}
                        <div class="flex flex-col items-center justify-center py-24">
                            <svg class="w-16 h-16 text-zinc-700 mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                            </svg>
                            <h3 class="text-2xl font-semibold text-zinc-300 mb-2">No styles available</h3>
                            <p class="text-zinc-500 text-center max-w-md">
                                Our gallery is being updated with stunning styles. Check back soon!
                            </p>
                        </div>
                    @else
                        {{-- Hairstyle Grid --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 xl:gap-8">
                                @foreach($galleryItems as $style)
                                <div x-data="{
                                    quickViewOpen: false,
                                    styleName: '{{ $style['name'] }}',
                                    styleDescription: '{{ $style['description'] }}',
                                    stylePrice: '{{ number_format((float) $style['price'], 2) }}'
                                }" class="group flex flex-col overflow-hidden rounded-lg border border-white/10 bg-zinc-900 transition hover:border-amber-500/30 hover:shadow-2xl hover:shadow-amber-500/10">

                                    {{-- Image Container with Inner Shadow --}}
                                    <div class="relative aspect-[4/3] w-full overflow-hidden bg-gradient-to-br from-zinc-800 to-zinc-900 flex-shrink-0">
                                        <img
                                            src="{{ $style['image'] }}"
                                            alt="{{ $style['name'] }}"
                                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                        >
                                        <!-- Inner Shadow Effect -->
                                        <div class="absolute inset-0 shadow-inset pointer-events-none rounded-t-lg"
                                             style="box-shadow: inset 0 1px 3px rgba(0,0,0,0.3), inset 0 -1px 3px rgba(0,0,0,0.3);"></div>
                                    </div>

                                    {{-- Content Container --}}
                                    <div class="flex flex-col flex-1 p-5 min-h-[140px]">
                                        {{-- Title --}}
                                        <div class="flex items-start justify-between gap-3 mb-2">
                                            <h4 class="font-semibold text-white leading-snug text-lg">
                                                {{ $style['name'] }}
                                            </h4>
                                        </div>

                                        <p class="mb-3 text-sm font-semibold text-amber-400">
                                            ₱{{ number_format((float) $style['price'], 2) }}
                                        </p>

                                        {{-- Description --}}
                                        <p class="text-sm text-zinc-400 line-clamp-2 mb-4">
                                            {{ $style['description'] }}
                                        </p>

                                        {{-- Action Buttons --}}
                                        <div class="flex items-center gap-3 mt-auto">
                                            @auth
                                                <button
                                                    type="button"
                                                    onclick="window.Livewire.dispatch('open-booking', { service: '{{ $style['name'] }}' })"
                                                    class="flex-1 rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold uppercase tracking-wider text-zinc-900 transition hover:bg-amber-400 focus:bg-amber-400 active:scale-95"
                                                >
                                                    Book This Style
                                                </button>
                                            @else
                                                <a
                                                    href="{{ route('book.appointment') }}"
                                                    class="flex-1 rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold uppercase tracking-wider text-zinc-900 transition hover:bg-amber-400 focus:bg-amber-400 active:scale-95 text-center"
                                                >
                                                    Book This Style
                                                </a>
                                            @endauth

                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

            {{-- CTA Section --}}
            <section class="bg-gradient-to-b from-[#0a0a0a] to-zinc-950 py-20">
                <div class="mx-auto w-full max-w-6xl px-6">
                    <div class="rounded-2xl border border-white/10 bg-gradient-to-br from-zinc-900/80 to-zinc-950 p-8 sm:p-12 text-center">
                        <p class="text-xs uppercase tracking-[0.4em] text-amber-500 mb-3">Ready to get sharp?</p>
                        <h3 class="text-3xl sm:text-4xl font-semibold text-white mb-4">Schedule Your Appointment</h3>
                        <p class="max-w-2xl mx-auto text-zinc-400 mb-8">
                            Choose any style from our gallery and book your session. Our barbers are ready to deliver the precision cut you deserve.
                        </p>
                        @auth
                            <button
                                type="button"
                                onclick="window.Livewire.dispatch('open-booking')"
                                class="rounded-full bg-amber-500 px-8 py-3 text-sm font-semibold uppercase tracking-widest text-zinc-900 transition hover:bg-amber-400 focus:bg-amber-400"
                            >
                                Book Now
                            </button>
                        @else
                            <a
                                href="{{ route('book.appointment') }}"
                                class="inline-block rounded-full bg-amber-500 px-8 py-3 text-sm font-semibold uppercase tracking-widest text-zinc-900 transition hover:bg-amber-400 focus:bg-amber-400"
                            >
                                Book Now
                            </a>
                        @endauth
                    </div>
                </div>
            </section>

            {{-- Footer --}}
            <footer class="border-t border-white/10 py-10 text-center text-sm text-zinc-500">
                © 2026 Black Ember Barbershop. All rights reserved.
            </footer>
        </div>

        @livewireScripts

        {{-- Close other <details> dropdowns when one opens --}}
        <script>
            document.querySelectorAll('details').forEach(details => {
                details.addEventListener('toggle', () => {
                    if (details.open) {
                        document.querySelectorAll('details').forEach(other => {
                            if (other !== details) other.removeAttribute('open');
                        });
                    }
                });
            });
        </script>
    </body>
</html>
